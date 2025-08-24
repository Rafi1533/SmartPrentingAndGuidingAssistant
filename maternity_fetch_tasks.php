<?php
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Validate parameters
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

if ($user_id <= 0 || empty($date)) {
    error_log("Invalid parameters: user_id=$user_id, date=$date");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user_id or date']);
    exit;
}

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    error_log("Invalid date format: date=$date");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

// Fetch tasks
$sql = "SELECT id, task_title, task_details, task_time, status FROM maternity_tasks WHERE user_id=$user_id AND task_date='$date'";
$result = $conn->query($sql);

if ($result === false) {
    error_log("Query failed: " . $conn->error . " | Query: $sql");
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = [
        'id' => (int)$row['id'],
        'task_title' => htmlspecialchars($row['task_title']),
        'task_details' => htmlspecialchars($row['task_details']),
        'task_time' => $row['task_time'],
        'status' => $row['status']
    ];
}

header('Content-Type: application/json');
echo json_encode($tasks);
$conn->close();
?>