<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = (int)$_POST['task_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $sql = "UPDATE parent_child_care_tasks SET status = '$status' WHERE id = $task_id";
    $conn->query($sql);
}
?>