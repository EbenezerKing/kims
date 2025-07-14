<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

// Ensure user has appropriate role
if ($_SESSION['role'] !== 'user') {
    header("Location: ../auth/user_login.php");
    exit;
}

// Get current user's purchase forms
$user_id = $_SESSION['user_id'];
$query = "SELECT p.*, c.name as company_name_display 
          FROM purchases p 
          LEFT JOIN companies c ON p.company_name = c.name 
          WHERE p.created_by = ? 
          ORDER BY p.submitted_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Purchase Forms | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-complete {
            background-color: #d1edff;
            color: #084298;
        }
        .table-responsive {
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .btn-sm {
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-info mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-text text-info"></i> My Purchase Forms</h2>
            <a href="../purchase/form.php" class="btn btn-info text-white">
                <i class="bi bi-plus-circle"></i> Submit New Form
            </a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> 
                        Submitted Forms (<?= $result->num_rows ?> total)
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
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
                            <?php while($form = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#<?= $form['id'] ?></span>
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
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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
    </script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>
