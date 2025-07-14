<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: form.php');
    exit;
}

try {
    // Validate and sanitize input
    $companyname = sanitize($_POST['companyname']);
    $awarddate = sanitize($_POST['awarddate']);
    $awardno = sanitize($_POST['awardno']);
    $waybillno = sanitize($_POST['waybillno']);
    $invoiceno = sanitize($_POST['invoiceno']);
    $quantityordered = (int)$_POST['quantityordered'];
    $quantityreceived = (int)$_POST['quantityreceived'];
    $unitofcount = (int)$_POST['unitofcount'];
    $balance = sanitize($_POST['balance']);
    $batchno = sanitize($_POST['batchno']);
    $expirydate = sanitize($_POST['expirydate']);
    $price = (float)$_POST['price'];
    $amount = (float)$_POST['amount'];
    $srano = (int)$_POST['srano'];
    $sradate = sanitize($_POST['sradate']);
    $lpono = sanitize($_POST['lpono']);
    $status = sanitize($_POST['status']);
    $details = sanitize($_POST['details']);
    
    // Validate that the company exists in the database
    if (empty($companyname)) {
        throw new Exception('Company name is required.');
    }
    
    $company_check = "SELECT id FROM companies WHERE name = ?";
    $company_stmt = $conn->prepare($company_check);
    if (!$company_stmt) {
        throw new Exception("Failed to prepare company validation query: " . $conn->error);
    }
    
    $company_stmt->bind_param("s", $companyname);
    $company_stmt->execute();
    $company_result = $company_stmt->get_result();
    
    if ($company_result->num_rows === 0) {
        throw new Exception('Invalid company selected. Please choose a company from the available list.');
    }
    
    $company_stmt->close();
    
    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['attachment']['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and PDF files are allowed.');
        }
        
        if ($_FILES['attachment']['size'] > $max_size) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }
        
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['attachment']['name']);
        $attachment_path = $upload_dir . $filename;
        
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
            throw new Exception('Failed to upload file.');
        }
    }

    // Debug: Print values before insertion
    error_log("Values to be inserted: " . print_r([
        $companyname, $awarddate, $awardno, $waybillno, $invoiceno,
        $quantityordered, $quantityreceived, $unitofcount, $balance,
        $batchno, $expirydate, $price, $amount, $srano, $sradate,
        $lpono, $status, $details, $attachment_path, $_SESSION['user_id']
    ], true));

    // Insert into database with explicit column names
    $query = "INSERT INTO purchases (
        company_name, award_date, award_no, details, waybill_no, invoice_no,
        quantity_ordered, quantity_received, unit_of_count, balance,
        batch_no, expiry_date, price, amount, sra_no, sra_date,
        lpo_no, status, attachment_path, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Match the number of parameters exactly - 20 parameters total
    $stmt->bind_param(
        "ssssssiiisssddissssi",
        $companyname, $awarddate, $awardno, $details, $waybillno, $invoiceno,
        $quantityordered, $quantityreceived, $unitofcount, $balance,
        $batchno, $expirydate, $price, $amount, $srano, $sradate,
        $lpono, $status, $attachment_path, $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $_SESSION['success_message'] = 'Purchase record saved successfully.';
    header('Location: form.php');
    exit;

} catch (Exception $e) {
    error_log("Error in handle_form.php: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: form.php');
    exit;
}