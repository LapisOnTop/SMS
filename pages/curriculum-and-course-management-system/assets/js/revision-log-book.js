(function() {
    'use strict';

    // DOM Elements
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const resetBtn = document.getElementById('resetBtn');
    const messageToast = document.getElementById('messageToast');
    const logsTableBody = document.getElementById('logsTableBody');
    const totalLogsCount = document.getElementById('totalLogsCount');
    const navItems = document.querySelectorAll('.nav-item');

    /**
     * Initialize all event listeners
     */
    function init() {
        exportCsvBtn.addEventListener('click', handleExportCsv);
        resetBtn.addEventListener('click', handleReset);

        // Sidebar navigation
        navItems.forEach(item => {
            item.addEventListener('click', handleNavigation);
        });
    }

    /**
     * Handle export to CSV
     */
    function handleExportCsv() {
        const table = document.querySelector('.logs-table');
        if (!table) {
            showMessage('error', 'No data available to export');
            return;
        }

        // Get all rows
        const rows = table.querySelectorAll('tr');
        const csvContent = [];

        // Header
        const headers = [];
        table.querySelector('thead tr').querySelectorAll('th').forEach(th => {
            headers.push(escapeCSV(th.textContent.trim()));
        });
        csvContent.push(headers.join(','));

        // Body rows
        table.querySelectorAll('tbody tr').forEach(tr => {
            const rowData = [];
            tr.querySelectorAll('td').forEach(td => {
                let text = '';
                
                if (td.classList.contains('timestamp-cell')) {
                    // Extract timestamp
                    text = td.querySelector('span')?.textContent.trim() || '';
                } else if (td.classList.contains('action-cell')) {
                    // Extract action badge
                    text = td.querySelector('.action-badge')?.textContent.trim() || '';
                } else if (td.classList.contains('details-cell')) {
                    // Extract details
                    text = td.querySelector('.details-text')?.textContent.trim() || '';
                } else {
                    text = td.textContent.trim();
                }
                
                rowData.push(escapeCSV(text));
            });
            csvContent.push(rowData.join(','));
        });

        // Create blob and download
        const csvString = csvContent.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        const timestamp = new Date().toISOString().slice(0, 10);
        link.setAttribute('href', url);
        link.setAttribute('download', `revision_logs_${timestamp}.csv`);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showMessage('success', `Exported ${table.querySelectorAll('tbody tr').length} logs to CSV`);
    }

    /**
     * Escape CSV values
     */
    function escapeCSV(value) {
        if (!value) return '""';
        
        // If contains comma, newline, or double quotes, wrap in quotes and escape double quotes
        if (value.includes(',') || value.includes('\n') || value.includes('"')) {
            return '"' + value.replace(/"/g, '""') + '"';
        }
        
        return value;
    }

    /**
     * Handle reset with confirmation
     */
    function handleReset() {
        if (!confirm('Are you sure you want to clear all revision logs? This action cannot be undone.')) {
            return;
        }

        if (!confirm('This will permanently delete all recorded changes. Continue?')) {
            return;
        }

        // TODO: Send to API endpoint to clear logs
        // DELETE /api/clear-revision-logs.php

        // Clear table
        const currentRows = logsTableBody.querySelectorAll('.log-row');
        const rowCount = currentRows.length;

        currentRows.forEach(row => {
            row.remove();
        });

        // Update count
        totalLogsCount.textContent = '0';

        // Add empty state
        addEmptyState();

        showMessage('success', `Cleared ${rowCount} revision logs`);
    }

    /**
     * Add empty state to table
     */
    function addEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-state-row';
        emptyRow.innerHTML = `
            <td colspan="3" style="text-align: center; padding: 40px 20px; color: var(--text-light);">
                <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;"></i>
                <p style="margin: 0; font-size: 14px;">No logs available</p>
            </td>
        `;
        logsTableBody.appendChild(emptyRow);
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

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', init);

    // Additional initialization for when script loads after DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
