<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['game_username'])) {
    echo json_encode(['error' => 'No user logged in']);
    exit;
}

$username = $_SESSION['game_username'];

// Fetch personal high score
$sql = "SELECT high_score FROM game_scores WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$personal_high_score = $result->num_rows > 0 ? $result->fetch_assoc()['high_score'] : 0;

// Fetch leaderboard (top 10 high scores)
$sql = "SELECT username, high_score FROM game_scores ORDER BY high_score DESC LIMIT 10";
$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}
$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}

echo json_encode([
    'personal_high_score' => $personal_high_score,
    'leaderboard' => $leaderboard
]);

$stmt->close();
$conn->close();
?>