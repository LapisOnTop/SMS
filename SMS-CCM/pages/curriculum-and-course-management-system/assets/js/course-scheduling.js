(function() {
    'use strict';

    // DOM Elements
    const schedulingContainer = document.getElementById('schedulingContainer');
    const programSelect = document.getElementById('programSelect');
    const messageToast = document.getElementById('messageToast');
    const allSubjectsLists = document.querySelectorAll('.subjects-list');
    const navItems = document.querySelectorAll('.nav-item');

    // State tracking
    let draggedElement = null;
    let dragSourceList = null;

    /**
     * Initialize all event listeners
     */
    function init() {
        // Program selector change
        programSelect.addEventListener('change', handleProgramChange);

        // Drag and drop events
        allSubjectsLists.forEach(list => {
            list.addEventListener('dragover', handleDragOver);
            list.addEventListener('drop', handleDrop);
            list.addEventListener('dragleave', handleDragLeave);
        });

        // Move dropdown events
        attachMoveDropdownListeners();

        // Sidebar navigation
        navItems.forEach(item => {
            item.addEventListener('click', handleNavigation);
        });

        // Initial unit calculations
        updateAllUnitCounts();
    }

    /**
     * Handle program change
     */
    function handleProgramChange(e) {
        const selectedProgram = e.target.value;
        window.location.href = `course-scheduling.php?program=${encodeURIComponent(selectedProgram)}`;
    }

    /**
     * Handle drag start
     */
    function handleDragStart(e) {
        draggedElement = this;
        dragSourceList = this.parentElement;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    /**
     * Handle drag over
     */
    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        this.classList.add('drag-over');
    }

    /**
     * Handle drag leave
     */
    function handleDragLeave(e) {
        if (e.target === this) {
            this.classList.remove('drag-over');
        }
    }

    /**
     * Handle drop
     */
    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.remove('drag-over');

        if (!draggedElement || dragSourceList === this) {
            resetDragState();
            return;
        }

        const targetYearSemester = this.dataset.yearSemester;
        const sourceYearSemester = dragSourceList.dataset.yearSemester;
        const subjectCode = draggedElement.dataset.code;

        // Move subject
        moveSubject(draggedElement, sourceYearSemester, targetYearSemester);

        // Show message
        showMessage('success', `${subjectCode} moved to ${targetYearSemester}`);

        // Update unit counts
        updateUnitCount(sourceYearSemester);
        updateUnitCount(targetYearSemester);

        resetDragState();
    }

    /**
     * Move subject between lists
     */
    function moveSubject(element, fromYearSemester, toYearSemester) {
        const targetList = document.querySelector(`.subjects-list[data-year-semester="${toYearSemester}"]`);
        
        if (targetList) {
            // Remove empty state if exists
            const emptyState = targetList.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }

            // Clone and append
            const newElement = element.cloneNode(true);
            attachSubjectEventListeners(newElement);
            targetList.appendChild(newElement);

            // Remove from source
            element.remove();

            // If source is now empty, add empty state
            const sourceList = document.querySelector(`.subjects-list[data-year-semester="${fromYearSemester}"]`);
            if (sourceList && sourceList.querySelectorAll('.subject-item').length === 0) {
                addEmptyState(sourceList);
            }
        }
    }

    /**
     * Add empty state to a list
     */
    function addEmptyState(list) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <i class="fas fa-inbox"></i>
            <p>No subjects assigned</p>
        `;
        list.appendChild(emptyState);
    }

    /**
     * Attach move dropdown listeners
     */
    function attachMoveDropdownListeners() {
        const dropdowns = document.querySelectorAll('.move-dropdown');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', handleMoveDropdownChange);
        });
    }

    /**
     * Handle move dropdown change
     */
    function handleMoveDropdownChange(e) {
        const targetYearSemester = e.target.value;
        if (!targetYearSemester) return;

        const subjectItem = e.target.closest('.subject-item');
        const fromYearSemester = e.target.dataset.from;
        const subjectCode = subjectItem.dataset.code;

        // Move subject
        moveSubject(subjectItem, fromYearSemester, targetYearSemester);

        // Show message
        showMessage('success', `${subjectCode} moved to ${targetYearSemester}`);

        // Update unit counts
        updateUnitCount(fromYearSemester);
        updateUnitCount(targetYearSemester);

        // Reset dropdown
        e.target.value = '';
    }

    /**
     * Attach subject item event listeners
     */
    function attachSubjectEventListeners(subjectItem) {
        subjectItem.addEventListener('dragstart', handleDragStart);
        subjectItem.addEventListener('dragend', resetDragState);

        const dropdown = subjectItem.querySelector('.move-dropdown');
        if (dropdown) {
            dropdown.addEventListener('change', handleMoveDropdownChange);
        }
    }

    /**
     * Reset drag state
     */
    function resetDragState() {
        if (draggedElement) {
            draggedElement.classList.remove('dragging');
        }
        allSubjectsLists.forEach(list => {
            list.classList.remove('drag-over');
        });
        draggedElement = null;
        dragSourceList = null;
    }

    /**
     * Update unit count for a specific year/semester
     */
    function updateUnitCount(yearSemester) {
        const list = document.querySelector(`.subjects-list[data-year-semester="${yearSemester}"]`);
        if (!list) return;

        const unitsBadge = document.getElementById(`units-${yearSemester.replace(/ /g, '-')}`);
        if (!unitsBadge) return;

        const subjectItems = list.querySelectorAll('.subject-item:not(.empty-state)');
        let totalUnits = 0;

        subjectItems.forEach(item => {
            const units = parseInt(item.dataset.units) || 0;
            totalUnits += units;
        });

        const totalUnitsSpan = unitsBadge.querySelector('.total-units');
        if (totalUnitsSpan) {
            totalUnitsSpan.textContent = totalUnits;
        }
    }

    /**
     * Update all unit counts
     */
    function updateAllUnitCounts() {
        const allBadges = document.querySelectorAll('.units-badge');
        allBadges.forEach(badge => {
            const text = badge.textContent.trim();
            const match = text.match(/Lost:\s*(\d+)\s*units/);
            if (match) {
                const yearSemester = badge.closest('.semester-section').querySelector('.semester-header h2').textContent.trim();
                updateUnitCount(yearSemester);
            }
        });
    }

    /**
     * Show message toast
     */
    function showMessage(type, message) {
        const messageText = document.getElementById('messageText');
        messageText.textContent = message;
        messageToast.className = `message-toast show ${type}`;

        setTimeout(() => {
            messageToast.classList.remove('show');
        }, 3000);
    }

    /**
     * Handle sidebar navigation
     */
    function handleNavigation(e) {
        e.preventDefault();

        // Update active state
        navItems.forEach(item => item.classList.remove('active'));
        this.classList.add('active');

        const section = this.dataset.section;
        console.log('Navigate to section:', section);

        // Load different pages based on section
        switch(section) {
            case 'program-setup':
                window.location.href = 'program-setup.php';
                break;
            case 'subject-management':
                window.location.href = 'subject-management.php';
                break;
            case 'course-scheduling':
                window.location.href = 'course-scheduling.php';
                break;
            case 'revision-log':
                window.location.href = 'revision-log-book.php';
                break;
        }
    }

    /**
     * Initialize draggable subjects on page load
     */
    function initializeDraggables() {
        const subjectItems = document.querySelectorAll('.subject-item');
        subjectItems.forEach(item => {
            attachSubjectEventListeners(item);
        });
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        init();
        initializeDraggables();
    });

    // Additional initialization for when script loads after DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            initializeDraggables();
        });
    } else {
        init();
        initializeDraggables();
    }
})();
