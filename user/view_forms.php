<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Set page variables
$pageTitle = 'My Purchase Forms';
$showBackButton = true;

// Build query with filters
$query = "SELECT * FROM purchases WHERE created_by = ?";
$params = array($_SESSION['user_id']);
$types = "i";

// Add company filter if set
if (!empty($_GET['company'])) {
    $query .= " AND company_name LIKE ?";
    $params[] = "%" . $_GET['company'] . "%";
    $types .= "s";
}

// Add date filters
if (!empty($_GET['year'])) {
    $query .= " AND YEAR(submitted_at) = ?";
    $params[] = $_GET['year'];
    $types .= "i";
}

if (!empty($_GET['month'])) {
    $query .= " AND MONTH(submitted_at) = ?";
    $params[] = $_GET['month'];
    $types .= "i";
}

if (!empty($_GET['day'])) {
    $query .= " AND DAY(submitted_at) = ?";
    $params[] = $_GET['day'];
    $types .= "i";
}

// Add status filter if set
if (!empty($_GET['status'])) {
    $query .= " AND status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// Order by submitted_at ascending
$query .= " ORDER BY submitted_at ASC";

// Prepare and execute query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Include header
require_once '../includes/header.php';
?>

<style>
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    margin-top: 0.25rem;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.dropdown-item:hover,
.dropdown-item.active {
    background-color: #0dcaf0;
    color: #fff;
}

.dropdown-item.disabled {
    color: #6c757d;
    cursor: default;
}

.dropdown-item.disabled:hover {
    background-color: transparent;
    color: #6c757d;
}

.form-check-input {
    cursor: pointer;
}

.table tbody tr:hover .form-check-input {
    opacity: 1;
}

.form-check-input {
    opacity: 0.7;
    transition: all 0.2s ease;
}

.btn-group .btn-outline-primary:hover {
    color: #fff;
    background-color: #0d6efd;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table-active {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.table-active:hover {
    background-color: rgba(13, 202, 240, 0.15) !important;
}

#bulkActions {
    transition: all 0.3s ease;
}

/* Add this to your existing styles */
.alert {
    transition: opacity 0.3s ease-in-out;
}

.alert.fade {
    opacity: 0;
}

.alert.show {
    opacity: 1;
}

