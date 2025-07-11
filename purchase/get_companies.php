<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_login();

header('Content-Type: application/json');

try {
    if (isset($_GET['term'])) {
        $search = $conn->real_escape_string($_GET['term']);
        $query = "SELECT id, name FROM companies WHERE name LIKE ? LIMIT 10";
        
        $stmt = $conn->prepare($query);
        $searchTerm = "%{$search}%";
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $companies = array();
        while ($row = $result->fetch_assoc()) {
            $companies[] = array(
                'id' => $row['id'],
                'label' => $row['name'],
                'value' => $row['name']
            );
        }
        
        echo json_encode($companies);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}