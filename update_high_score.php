<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['game_username'])) {
    echo json_encode(['error' => 'No user logged in']);
    exit;
}

$username = $_SESSION['game_username'];
$high_score = isset($_POST['high_score']) ? (int)$_POST['high_score'] : 0;

$sql = "UPDATE game_scores SET high_score = GREATEST(high_score, ?) WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("is", $high_score, $username);
$success = $stmt->execute();

echo json_encode(['success' => $success, 'error' => $success ? null : $stmt->error]);

$stmt->close();
$conn->close();
?>