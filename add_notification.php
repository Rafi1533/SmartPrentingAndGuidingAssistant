<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $admin_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : null;
    $message = $conn->real_escape_string($_POST['message']);
    $type = $conn->real_escape_string($_POST['type']);
    $sql = "INSERT INTO parent_child_care_notifications (user_id, admin_id, message, type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $user_id, $admin_id, $message, $type);
    $stmt->execute();
    $stmt->close();
}
?>