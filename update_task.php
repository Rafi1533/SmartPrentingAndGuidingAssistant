<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = (int)$_POST['task_id'];
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $status = $conn->real_escape_string($_POST['status']);
    $user_id = $_SESSION['user_id'];

    $sql = "UPDATE parent_child_care_tasks SET status='$status' WHERE id=$task_id AND user_id=$user_id";
    if ($conn->query($sql)) {
        // Notify admins
        $sql_admins = "SELECT id FROM users WHERE role='admin'";
        $admins = $conn->query($sql_admins);
        while ($admin = $admins->fetch_assoc()) {
            $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ({$admin['id']}, 'Task \"$task_name\" completed by User ID $user_id', 'completed')");
        }
    }
}
?>