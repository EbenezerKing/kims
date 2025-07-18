<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Ensure user has appropriate role
if ($_SESSION['role'] !== 'user') {
    http_response_code(403);
    exit('Access denied');
}

// Validate form ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('Invalid form ID');
}

$form_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get form details - ensure user can only view their own forms
$query = "SELECT * FROM purchases WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $form_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit('Form not found or access denied');
}

$form = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-info mb-3"><i class="bi bi-building"></i> Company Information</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>Company Name:</strong></td>
                <td><?= htmlspecialchars($form['company_name']) ?></td>
            </tr>
        </table>

        <h6 class="text-info mb-3 mt-4"><i class="bi bi-calendar-check"></i> Award Details</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>Award Date:</strong></td>
                <td><?= date('F d, Y', strtotime($form['award_date'])) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Award Number:</strong></td>
                <td><?= htmlspecialchars($form['award_no']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>LPO Number:</strong></td>
                <td><?= htmlspecialchars($form['lpo_no']) ?></td>
            </tr>
        </table>

        <h6 class="text-info mb-3 mt-4"><i class="bi bi-file-earmark-text"></i> Document Numbers</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>Waybill Number:</strong></td>
                <td><?= htmlspecialchars($form['waybill_no']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Invoice Number:</strong></td>
                <td><?= htmlspecialchars($form['invoice_no']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Batch Number:</strong></td>
                <td><?= htmlspecialchars($form['batch_no']) ?></td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6 class="text-info mb-3"><i class="bi bi-box"></i> Quantity Information</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>Quantity Ordered:</strong></td>
                <td><?= number_format($form['quantity_ordered']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Quantity Received:</strong></td>
                <td><?= number_format($form['quantity_received']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Unit of Count:</strong></td>
                <td><?= number_format($form['unit_of_count']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Balance:</strong></td>
                <td><?= htmlspecialchars($form['balance']) ?></td>
            </tr>
        </table>

        <h6 class="text-info mb-3 mt-4"><i class="bi bi-currency-exchange"></i> Financial Details</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>Unit Price:</strong></td>
                <td class="text-success">GHC <?= number_format($form['price'], 2) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Total Amount:</strong></td>
                <td class="text-success fw-bold">GHC <?= number_format($form['amount'], 2) ?></td>
            </tr>
        </table>

        <h6 class="text-info mb-3 mt-4"><i class="bi bi-clipboard-check"></i> SRA Information</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted"><strong>SRA Number:</strong></td>
                <td><?= htmlspecialchars($form['sra_no']) ?></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>SRA Date:</strong></td>
                <td><?= date('F d, Y', strtotime($form['sra_date'])) ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-info mb-3"><i class="bi bi-calendar-x"></i> Expiry & Status</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted" style="width: 150px;"><strong>Expiry Date:</strong></td>
                <td>
                    <?php 
                    $expiry_date = strtotime($form['expiry_date']);
                    $is_expired = $expiry_date < time();
                    ?>
                    <span class="<?= $is_expired ? 'text-danger' : 'text-success' ?>">
                        <?= date('F d, Y', $expiry_date) ?>
                        <?php if ($is_expired): ?>
                            <i class="bi bi-exclamation-triangle ms-1" title="Expired"></i>
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Status:</strong></td>
                <td>
                    <span class="badge <?= $form['status'] == 'Complete' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <i class="bi bi-<?= $form['status'] == 'Complete' ? 'check-circle' : 'clock' ?>"></i>
                        <?= $form['status'] ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-info mb-3"><i class="bi bi-chat-text"></i> Additional Details</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($form['details'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($form['attachment_path']): ?>
<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-info mb-3"><i class="bi bi-paperclip"></i> Attachment</h6>
        <div class="card bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark text-secondary me-2"></i>
                    <div>
                        <strong>Attached File:</strong>
                        <a href="<?= htmlspecialchars($form['attachment_path']) ?>" 
                           target="_blank" class="btn btn-outline-info btn-sm ms-2">
                            <i class="bi bi-download"></i> View/Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-info mb-3"><i class="bi bi-info-circle"></i> Submission Info</h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="text-muted" style="width: 150px;"><strong>Form ID:</strong></td>
                <td><span class="badge bg-secondary">#<?= $form['id'] ?></span></td>
            </tr>
            <tr>
                <td class="text-muted"><strong>Submitted:</strong></td>
                <td><?= date('F d, Y \a\t H:i:s', strtotime($form['submitted_at'])) ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <a href="print_form.php?id=<?= $form['id'] ?>" target="_blank" class="btn btn-info text-white">
            <i class="bi bi-printer"></i> Print Form
        </a>
    </div>
</div>

<?php 
$stmt->close();
$conn->close(); 
?>
