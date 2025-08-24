<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_POST['username']) || empty(trim($_POST['username']))) {
    echo json_encode(['error' => 'No username provided']);
    exit;
}

$username = $conn->real_escape_string(trim($_POST['username']));

// Check if username already exists
$sql = "SELECT id FROM game_scores WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['error' => 'Username already exists']);
} else {
    $sql = "INSERT INTO game_scores (username, high_score) VALUES (?, 0)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $username);
    $success = $stmt->execute();
    
    if ($success) {
        $_SESSION['game_username'] = $username;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Insert failed: ' . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>