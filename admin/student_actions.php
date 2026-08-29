<?php
// admin/student_actions.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Helper function: Parse native .xlsx XML files without third-party libraries
function parseXlsxFile($filePath) {
    $rows = [];
    $zip = new ZipArchive();
    
    if ($zip->open($filePath) === TRUE) {
        // Read shared strings (text contents of cells)
        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== FALSE) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->si as $val) {
                $sharedStrings[] = (string)$val->t;
            }
        }

        // Read main sheet XML
        if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== FALSE) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $val = (string)$cell->v;
                    // Check if value refers to shared string table
                    if (isset($cell['t']) && (string)$cell['t'] === 's' && isset($sharedStrings[$val])) {
                        $val = $sharedStrings[$val];
                    }
                    $rowData[] = $val;
                }
                if (!empty($rowData)) {
                    $rows[] = $rowData;
                }
            }
        }
        $zip->close();
    }
    return $rows;
}

// Clean hidden spaces and special control characters from imported strings
function cleanInputString($str) {
    if ($str === null) return '';
    // Strip non-breaking spaces, control characters, carriage returns, and tabs
    $cleaned = preg_replace('/[\x00-\x1F\x7F-\xA0\xC2\xA0]/u', '', (string)$str);
    return trim($cleaned);
}

// -------------------------------------------------------------------------
// 1. Action: Upload Student Roster (CSV & XLSX)
// -------------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'upload_csv') {
    if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] === 0) {
        $fileName = $_FILES['student_file']['name'];
        $fileTmpPath = $_FILES['student_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // PostgreSQL ON CONFLICT syntax for Upsert operations
        $stmt = $pdo->prepare("
            INSERT INTO students_list (full_name, admission_no) 
            VALUES (:full_name, :admission_no)
            ON CONFLICT (admission_no) 
            DO UPDATE SET full_name = EXCLUDED.full_name
        ");

        $count = 0;

        // PATH A: Native Excel Files (.xlsx)
        if ($fileExtension === 'xlsx') {
            $sheetData = parseXlsxFile($fileTmpPath);

            foreach ($sheetData as $row) {
                if (count($row) < 2) continue;

                $adm  = cleanInputString($row[0] ?? '');
                $name = cleanInputString($row[1] ?? '');

                if (empty($adm) || empty($name) || strtolower($adm) === 'admission_no' || strtolower($adm) === 'admission no' || strtolower($adm) === 's/n') {
                    continue;
                }

                $stmt->execute(['admission_no' => $adm, 'full_name' => $name]);
                $count++;
            }

            header("Location: dashboard.php?msg=" . urlencode("Successfully imported $count student records from Excel file."));
            exit;
        } 
        
        // PATH B: CSV Files (.csv)
        else if ($fileExtension === 'csv') {
            $fileContent = file_get_contents($fileTmpPath);
            $fileContent = str_replace("\xEF\xBB\xBF", '', $fileContent); // Strip UTF-8 BOM
            file_put_contents($fileTmpPath, $fileContent);

            if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
                $firstLine = fgets($handle);
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                rewind($handle);

                while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    if (empty($data) || count($data) < 2) continue;

                    $adm  = cleanInputString($data[0] ?? '');
                    $name = cleanInputString($data[1] ?? '');

                    if (empty($adm) || empty($name) || strtolower($adm) === 'admission_no' || strtolower($adm) === 'admission no' || strtolower($adm) === 's/n') {
                        continue;
                    }

                    $stmt->execute(['admission_no' => $adm, 'full_name' => $name]);
                    $count++;
                }
                fclose($handle);
                header("Location: dashboard.php?msg=" . urlencode("Successfully imported $count student records from CSV."));
                exit;
            }
        } else {
            header("Location: dashboard.php?err=" . urlencode("Unsupported file type. Please upload a .csv or .xlsx file."));
            exit;
        }
    }
    header("Location: dashboard.php?err=" . urlencode("Failed to upload file."));
    exit;
}

// -------------------------------------------------------------------------
// 2. Action: Check Duplicate Student Admission Number (AJAX)
// -------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'check_duplicate') {
    header('Content-Type: application/json');
    $adm = cleanInputString($_GET['admission_no'] ?? '');

    $stmt = $pdo->prepare("SELECT full_name FROM students_list WHERE TRIM(admission_no) ILIKE :adm");
    $stmt->execute(['adm' => $adm]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode(['exists' => true, 'existing_name' => $existing['full_name']]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}

// -------------------------------------------------------------------------
// 3. Action: Manual Student Add or Overwrite
// -------------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'add_manual_student') {
    $adm  = cleanInputString($_POST['admission_no'] ?? '');
    $name = cleanInputString($_POST['full_name'] ?? '');

    if (!empty($adm) && !empty($name)) {
        $stmt = $pdo->prepare("
            INSERT INTO students_list (full_name, admission_no) 
            VALUES (:full_name, :admission_no)
            ON CONFLICT (admission_no) 
            DO UPDATE SET full_name = EXCLUDED.full_name
        ");
        $stmt->execute(['admission_no' => $adm, 'full_name' => $name]);

        header("Location: dashboard.php?msg=" . urlencode("Student record saved successfully."));
        exit;
    }
}

// -------------------------------------------------------------------------
// 4. Action: Delete Student Record
// -------------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete_student') {
    $student_id = intval($_POST['student_id'] ?? 0);

    if ($student_id > 0) {
        try {
            // Delete attendance records linked to student first
            $stmtAtt = $pdo->prepare("DELETE FROM parents_attendence WHERE student_id = :id");
            $stmtAtt->execute(['id' => $student_id]);

            // Delete student record
            $stmtStudent = $pdo->prepare("DELETE FROM students_list WHERE id = :id");
            $stmtStudent->execute(['id' => $student_id]);

            header("Location: dashboard.php?msg=" . urlencode("Student and associated attendance records deleted successfully."));
            exit;
        } catch (PDOException $e) {
            header("Location: dashboard.php?err=" . urlencode("Failed to delete student: " . $e->getMessage()));
            exit;
        }
    }
}

header("Location: dashboard.php");
exit;
?>
