<?php
session_start();
include 'db.php';

// Escape helper
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id'])) { 
    header('Location: parent_login.php'); 
    exit; 
}

$user_id = (int)$_SESSION['user_id'];
if (!isset($_GET['session'])) { 
    die("Session id required"); 
}
$session_id = intval($_GET['session']);

// fetch session info
$stmt = $conn->prepare("
    SELECT s.*, r.user_id, at.name AS autism_name 
    FROM therapy_sessions s 
    JOIN therapy_requests r ON s.request_id = r.id 
    JOIN therapy_autism_types at ON s.autism_type_id = at.id 
    WHERE s.id = ?
");
$stmt->bind_param("i", $session_id);
$stmt->execute();
$res = $stmt->get_result();
$session = $res->fetch_assoc();
$stmt->close();

if (!$session) die("Session not found");
if ($session['user_id'] != $user_id) die("Not allowed");

$msg = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_payment'])) {
    $tx_id = trim($_POST['tx_id']);
    if (empty($tx_id)) {
        $msg = "Transaction ID required";
    } else {
        $screenshot_path = null;

        if (!empty($_FILES['screenshot']['name']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $msg = "Invalid file type";
            } else {
                $dir = 'uploads/session_receipts/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $new = $dir . uniqid('pay_') . '.' . $ext;
                if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $new)) {
                    $screenshot_path = $new;
                }
            }
        }

        if ($msg === '') {
            $amount = 200.00;
            $stmt = $conn->prepare("
                INSERT INTO therapy_payments (session_id, user_id, amount, tx_id, tx_screenshot_path) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iidss", $session_id, $user_id, $amount, $tx_id, $screenshot_path);
            if ($stmt->execute()) {
                $msg = "Payment info uploaded. Wait for admin verification.";
            } else {
                $msg = "DB error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Pay for Session</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root { --blue: #1565C0; }
body { font-family: Inter, Arial, sans-serif; background: #f5f8fb; margin: 0; }
.container { max-width: 700px; margin: 30px auto; padding: 16px; }
.card { background: #fff; padding: 16px; border-radius: 10px; box-shadow: 0 6px 18px rgba(16,24,40,.06); }
label { display: block; margin-top: 8px; }
input, button { width: 100%; padding: 10px; margin-top: 8px; border-radius: 8px; border: 1px solid #e6edf3; box-sizing: border-box; }
button { background: var(--blue); color: #fff; border: none; cursor: pointer; transition: background 0.3s; }
button:hover { background: #0d47a1; }
.small { font-size: 13px; color: #666; }
.msg { margin: 8px 0; padding: 8px; background: #e3f2fd; border-radius: 6px; }
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Pay 200 ৳ via bKash</h2>
    <p class="small">Send 200 ৳ to bKash number: <strong><?= esc($session['bkash_number']) ?></strong></p>
    <p class="small">Session: <?= esc($session['autism_name']) ?> — <?= esc($session['start_datetime']) ?></p>
    <?php if($msg): ?><div class="msg"><strong><?= esc($msg) ?></strong></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <label>Transaction ID</label>
      <input name="tx_id" required>
      <label>Upload Screenshot (jpg/png)</label>
      <input type="file" name="screenshot" accept=".jpg,.jpeg,.png">
      <button type="submit" name="upload_payment">Upload Payment</button>
    </form>
  </div>
</div>
</body>
</html>
