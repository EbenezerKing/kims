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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Form | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Purchase Form</h4>
        </div>
        <div class="card-body">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="handle_form.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="companyname" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="companyname" name="companyname" required 
                           list="companies" value="<?= isset($_POST['companyname']) ? htmlspecialchars($_POST['companyname']) : '' ?>">
                    <datalist id="companies">
                        <?php while($company = $companies_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($company['name']) ?>">
                        <?php endwhile; ?>
                    </datalist>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="awarddate" class="form-label">Award Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="awarddate" name="awarddate" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="awardno" class="form-label">Award Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="awardno" name="awardno" required>
                    </div>
                    <div class="col-md-4">
                        <label for="waybillno" class="form-label">WAYBILL Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="waybillno" name="waybillno">
                    </div>
                    <div class="col-md-4">
                        <label for="invoiceno" class="form-label">INVOICE Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="invoiceno" name="invoiceno">
                    </div>
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
                        <label for="balance" class="form-label">Balance <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="balance" name="balance" required>
                    </div>
                    <div class="col-md-4">
                        <label for="batchno" class="form-label">Batch Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="batchno" name="batchno" required>
                    </div>
                    <div class="col-md-4">
                        <label for="expirydate" class="form-label">Expiry Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expirydate" name="expirydate" required>
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
                    <div class="col-md-4">
                        <label for="lpono" class="form-label">LPO Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lpono" name="lpono" required>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">--Select--</option>
                            <option value="Pending">Pending</option>
                            <option value="Complete">Complete</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label for="details" class="form-label">Details <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="details" name="details" required rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="attachment" class="form-label">Attachment (JPG, PNG, PDF, max 5MB)</label>
                    <input type="file" class="form-control" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <button type="submit" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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