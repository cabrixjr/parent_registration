/**
 * Live Student Search Autocomplete
 * Kibaha Secondary School Parent Register System
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('student_search');
    const hiddenStudentId = document.getElementById('student_id');
    const resultsBox = document.getElementById('autocomplete_list');

    if (!searchInput || !hiddenStudentId || !resultsBox) return;

    let debounceTimer;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        // Reset hidden student ID whenever input changes
        hiddenStudentId.value = '';

        // Clear existing debounce timer
        clearTimeout(debounceTimer);

        if (query.length < 1) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML = '';
            return;
        }

        // Debounce requests by 250ms to minimize server calls
        debounceTimer = setTimeout(() => {
            fetch(`api/search_students.php?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    resultsBox.innerHTML = '';

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(student => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerHTML = `<strong>${escapeHtml(student.full_name)}</strong> <small>(${escapeHtml(student.admission_no)})</small>`;

                            item.addEventListener('click', () => {
                                searchInput.value = `${student.full_name} (${student.admission_no})`;
                                hiddenStudentId.value = student.id;
                                resultsBox.style.display = 'none';
                            });

                            resultsBox.appendChild(item);
                        });
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<div class="autocomplete-item" style="color: #888; cursor: default;">No matching student found</div>';
                        resultsBox.style.display = 'block';
                    }
                })
                .catch(error => console.error('Autocomplete Fetch Error:', error));
        }, 250);
    });

    // Close autocomplete menu when clicking outside
    document.addEventListener('click', (e) => {
        if (e.target !== searchInput && !resultsBox.contains(e.target)) {
            resultsBox.style.display = 'none';
        }
    });

    // Simple HTML escaping helper
    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, (match) => {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return map[match];
        });
    }
});