tr {
    transition: opacity 0.3s ease-in-out;
}
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">Purchase Report</h1>
            <p class="text-muted">View and manage your submitted purchase report</p>
        </div>
        <a href="../purchase/form.php" class="btn btn-info text-white">
            <i class="bi bi-plus-circle me-2"></i>New Form
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filter by Company</label>
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-building"></i>
                            </span>
                            <input type="text" class="form-control" id="companyFilter" name="company" 
                                   placeholder="Start typing company name..."
                                   value="<?= isset($_GET['company']) ? htmlspecialchars($_GET['company']) : '' ?>"
                                   autocomplete="off">
                        </div>
                        <div id="companySuggestions" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Day</label>
                    <select class="form-select" name="day">
                        <option value="">Any Day</option>
                        <?php for($i = 1; $i <= 31; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['day']) && $_GET['day'] == $i) ? 'selected' : '' ?>>
                                <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select class="form-select" name="month">
                        <option value="">Any Month</option>
                        <?php 
                        $months = array(
                            1 => 'January', 2 => 'February', 3 => 'March',
                            4 => 'April', 5 => 'May', 6 => 'June',
                            7 => 'July', 8 => 'August', 9 => 'September',
                            10 => 'October', 11 => 'November', 12 => 'December'
                        );
                        foreach($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= (isset($_GET['month']) && $_GET['month'] == $num) ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year">
                        <option value="">Any Year</option>
                        <?php 
                        $currentYear = date('Y');
                        for($year = $currentYear; $year >= $currentYear - 5; $year--): ?>
                            <option value="<?= $year ?>" <?= (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <div class="d-flex gap-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="Pending" <?= (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="Complete" <?= (isset($_GET['status']) && $_GET['status'] === 'Complete') ? 'selected' : '' ?>>Complete</option>
                        </select>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-filter"></i>
                        </button>
                        <a href="view_forms.php" class="btn btn-light">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" class="card shadow-sm mb-4 d-none">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <span id="selectedCount" class="text-muted"></span>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-info text-white" id="printSelected">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button type="button" class="btn btn-light" id="deselectAll">
                    <i class="bi bi-x-circle"></i> Deselect
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($_GET['company']) || !empty($_GET['status']) || !empty($_GET['year']) || !empty($_GET['month']) || !empty($_GET['day'])): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted">
                <i class="bi bi-filter"></i>
                Showing <?= $result->num_rows ?> filtered results
                <?php
                $filters = array();
                if (!empty($_GET['company'])) {
                    $filters[] = 'company "' . htmlspecialchars($_GET['company']) . '"';
                }
                if (!empty($_GET['status'])) {
                    $filters[] = 'status "' . htmlspecialchars($_GET['status']) . '"';
                }
                if (!empty($_GET['day']) || !empty($_GET['month']) || !empty($_GET['year'])) {
                    $date_parts = array();
                    if (!empty($_GET['day'])) $date_parts[] = 'day ' . $_GET['day'];
                    if (!empty($_GET['month'])) $date_parts[] = $months[$_GET['month']];
                    if (!empty($_GET['year'])) $date_parts[] = $_GET['year'];
                    $filters[] = implode(' ', $date_parts);
                }
                echo !empty($filters) ? ' for ' . implode(', ', $filters) : '';
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="card shadow">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>No.</th>
                            <th>Company</th>
                            <th>Award No.</th>
                            <th>Award Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1; // Initialize counter
                        while($form = $result->fetch_assoc()): 
                        ?>
                            <tr data-id="<?= $form['id'] ?>">
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input form-check-row" type="checkbox" 
                                               value="<?= $form['id'] ?>" 
                                               data-form-id="<?= $form['id'] ?>">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= $counter ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($form['company_name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($form['award_no']) ?></td>
                                <td><?= date('M d, Y', strtotime($form['award_date'])) ?></td>
                                <td>
                                    <span class="text-success fw-bold">
                                        GHC <?= number_format($form['amount'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $form['status'] == 'Complete' ? 'status-complete' : 'status-pending' ?>">
                                        <?= $form['status'] == 'Complete' ? 'Complete' : 'Pending' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y H:i', strtotime($form['submitted_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="viewDetails(<?= $form['id'] ?>)"
                                                data-bs-toggle="modal" data-bs-target="#detailsModal">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <?php if ($form['attachment_path']): ?>
                                            <a href="<?= htmlspecialchars($form['attachment_path']) ?>" 
                                               class="btn btn-outline-secondary btn-sm" 
                                               target="_blank">
                                                <i class="bi bi-paperclip"></i> File
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="deleteForm(<?= $form['id'] ?>)">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        $counter++; // Increment counter
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="card shadow">
                <div class="card-body">
                    <i class="bi bi-inbox display-1 text-muted mb-4"></i>
                    <h3 class="text-muted">No Purchase Forms Found</h3>
                    <p class="text-muted mb-4">You haven't submitted any purchase forms yet.</p>
                    <a href="../purchase/form.php" class="btn btn-info text-white btn-lg">
                        <i class="bi bi-plus-circle"></i> Submit Your First Form
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-text"></i> Purchase Form Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <div class="text-center py-3">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this purchase form? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash"></i> Delete Form
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewDetails(formId) {
        // Show loading spinner
        document.getElementById('modalContent').innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Fetch form details via AJAX
        fetch('get_form_details.php?id=' + formId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('modalContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error loading form details. Please try again.
                    </div>
                `;
            });
    }

    // Clear individual filters
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('keyup', function(e) {
            // Clear on Escape key
            if (e.key === 'Escape') {
                this.value = '';
            }
        });
    });

    // Add loading state to filter form
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelector('button[type="submit"]').innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Filtering...
        `;
    });

    // Company autocomplete
    const companyInput = document.getElementById('companyFilter');
    const suggestionsList = document.getElementById('companySuggestions');
    let typingTimer;

    companyInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        
        // Clear suggestions if input is empty
        if (this.value.trim() === '') {
            suggestionsList.style.display = 'none';
            return;
        }
        
        // Wait for user to stop typing
        typingTimer = setTimeout(() => {
            fetch('get_companies.php?term=' + encodeURIComponent(this.value))
                .then(response => response.json())
                .then(companies => {
                    if (companies.length > 0) {
                        suggestionsList.innerHTML = companies.map(company => `
                            <button class="dropdown-item" type="button">
                                <i class="bi bi-building me-2"></i>${company}
                            </button>
                        `).join('');
                        
                        // Add click handlers to suggestions
                        suggestionsList.querySelectorAll('.dropdown-item').forEach(item => {
                            item.addEventListener('click', function() {
                                companyInput.value = this.textContent.trim();
                                suggestionsList.style.display = 'none';
                                // Submit the form
                                document.querySelector('form').submit();
                            });
                        });
                        
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.innerHTML = `
                            <div class="dropdown-item disabled">
                                <i class="bi bi-info-circle me-2"></i>No matches found
                            </div>
                        `;
                        suggestionsList.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    suggestionsList.style.display = 'none';
                });
        }, 300); // 300ms delay
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!companyInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Navigate suggestions with keyboard
    companyInput.addEventListener('keydown', function(e) {
        const items = suggestionsList.querySelectorAll('.dropdown-item:not(.disabled)');
        const currentIndex = Array.from(items).findIndex(item => item.classList.contains('active'));
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (suggestionsList.style.display === 'none') {
                    // Show suggestions if hidden
                    const event = new Event('input');
                    companyInput.dispatchEvent(event);
                } else {
                    const nextIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0;
                    items.forEach(item => item.classList.remove('active'));
                    items[nextIndex]?.classList.add('active');
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                if (items.length) {
                    const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
                    items.forEach(item => item.classList.remove('active'));
                    items[prevIndex]?.classList.add('active');
                }
                break;
                
            case 'Enter':
                e.preventDefault();
                const activeItem = suggestionsList.querySelector('.dropdown-item.active');
                if (activeItem) {
                    companyInput.value = activeItem.textContent.trim();
                    suggestionsList.style.display = 'none';
                    document.querySelector('form').submit();
                }
                break;
                
            case 'Escape':
                suggestionsList.style.display = 'none';
                break;
        }
    });

    // Form selection handling
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxes = document.getElementsByClassName('form-check-row');

    // Select/Deselect all
    selectAllCheckbox.addEventListener('change', function() {
        Array.from(checkboxes).forEach(checkbox => {
            checkbox.checked = this.checked;
            updateRowSelection(checkbox);
        });
        updateBulkActions();
    });

    // Individual checkbox handling
    Array.from(checkboxes).forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateRowSelection(this);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            updateBulkActions();
        });
    });

    // Bulk actions handling
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const printSelected = document.getElementById('printSelected');
    const deselectAll = document.getElementById('deselectAll');

    function updateBulkActions() {
        const selectedForms = Array.from(checkboxes).filter(cb => cb.checked);
        if (selectedForms.length > 0) {
            bulkActions.classList.remove('d-none');
            selectedCount.textContent = `${selectedForms.length} form${selectedForms.length > 1 ? 's' : ''} selected`;
        } else {
            bulkActions.classList.add('d-none');
        }
    }

    // Update checkbox handling to include bulk actions
    selectAllCheckbox.addEventListener('change', function() {
        Array.from(checkboxes).forEach(checkbox => {
            checkbox.checked = this.checked;
            updateRowSelection(checkbox);
        });
        updateBulkActions();
    });

    
    Array.from(checkboxes).forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateRowSelection(this);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            updateBulkActions();
        });
    });

    function updateRowSelection(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.classList.add('table-active');
        } else {
            row.classList.remove('table-active');
        }
    }

    // Deselect all handler
    deselectAll.addEventListener('click', function() {
        selectAllCheckbox.checked = false;
        Array.from(checkboxes).forEach(checkbox => {
            checkbox.checked = false;
            updateRowSelection(checkbox);
        });
        updateBulkActions();
    });

    // Print selected forms
    printSelected.addEventListener('click', function() {
        const selectedIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        if (selectedIds.length > 0) {
            // Show loading state
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
            
            Promise.all(selectedIds.map(id => 
                fetch('get_form_details.php?id=' + id)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
            ))
            .then(details => {
                // Open print window
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>KIMS - Print Forms</title>
                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
                        <style>
                            @media print {
                                @page { 
                                    size: A4;
                                    margin: 1.5cm;
                                }
                                .no-print { 
                                    display: none !important; 
                                }
                                .page-break { 
                                    page-break-before: always; 
                                }
                            } /* Add this closing brace */
                            .form-section {
                                padding: 2rem 0;
                                margin-bottom: 2rem;
                            }
                            .form-header {
                                text-align: center;
                                margin-bottom: 2rem;
                                padding-bottom: 1rem;
                                border-bottom: 2px solid #dee2e6;
                            }
                        </style>
                    </head>
                    <body class="bg-white">
                        <!-- Print Controls -->
                        <div class="container-fluid mb-4 no-print">
                            <div class="row">
                                <div class="col-12 py-3">
                                    <button onclick="window.print()" class="btn btn-info text-white">
                                        <i class="bi bi-printer"></i> Print Forms
                                    </button>
                                    <button onclick="window.close()" class="btn btn-light ms-2">
                                        <i class="bi bi-x-lg"></i> Close
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Forms Content -->
                        ${details.map((detail, index) => `
                            <div class="form-section ${index > 0 ? 'page-break' : ''}">
                                <div class="form-header">
                                    <h2>KIMS Purchase Form</h2>
                                    <p class="text-muted">Generated on ${new Date().toLocaleString()}</p>
                                </div>
                                ${detail}
                                <div class="text-center text-muted mt-4">
                                    <small>Page ${index + 1} of ${details.length}</small>
                                </div>
                            </div>
                        `).join('')}
                    </body>
                    </html>
                `);

                // Reset button state
                this.innerHTML = '<i class="bi bi-printer"></i> Print Selected';
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = '<i class="bi bi-printer"></i> Print Selected';
                alert('Error loading form details. Please try again.');
            });
        }
    });

    // Add event delegation for checkboxes
    document.querySelector('table').addEventListener('change', function(e) {
        if (e.target.classList.contains('form-check-row')) {
            updateRowSelection(e.target);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            updateBulkActions();
        }
    });

    // Delete form handling
    let formToDelete = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    function deleteForm(formId) {
        if (!confirm('Are you sure you want to delete this form? This action cannot be undone.')) {
            return;
        }

        // Show loading state on the delete button
        const deleteBtn = document.querySelector(`button[onclick="deleteForm(${formId})"]`);
        const originalBtnContent = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Deleting...';
        deleteBtn.disabled = true;

        const form = new FormData();
        form.append('id', formId);

        fetch('delete_form.php', {
            method: 'POST',
            body: form
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find and remove the row immediately
                const row = deleteBtn.closest('tr');
                if (row) {
                    // Fade out animation
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        
                        // Update row count if exists
                        const rowCount = document.querySelector('.row-count');
                        if (rowCount) {
                            const currentCount = parseInt(rowCount.textContent);
                            rowCount.textContent = currentCount > 0 ? currentCount - 1 : 0;
                        }
                    }, 300); // Match the CSS transition duration
                }
            } else {
                alert('Error deleting form. Please try again.');
                deleteBtn.innerHTML = originalBtnContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting form. Please try again.');
            deleteBtn.innerHTML = originalBtnContent;
            deleteBtn.disabled = false;
        });
    }
</script>

<?php
// Close database connection
$conn->close();
?>