<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

if (!isset($_GET['ids'])) {
    die('No forms selected');
}

$form_ids = array_map('intval', explode(',', $_GET['ids']));
$user_id = $_SESSION['user_id'];

// Get forms details
$placeholders = str_repeat('?,', count($form_ids) - 1) . '?';
$query = "SELECT * FROM purchases WHERE id IN ($placeholders) AND created_by = ? ORDER BY id ASC";

$stmt = $conn->prepare($query);
$types = str_repeat('i', count($form_ids)) . 'i';
$params = array_merge($form_ids, [$user_id]);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('No forms found or access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Purchase Forms - KIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
        .form-section {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 2rem;
            margin-bottom: 2rem;
        }
        .form-section:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body class="bg-white">
     <div class="company-header">
        <h2>KIMS Purchase Form</h2>
        <p class="text-muted">Form #<?= str_pad($form['id'], 5, '0', STR_PAD_LEFT) ?></p>
    </div>
    <!-- Print Controls -->
    <div class="container-fluid mb-4 no-print">
        <div class="row">
            <div class="col-12 py-3">
                <button onclick="window.print()" class="btn btn-info text-white">
                    <i class="bi bi-printer"></i> Print All Forms
                </button>
                <button onclick="window.close()" class="btn btn-light">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php while ($form = $result->fetch_assoc()): ?>
            <div class="form-section">
                <div class="text-center mb-4">
                    <h2>KIMS Purchase Form</h2>
                    <p class="text-muted">Form #<?= str_pad($form['id'], 5, '0', STR_PAD_LEFT) ?></p>
                </div>

                <!-- Form Content -->
                <div class="row">
                    <!-- Company Details -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Company Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Company Name:</td>
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
                        </table>
                    </div>

                    <!-- Financial Details -->
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Financial Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Amount:</td>
                                <td><strong>GHC <?= number_format($form['amount'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td><span class="badge <?= $form['status'] === 'Complete' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $form['status'] ?>
                                </span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Submitted:</td>
                                <td><?= date('F d, Y', strtotime($form['submitted_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>