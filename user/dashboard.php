<?php
require_once '../includes/auth.php';
require_login();

// Ensure user has appropriate role
if ($_SESSION['role'] !== 'user') {
    header("Location: ../auth/user_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-info mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-person-circle"></i> User Dashboard</a>
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
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-text display-4 text-info mb-3"></i>
                        <h5 class="card-title">View Purchase Forms</h5>
                        <p class="card-text">Access and review your submitted purchase forms</p>
                        <a href="view_forms.php" class="btn btn-info text-white">
                            <i class="bi bi-eye"></i> View Forms
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-plus display-4 text-info mb-3"></i>
                        <h5 class="card-title">Submit New Form</h5>
                        <p class="card-text">Create and submit a new purchase form</p>
                        <a href="../purchase/form.php" class="btn btn-info text-white">
                            <i class="bi bi-plus-circle"></i> New Form
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>