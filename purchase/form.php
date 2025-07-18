<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Handle form messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Get companies for datalist
$companies_query = "SELECT name FROM companies ORDER BY name ASC";
$companies_result = $conn->query($companies_query);

// Check if query was successful
if (!$companies_result) {
    die("Error fetching companies: " . $conn->error);
}

// Store companies in an array to avoid issues with result pointer
$companies = [];
while($company = $companies_result->fetch_assoc()) {
    $companies[] = $company['name'];
}

$pageTitle = 'New Purchase Form';
$showBackButton = true;
require_once '../includes/header.php';
?>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">New Purchase Form</h1>
            <p class="text-muted">Create and submit a new purchase request</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card shadow-lg border-0">
        <div class="card-header bg-gradient-info text-white p-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-text fs-3 me-2"></i>
                <h4 class="mb-0">Purchase Details</h4>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if ($success_message): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="handle_form.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                <!-- Company Selection -->
                <div class="mb-4">
                    <label for="companyname" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-building"></i>
                            </span>
                            <input type="text" class="form-control" id="companyname" name="companyname" required 
                                   placeholder="Start typing to search companies..."
                                   value="<?= isset($_POST['companyname']) ? htmlspecialchars($_POST['companyname']) : '' ?>"
                                   autocomplete="off">
                        </div>
                        <div id="company-suggestions" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                    </div>
                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Select from existing companies in the database</div>
                    <input type="hidden" id="company-selected" name="company_selected" value="0">
                </div>

                <!-- Form Sections -->
                <div class="row g-4">
                    <!-- Award Details Section -->
                    <div class="col-12">
                        <h5 class="text-info mb-3"><i class="bi bi-trophy me-2"></i>Award Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Award Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar"></i></span>
                                    <input type="date" class="form-control" id="awarddate" name="awarddate" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Award Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control" id="awardno" name="awardno" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">LPO Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-file-text"></i></span>
                                    <input type="text" class="form-control" id="lpono" name="lpono" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Details Section -->
                    <div class="col-12">
                        <h5 class="text-info mb-3"><i class="bi bi-truck me-2"></i>Delivery Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="waybillno" class="form-label">WAYBILL Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="waybillno" name="waybillno">
                            </div>
                            <div class="col-md-4">
                                <label for="invoiceno" class="form-label">INVOICE Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="invoiceno" name="invoiceno">
                            </div>
                            <div class="col-md-4">
                                <label for="deliverydate" class="form-label">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="deliverydate" name="deliverydate" required>
                            </div>
                            <div class="col-md-4">
                                <label for="deliveryaddress" class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="deliveryaddress" name="deliveryaddress" required>
                            </div>
                            <div class="col-md-4">
                                <label for="contactperson" class="form-label">Contact Person</label>
                                <input type="number" class="form-control" id="contactperson" name="contactperson">
                            </div>
                            <div class="col-md-4">
                                <label for="contactnumber" class="form-label">Contact Number</label>
                                <input type="number" class="form-control" id="contactnumber" name="contactnumber">
                            </div>
                        </div>
                    </div>

                    <!-- Quantity and Price Section -->
                    <div class="col-12">
                        <h5 class="text-info mb-3"><i class="bi bi-calculator me-2"></i>Quantity & Price Details</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="quantityordered" class="form-label">Quantity Ordered <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantityordered" name="quantityordered" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="quantityreceived" class="form-label">Quantity Received <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantityreceived" name="quantityreceived" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="unitofcount" class="form-label">Unit of Count <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="unitofcount" name="unitofcount" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label for="srano" class="form-label">SRA Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="srano" name="srano" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sradate" class="form-label">SRA Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="sradate" name="sradate" required>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details Section -->
                    <div class="col-12">
                        <h5 class="text-info mb-3"><i class="bi bi-card-text me-2"></i>Additional Information</h5>
                        <div class="mb-3">
                            <label class="form-label">Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="details" name="details" required rows="3" 
                                      placeholder="Enter additional details about the purchase..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachment</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-paperclip"></i></span>
                                <input type="file" class="form-control" id="attachment" name="attachment" 
                                       accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="form-text">Accepted formats: JPG, PNG, PDF (max 5MB)</div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-check-circle me-2"></i>Submit Purchase Form
                    </button>
                    <a href="../user/dashboard.php" class="btn btn-light">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Companies data from PHP
    const companies = <?= json_encode($companies) ?>;
    
    const companyInput = document.getElementById('companyname');
    const suggestionBox = document.getElementById('company-suggestions');
    const companySelected = document.getElementById('company-selected');
    const form = companyInput.closest('form');
    
    let selectedIndex = -1;
    let filteredCompanies = [];
    
    // Function to filter companies based on input
    function filterCompanies(searchTerm) {
        return companies.filter(company => 
            company.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }
    
    // Function to show suggestions
    function showSuggestions(searchTerm) {
        filteredCompanies = filterCompanies(searchTerm);
        suggestionBox.innerHTML = '';
        selectedIndex = -1;
        
        if (filteredCompanies.length === 0) {
            suggestionBox.style.display = 'none';
            return;
        }
        
        filteredCompanies.forEach((company, index) => {
            const item = document.createElement('a');
            item.className = 'dropdown-item';
            item.href = '#';
            item.textContent = company;
            item.addEventListener('click', function(e) {
                e.preventDefault();
                selectCompany(company);
            });
            suggestionBox.appendChild(item);
        });
        
        suggestionBox.style.display = 'block';
    }
    
    // Function to hide suggestions
    function hideSuggestions() {
        setTimeout(() => {
            suggestionBox.style.display = 'none';
        }, 200);
    }
    
    // Function to select a company
    function selectCompany(company) {
        companyInput.value = company;
        companySelected.value = '1';
        companyInput.classList.remove('is-invalid');
        companyInput.classList.add('is-valid');
        suggestionBox.style.display = 'none';
    }
    
    // Function to validate company selection
    function validateCompany() {
        const inputValue = companyInput.value.trim();
        const isValid = companies.includes(inputValue);
        
        if (inputValue === '') {
            companyInput.classList.remove('is-valid', 'is-invalid');
            companySelected.value = '0';
        } else if (isValid) {
            companyInput.classList.remove('is-invalid');
            companyInput.classList.add('is-valid');
            companySelected.value = '1';
        } else {
            companyInput.classList.remove('is-valid');
            companyInput.classList.add('is-invalid');
            companySelected.value = '0';
        }
        
        return isValid || inputValue === '';
    }
    
    // Event listeners for company input
    companyInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        if (searchTerm.length >= 1) {
            showSuggestions(searchTerm);
        } else {
            hideSuggestions();
        }
        
        validateCompany();
    });
    
    companyInput.addEventListener('blur', function() {
        hideSuggestions();
        validateCompany();
    });
    
    companyInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            showSuggestions(this.value.trim());
        }
    });
    
    // Keyboard navigation
    companyInput.addEventListener('keydown', function(e) {
        const items = suggestionBox.querySelectorAll('.dropdown-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && items[selectedIndex]) {
                selectCompany(items[selectedIndex].textContent);
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });
    
    // Function to update visual selection
    function updateSelection(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    // Form validation before submit
    form.addEventListener('submit', function(e) {
        if (!validateCompany()) {
            e.preventDefault();
            companyInput.focus();
            alert('Please select a valid company from the list.');
            return false;
        }
        
        if (companySelected.value !== '1') {
            e.preventDefault();
            companyInput.focus();
            alert('Please select a company from the available options.');
            return false;
        }
    });
    
    // Initial validation if there's a pre-filled value
    if (companyInput.value.trim() !== '') {
        validateCompany();
    }

    // Auto-calculate amount
    document.querySelectorAll('#price, #quantityreceived').forEach(function(element) {
        element.addEventListener('change', function() {
            var price = parseFloat(document.getElementById('price').value) || 0;
            var qty = parseFloat(document.getElementById('quantityreceived').value) || 0;
            document.getElementById('amount').value = (price * qty).toFixed(2);
        });
    });

    // Auto-calculate balance
    document.querySelectorAll('#quantityordered, #quantityreceived').forEach(function(element) {
        element.addEventListener('change', function() {
            var ordered = parseInt(document.getElementById('quantityordered').value) || 0;
            var received = parseInt(document.getElementById('quantityreceived').value) || 0;
            document.getElementById('balance').value = ordered - received;
        });
    });
});
</script>
</body>
</html>