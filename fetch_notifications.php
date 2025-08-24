<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, message, type, created_at FROM parent_child_care_notifications WHERE user_id=$user_id AND is_read=0 ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id'],
        'message' => htmlspecialchars($row['message']),
        'type' => $row['type'],
        'created_at' => $row['created_at']
    ];
}

header('Content-Type: application/json');
echo json_encode($notifications);
?>