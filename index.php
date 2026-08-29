<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration - Kibaha Secondary School</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="gov-header">
        <h1>KIBAHA SECONDARY SCHOOL</h1>
        <p>PARENT-TEACHER MEETING ATTENDANCE REGISTER</p>
    </header>
    <div class="container">
        <div class="container-narrow">
        <div class="card">
            <h2 style="color: var(--gov-navy); margin-bottom: 20px; font-size: 1.2rem; text-align: center;">
                PARENT / GUARDIAN DETAILS
            </h2>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    ✓ Registration submitted successfully! Thank you.
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    ⚠️ Please fill in all fields correctly and select a valid student.
                </div>
            <?php endif; ?>

            <form action="submit_registration.php" method="POST" autocomplete="off">
                
                <div class="form-group">
                    <label for="parent_name">Parent / Guardian Full Name:</label>
                    <input type="text" id="parent_name" name="parent_name" placeholder="e.g., John Doe" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="e.g., 0712345678" required>
                </div>

                <div class="form-group">
                    <label for="student_search">Search Student Name:</label>
                    <input type="text" id="student_search" placeholder="Type student's name..." required>
                    <!-- Hidden field to hold the actual selected student ID -->
                    <input type="hidden" id="student_id" name="student_id" required>
                    <div id="autocomplete_list" class="autocomplete-results" style="display: none;"></div>
                </div>

                <button type="submit" class="btn">REGISTER ATTENDANCE</button>
            </form>
        </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('student_search');
        const hiddenStudentId = document.getElementById('student_id');
        const resultsBox = document.getElementById('autocomplete_list');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear hidden ID if user edits search text
            hiddenStudentId.value = '';

            if (query.length < 1) {
                resultsBox.style.display = 'none';
                return;
            }

            fetch(`api/search_students.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsBox.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(student => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerHTML = `<strong>${student.full_name}</strong> <small>(${student.admission_no})</small>`;
                            
                            item.addEventListener('click', function() {
                                searchInput.value = `${student.full_name} (${student.admission_no})`;
                                hiddenStudentId.value = student.id;
                                resultsBox.style.display = 'none';
                            });

                            resultsBox.appendChild(item);
                        });
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<div class="autocomplete-item" style="color: #888;">No matching student found</div>';
                        resultsBox.style.display = 'block';
                    }
                })
                .catch(err => console.error('Autocomplete Error:', err));
        });

        // Hide dropdown on click outside
        document.addEventListener('click', function(e) {
            if (e.target !== searchInput && e.target !== resultsBox) {
                resultsBox.style.display = 'none';
            }
        });
    </script>
</body>
</html>