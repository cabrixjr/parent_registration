<?php
// admin/student_actions.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Helper function: Parse native .xlsx XML files without libraries
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

// 1. Action: Upload Student Roster (CSV & XLSX)
if (isset($_POST['action']) && $_POST['action'] === 'upload_csv') {
    if (isset($_FILES['student_file']) && $_FILES['student_file']['error'] === 0) {
        $fileName = $_FILES['student_file']['name'];
        $fileTmpPath = $_FILES['student_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $stmt = $pdo->prepare("
            INSERT INTO students_list (full_name, admission_no) 
            VALUES (:full_name, :admission_no)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)
        ");

        $count = 0;

        // PATH A: Native Excel Files (.xlsx)
        if ($fileExtension === 'xlsx') {
            $sheetData = parseXlsxFile($fileTmpPath);

            foreach ($sheetData as $row) {
                if (count($row) < 2) continue;

                $adm = trim(preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $row[0] ?? ''));
                $name = trim(preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $row[1] ?? ''));

                if (empty($adm) || empty($name) || strtolower($adm) === 'admission_no' || strtolower($adm) === 'admission no') {
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
            $fileContent = str_replace("\xEF\xBB\xBF", '', $fileContent);
            file_put_contents($fileTmpPath, $fileContent);

            if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
                $firstLine = fgets($handle);
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                rewind($handle);

                while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    if (empty($data) || count($data) < 2) continue;

                    $adm = trim(preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $data[0]));
                    $name = trim(preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $data[1]));

                    if (empty($adm) || empty($name) || strtolower($adm) === 'admission_no' || strtolower($adm) === 'admission no') {
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

// 2. Action: Check Duplicate Student Admission Number (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'check_duplicate') {
    header('Content-Type: application/json');
    $adm = trim($_GET['admission_no'] ?? '');

    $stmt = $pdo->prepare("SELECT full_name FROM students_list WHERE admission_no = :adm");
    $stmt->execute(['adm' => $adm]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode(['exists' => true, 'existing_name' => $existing['full_name']]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}

// 3. Action: Manual Student Add or Overwrite
if (isset($_POST['action']) && $_POST['action'] === 'add_manual_student') {
    $adm = trim($_POST['admission_no'] ?? '');
    $name = trim($_POST['full_name'] ?? '');

    if (!empty($adm) && !empty($name)) {
        $stmt = $pdo->prepare("
            INSERT INTO students_list (full_name, admission_no) 
            VALUES (:full_name, :admission_no)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name)
        ");
        $stmt->execute(['admission_no' => $adm, 'full_name' => $name]);

        header("Location: dashboard.php?msg=" . urlencode("Student record saved successfully."));
        exit;
    }
}
?>