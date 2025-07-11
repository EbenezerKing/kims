<?php
require_once '../includes/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">KIMS Dashboard</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h2 class="mb-4">Welcome to Dashboard</h2>
                        <div class="d-grid gap-3 col-6 mx-auto">
                            <a href="../purchase/form.php" class="btn btn-success btn-lg">Submit Purchase Form</a>
                            <a href="reports.php" class="btn btn-info btn-lg">View Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>