<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notification_id'])) {
    $notification_id = (int)$_POST['notification_id'];
    $sql = "UPDATE parent_child_care_notifications SET is_read = 1 WHERE id = $notification_id";
    $conn->query($sql);
}
?>