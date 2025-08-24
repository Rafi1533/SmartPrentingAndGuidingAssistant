<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = (int)$_POST['task_id'];
    $sql_task = "SELECT user_id, task_name FROM parent_child_care_tasks WHERE id=$task_id";
    $task = $conn->query($sql_task)->fetch_assoc();
    $user_id = $task['user_id'];
    $task_name = $conn->real_escape_string($task['task_name']);

    // Notify user
    $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ($user_id, 'Task \"$task_name\" is overdue', 'overdue')");

    // Notify admins
    $sql_admins = "SELECT id FROM users WHERE role='admin'";
    $admins = $conn->query($sql_admins);
    while ($admin = $admins->fetch_assoc()) {
        $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ({$admin['id']}, 'Task \"$task_name\" is overdue for User ID $user_id', 'overdue')");
    }
}
?>