<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id']) && isset($_POST['section']) && isset($_POST['red_flags']) && isset($_POST['risk_level'])) {
    $user_id = $_SESSION['user_id'];
    $section = $_POST['section'];
    $red_flags = $_POST['red_flags'];
    $risk_level = $_POST['risk_level'];

    $sql = "INSERT INTO autism_results (user_id, section, red_flags, risk_level) VALUES ($user_id, '$section', $red_flags, '$risk_level')";
    $conn->query($sql);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>