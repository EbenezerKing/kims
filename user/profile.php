<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

$pageTitle = 'My Profile';
$showBackButton = true;

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($full_name) || empty($email)) {
            throw new Exception("Name and email are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Start transaction
        $conn->begin_transaction();

        // Update basic info
        $update_query = "UPDATE users SET 
                        full_name = ?,
                        email = ?,
                        phone = ?,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $full_name, $email, $phone, $_SESSION['user_id']);
        $stmt->execute();

        // Handle password change if requested
        if (!empty($current_password)) {
            // Verify current password
            $verify_query = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($verify_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!password_verify($current_password, $result['password'])) {
                throw new Exception("Current password is incorrect");
            }

            // Validate new password
            if (empty($new_password) || strlen($new_password) < 6) {
                throw new Exception("New password must be at least 6 characters");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match");
            }

            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($password_query);
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $stmt->execute();
        }

        $conn->commit();
        $success_message = "Profile updated successfully!";
        
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Profile Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-info text-white p-4">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white p-3 me-3">
                            <i class="bi bi-person-circle text-info display-6"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                            <p class="mb-0 opacity-75">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <!-- Basic Information -->
                        <h5 class="mb-4"><i class="bi bi-person me-2"></i>Basic Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" 
                                       value="<?= ucfirst(htmlspecialchars($user['role'])) ?>" readonly>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <h5 class="mb-4"><i class="bi bi-key me-2"></i>Change Password</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password">
                                <div class="form-text">Leave blank if you don't want to change password</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password">
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                            <a href="dashboard.php" class="btn btn-light">
                                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
$stmt->close();
$conn->close();
?>