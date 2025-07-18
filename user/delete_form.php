<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Validate form ID
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid form ID');
    }

    $form_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    // Check if form exists and get all related data
    $stmt = $conn->prepare("SELECT * FROM purchases WHERE id = ? AND created_by = ? FOR UPDATE");
    $stmt->bind_param("ii", $form_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Form not found or access denied');
    }

    $form = $result->fetch_assoc();
    $stmt->close();

    // Delete any uploaded files
    if (!empty($form['attachment_path'])) {
        $file_path = "../uploads/" . basename($form['attachment_path']);
        if (file_exists($file_path)) {
            if (!unlink($file_path)) {
                error_log("Failed to delete file: " . $file_path);
            }
            
            // Delete empty directories if any
            $dir = dirname($file_path);
            if (is_dir($dir) && count(scandir($dir)) == 2) { // . and .. only
                rmdir($dir);
            }
        }
    }

    // Delete all related records first (if you have any related tables)
    // Example: Delete form comments if you have any
    // $stmt = $conn->prepare("DELETE FROM form_comments WHERE form_id = ?");
    // $stmt->bind_param("i", $form_id);
    // $stmt->execute();
    // $stmt->close();

    // Delete the main form record
    $stmt = $conn->prepare("DELETE FROM purchases WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $form_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Form could not be deleted');
    }

    // Optional: Delete any audit logs or history
    // $stmt = $conn->prepare("DELETE FROM form_history WHERE form_id = ?");
    // $stmt->bind_param("i", $form_id);
    // $stmt->execute();
    // $stmt->close();

    // Commit transaction
    $conn->commit();
    
    // Clean up any temporary files or cache
    clearstatcache();
    
    echo json_encode([
        'success' => true,
        'message' => 'Form and all associated data deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if ($conn && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    
    error_log("Delete form error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}