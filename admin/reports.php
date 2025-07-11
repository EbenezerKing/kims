<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_login();

$result = $conn->query("SELECT * FROM purchases ORDER BY submitted_at DESC");
echo "<h2>Purchase Reports</h2><table border='1'><tr>
<th>ID</th><th>Company</th><th>Award No</th><th>Status</th><th>Date</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
    <td>{$row['id']}</td>
    <td>{$row['company_name']}</td>
    <td>{$row['award_no']}</td>
    <td>{$row['status']}</td>
    <td>{$row['submitted_at']}</td></tr>";
}
echo "</table><a href='../admin/dashboard.php'>Back</a>";
?>