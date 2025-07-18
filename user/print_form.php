<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Validate form ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid form ID');
}

$form_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get form details
$query = "SELECT * FROM purchases WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $form_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Form not found or access denied');
}

$form = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Form #<?= $form['id'] ?> - Print View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .table {
                font-size: 12px;
            }
            .table td {
                padding: 0.5rem;
            }
        }
        .company-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-title {
            border-bottom: 2px solid #0dcaf0;
            padding-bottom: 0.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Print Header -->
    <div class="company-header">
        <h2>KIMS Purchase Form</h2>
        <p class="text-muted">Form #<?= str_pad($form['id'], 5, '0', STR_PAD_LEFT) ?></p>
    </div>

    <!-- Print Controls -->
    <div class="container-fluid mb-4 no-print">
        <div class="row">
            <div class="col-12">
                <button onclick="window.print()" class="btn btn-info text-white">
                    <i class="bi bi-printer"></i> Print Form
                </button>
                <a href="view_forms.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Back to Forms
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Form Content -->
        <div class="row">
            <div class="col-md-6">
                <h5 class="form-title">Company & Award Information</h5>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" style="width: 40%">Company Name:</td>
                        <td><strong><?= htmlspecialchars($form['company_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Award Number:</td>
                        <td><?= htmlspecialchars($form['award_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Award Date:</td>
                        <td><?= date('F d, Y', strtotime($form['award_date'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">LPO Number:</td>
                        <td><?= htmlspecialchars($form['lpo_no']) ?></td>
                    </tr>
                </table>

                <h5 class="form-title mt-4">Document Information</h5>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" style="width: 40%">Waybill Number:</td>
                        <td><?= htmlspecialchars($form['waybill_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Invoice Number:</td>
                        <td><?= htmlspecialchars($form['invoice_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Batch Number:</td>
                        <td><?= htmlspecialchars($form['batch_no']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h5 class="form-title">Quantity & Financial Details</h5>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" style="width: 40%">Quantity Ordered:</td>
                        <td><?= number_format($form['quantity_ordered']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Quantity Received:</td>
                        <td><?= number_format($form['quantity_received']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Unit Price:</td>
                        <td>GHC <?= number_format($form['price'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Amount:</td>
                        <td><strong>GHC <?= number_format($form['amount'], 2) ?></strong></td>
                    </tr>
                </table>

                <h5 class="form-title mt-4">Status Information</h5>
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted" style="width: 40%">Status:</td>
                        <td>
                            <span class="badge <?= $form['status'] == 'Complete' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $form['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Expiry Date:</td>
                        <td><?= date('F d, Y', strtotime($form['expiry_date'])) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Submission Date:</td>
                        <td><?= date('F d, Y', strtotime($form['submitted_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="form-title">Additional Details</h5>
                <div class="border p-3 bg-light">
                    <?= nl2br(htmlspecialchars($form['details'])) ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-5">
            <div class="col-12">
                <hr>
                <div class="text-center text-muted small">
                    <p>Printed on <?= date('F d, Y \a\t h:i A') ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php 
$stmt->close();
$conn->close(); 
?>