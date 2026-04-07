(function() {
    'use strict';

    // DOM Elements
    const modal = document.getElementById('subjectModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const addSubjectBtn = document.getElementById('addSubjectBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const subjectForm = document.getElementById('subjectForm');
    const messageToast = document.getElementById('messageToast');
    const navItems = document.querySelectorAll('.nav-item');

    // Filters
    const programFilter = document.getElementById('programFilter');
    const yearFilter = document.getElementById('yearFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const resetFilterBtn = document.getElementById('resetFilterBtn');

    // Stats
    const totalSubjectsCount = document.getElementById('totalSubjectsCount');
    const totalUnitsCount = document.getElementById('totalUnitsCount');
    const totalHoursCount = document.getElementById('totalHoursCount');

    // Form fields
    const subjectCodeInput = document.getElementById('subjectCode');
    const subjectDescriptionInput = document.getElementById('subjectDescription');
    const subjectProgramSelect = document.getElementById('subjectProgram');
    const subjectYearSelect = document.getElementById('subjectYear');
    const subjectSemesterSelect = document.getElementById('subjectSemester');
    const subjectUnitsInput = document.getElementById('subjectUnits');
    const subjectHoursInput = document.getElementById('subjectHours');
    const subjectPrerequisiteInput = document.getElementById('subjectPrerequisite');
    const subjectCorequisiteInput = document.getElementById('subjectCorequisite');
    const modalTitle = document.getElementById('modalTitle');

    let editingSubjectId = null;

    function init() {
        addSubjectBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);
        cancelModalBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);

        // ✅ ONLY ONE SUBMIT HANDLER
        subjectForm.addEventListener('submit', handleFormSubmit);

        programFilter.addEventListener('change', updateTable);
        yearFilter.addEventListener('change', updateTable);
        semesterFilter.addEventListener('change', updateTable);
        resetFilterBtn.addEventListener('click', resetFilters);

        attachTableEventListeners();
        updateStatistics();
    }

    function openModal() {
        modal.classList.add('active');
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        subjectForm.reset();
        editingSubjectId = null;
        modalTitle.textContent = 'Add New Subject';
    }

    function closeModal() {
        modal.classList.remove('active');
        modalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
        subjectForm.reset();
    }

function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(subjectForm);
        
        // Convert FormData to a plain object for consistency if you prefer JSON,
        // but standard FormData is fine as long as PHP reads $_POST.
        fetch('subject-management.php', {
            method: 'POST',
            body: formData // This sends as multipart/form-data
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Server Error');
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                showMessage('success', data.message);
                // Delay reload slightly so user sees the success message
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            showMessage('error', err.message || 'Failed to save subject.');
        });
    }

    function attachTableEventListeners() {
        document.querySelectorAll('.subject-row').forEach(row => {
            const editBtn = row.querySelector('.btn-edit');
            const deleteBtn = row.querySelector('.btn-delete');

            if (editBtn) {
                editBtn.addEventListener('click', () => handleEditSubject(row));
            }

            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => handleDeleteSubject(row));
            }
        });
    }

    function handleEditSubject(row) {
        subjectCodeInput.value = row.querySelector('.code-cell strong').textContent.trim();
        subjectDescriptionInput.value = row.querySelector('.description-text').textContent.trim();
        subjectProgramSelect.value = row.dataset.program;
        subjectYearSelect.value = row.dataset.year;
        subjectSemesterSelect.value = row.dataset.semester;

        const [units, hours] = row.querySelector('.units-badge').textContent.split('/');
        subjectUnitsInput.value = units.trim();
        subjectHoursInput.value = hours.trim();

        modalTitle.textContent = 'Edit Subject';

        modal.classList.add('active');
        modalOverlay.classList.add('active');
    }

    function handleDeleteSubject(row) {
        const subjectId = row.dataset.subjectId;

        if (!confirm('Delete this subject?')) return;

        fetch('delete-subject.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `subject_id=${subjectId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                row.remove();
                updateStatistics();
                showMessage('success', 'Deleted successfully');
            } else {
                showMessage('error', 'Delete failed');
            }
        });
    }

    function updateTable() {
        const pv = programFilter.value;
        const yv = yearFilter.value;
        const sv = semesterFilter.value;

        document.querySelectorAll('.subject-row').forEach(row => {
            const match =
                (!pv || row.dataset.program === pv) &&
                (!yv || row.dataset.year === yv) &&
                (!sv || row.dataset.semester === sv);

            row.style.display = match ? '' : 'none';
        });

        updateStatistics();
    }

    function resetFilters() {
        programFilter.value = '';
        yearFilter.value = '';
        semesterFilter.value = '';
        updateTable();
    }

    function updateStatistics() {
        let count = 0, units = 0, hours = 0;

        document.querySelectorAll('.subject-row').forEach(row => {
            if (row.style.display === 'none') return;

            count++;
            const parts = row.querySelector('.units-badge').textContent.split('/');
            units += parseInt(parts[0]) || 0;
            hours += parseInt(parts[1]) || 0;
        });

        totalSubjectsCount.textContent = count;
        totalUnitsCount.textContent = units;
        totalHoursCount.textContent = hours;
    }

    function showMessage(type, msg) {
        messageToast.textContent = msg;
        messageToast.className = `message-toast show ${type}`;
        setTimeout(() => messageToast.classList.remove('show'), 3000);
    }

    document.addEventListener('DOMContentLoaded', init);

    document.addEventListener('DOMContentLoaded', function() {
    const subjectForm = document.getElementById('subjectForm');

    if (subjectForm) {
        // This connects your "Save Subject" button to the function
        subjectForm.addEventListener('submit', handleFormSubmit);
    }
});

function handleFormSubmit(e) {
    e.preventDefault(); // This stops the page from refreshing immediately

    // 1. Gather the data from the form
    const formData = new FormData(e.target);

    // 2. Send the data to your PHP file
    fetch('subject-management.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // We expect a JSON response from PHP
    .then(data => {
        if (data.status === 'success') {
            alert('Saved successfully!');
            window.location.reload(); // Refresh to show new data in table
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Could not connect to the server.');
    });
}
})();