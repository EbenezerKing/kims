<?php
session_start();

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login to access page
 * Redirects to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Please login to access this page.';
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Check if user has admin role
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin role to access page
 * Redirects to dashboard if not admin
 */
function require_admin() {
    if (!is_admin()) {
        $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
        header('Location: /user/dashboard.php');
        exit;
    }
}

/**
 * Logout user and redirect to login page
 */
function logout() {
    session_start();
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}
?>