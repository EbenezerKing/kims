<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
} else {
    header("Location: auth/user_login.php");
}
exit;
?>