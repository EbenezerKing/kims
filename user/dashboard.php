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
    <style>
        .nav-link {
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .action-card {
            border: none;
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
        }
        .bg-gradient-info {
            background: linear-gradient(45deg, #0dcaf0, #0d6efd);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-info mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="bi bi-grid-fill fs-4"></i>
                <span class="fs-4">KIMS Dashboard</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                   
                    <li class="nav-item">
                        <a class="nav-link text-white d-flex align-items-center gap-2" href="#">
                            <i class="bi bi-bell-fill"></i>
                            <span class="d-lg-none">Notifications</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white d-flex align-items-center gap-2" href="#" 
                           data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
    <i class="bi bi-person me-2"></i>Profile
</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="../auth/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                <p class="text-muted">Here's what's happening with your purchase forms today.</p>
            </div>
        </div>

        <!-- Actions Section -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card action-card h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-info bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                                <i class="bi bi-file-earmark-plus display-6 text-info"></i>
                            </div>
                            <h4 class="card-title">Submit New Form</h4>
                            <p class="card-text text-muted">Create and submit a new purchase form request</p>
                        </div>
                        <a href="../purchase/form.php" class="btn btn-info text-white w-100 py-2">
                            <i class="bi bi-plus-circle me-2"></i>New Form
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card action-card h-100">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="bg-info bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                                <i class="bi bi-file-text display-6 text-info"></i>
                            </div>
                            <h4 class="card-title">View Purchase Report</h4>
                            <p class="card-text text-muted">Access and manage your submitted purchase Report</p>
                        </div>
                        <a href="view_forms.php" class="btn btn-info text-white w-100 py-2">
                            <i class="bi bi-eye me-2"></i>View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>