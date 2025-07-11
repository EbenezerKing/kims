<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
session_start();

// Initialize error variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    try {
        // Check if fields are empty
        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields";
        } else {
            // Debug connection
            if (!$conn) {
                $error = "Database connection failed: " . mysqli_connect_error();
            } else {
                $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
                if (!$stmt) {
                    $error = "Prepare failed: " . $conn->error;
                } else {
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        if (password_verify($password, $user['password'])) {
                            if ($user['role'] === 'user') {
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['username'] = $username;
                                $_SESSION['role'] = $user['role'];
                                
                                // Redirect to dashboard
                                header("Location: ../user/dashboard.php");
                                exit;
                            } else {
                                $error = "Invalid user role. Please use the correct login page.";
                            }
                        } else {
                            $error = "Invalid password";
                        }
                    } else {
                        $error = "User not found";
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login | KIMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow" style="width: 100%; max-width: 400px;">
            <div class="card-header bg-info text-white text-center">
                <h4 class="mb-0">User Login</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center py-2">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                required autofocus>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info w-100 text-white">Login</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="login.php" class="text-decoration-none">Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>