<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

header('Content-Type: application/json');

try {
    $search = isset($_GET['term']) ? $_GET['term'] : '';
    
    $query = "SELECT DISTINCT company_name 
              FROM purchases 
              WHERE company_name LIKE ? 
              ORDER BY company_name 
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $companies = [];
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row['company_name'];
    }
    
    echo json_encode($companies);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();