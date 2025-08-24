<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle counseling request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $details = $conn->real_escape_string($_POST['details']);
    $sql = "INSERT INTO parentcoun_counseling_requests (user_id, title, details) VALUES ($user_id, '$title', '$details')";
    if ($conn->query($sql)) {
        $message = "Counseling request submitted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $transaction_number = $conn->real_escape_string($_POST['transaction_number']);
    
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
        $upload_dir = 'uploads/';
        $screenshot_name = time() . '_' . basename($_FILES['screenshot']['name']);
        $screenshot_path = $upload_dir . $screenshot_name;
        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $screenshot_path)) {
            $sql = "INSERT INTO parentcoun_payment_submissions (request_id, transaction_number, screenshot_path) VALUES ($request_id, '$transaction_number', '$screenshot_path')";
            if ($conn->query($sql)) {
                $sql = "UPDATE parentcoun_counseling_requests SET status = 'Payment Required' WHERE id = $request_id AND user_id = $user_id";
                $conn->query($sql);
                $message = "Payment details submitted successfully!";
            } else {
                $message = "Error: " . $conn->error;
            }
        } else {
            $message = "Error uploading screenshot.";
        }
    } else {
        $message = "Please upload a screenshot.";
    }
}

// Fetch user requests
$requests = $conn->query("SELECT r.*, rs.doctor_name, rs.doctor_details, rs.session_date, rs.start_time, rs.end_time, rs.bkash_number, rs.amount, rs.video_call_link, ps.transaction_number, ps.screenshot_path, ps.status as payment_status
    FROM parentcoun_counseling_requests r
    LEFT JOIN parentcoun_counseling_responses rs ON r.id = rs.request_id
    LEFT JOIN parentcoun_payment_submissions ps ON r.id = ps.request_id
    WHERE r.user_id = $user_id
    ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Counseling - Smart Parenting Assistant</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        :root {
            --blue: #1565c0;
            --blue-light: #1976d2;
            --blue-dark: #0d47a1;
            --white: #fff;
            --gray-light: #f5f7fa;
            --gray-dark: #374151;
            --shadow-light: rgba(21, 101, 192, 0.35);
            --error-red: #e94b4b;
            --success-green: #3cb371;
            --warning-yellow: #f1c40f;
        }

        body.dark {
            --blue: #90caf9;
            --blue-light: #bbdefb;
            --blue-dark: #64b5f6;
            --white: #e3f2fd;
            --gray-light: #121212;
            --gray-dark: #b0bec5;
            --shadow-light: rgba(144, 202, 249, 0.7);
            background-color: var(--gray-light);
            color: var(--blue-dark);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: var(--gray-light);
            color: var(--blue-dark);
            transition: background-color 0.4s ease, color 0.4s ease;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(90deg, #e74c3c, #8e44ad, #3498db);
            color: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .navbar ul li {
            margin: 0 15px;
        }

        .navbar ul li a {
            color: var(--white);
            text-decoration: none;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
        }

        .navbar ul li a:hover {
            background: var(--blue-dark);
            transform: scale(1.1);
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 10px var(--shadow-light);
            color: var(--blue-dark);
        }

        body.dark .container {
            background: var(--gray-dark);
            color: var(--white);
        }

        h2, h3 {
            color: var(--blue);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="text"], input[type="file"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        body.dark input[type="text"], body.dark input[type="file"], body.dark textarea {
            border-color: var(--blue-dark);
            background: var(--gray-dark);
            color: var(--white);
        }

        button[type="submit"], .action-btn {
            background-color: var(--blue);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        button[type="submit"]:hover, .action-btn:hover {
            background-color: var(--blue-dark);
            transform: translateY(-2px);
        }

        .error {
            background: var(--error-red);
            color: var(--white);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success {
            background: var(--success-green);
            color: var(--white);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }

        body.dark th, body.dark td {
            border-color: var(--blue-dark);
        }

        th {
            background-color: #f2f2f2;
            color: var(--blue-dark);
        }

        body.dark th {
            background-color: var(--blue-dark);
            color: var(--white);
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        body.dark tr:hover {
            background-color: var(--gray-dark);
        }

        .join-btn {
            background-color: var(--success-green);
        }

        .join-btn:hover {
            background-color: #2e8b57;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span>Parent Counseling</span>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Request a Counseling Session</h2>
        <?php if (isset($message)) { ?>
            <div class="<?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Session Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="details">Details:</label>
                <textarea id="details" name="details" required></textarea>
            </div>
            <button type="submit" name="submit_request">Submit Request</button>
        </form>

        <h3>Your Counseling Requests</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Details</th>
                <th>Status</th>
                <th>Response</th>
                <th>Action</th>
            </tr>
            <?php while ($request = $requests->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                    <td><?php echo htmlspecialchars($request['details']); ?></td>
                    <td><?php echo $request['status']; ?></td>
                    <td>
                        <?php if ($request['status'] == 'Responded' || $request['status'] == 'Payment Required' || $request['status'] == 'Accepted' || $request['status'] == 'Rejected') { ?>
                            <p><strong>Doctor:</strong> <?php echo htmlspecialchars($request['doctor_name']); ?></p>
                            <p><strong>Details:</strong> <?php echo htmlspecialchars($request['doctor_details']); ?></p>
                            <p><strong>Date:</strong> <?php echo $request['session_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $request['start_time'] . ' - ' . $request['end_time']; ?></p>
                            <p><strong>bKash Number:</strong> <?php echo htmlspecialchars($request['bkash_number']); ?></p>
                            <p><strong>Amount:</strong> <?php echo $request['amount']; ?> BDT</p>
                        <?php } ?>
                        <?php if ($request['status'] == 'Rejected') { ?>
                            <p class="error">Wrong transaction, try again.</p>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($request['status'] == 'Responded' || $request['status'] == 'Rejected') { ?>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <div class="form-group">
                                    <label for="transaction_number_<?php echo $request['id']; ?>">Transaction Number:</label>
                                    <input type="text" id="transaction_number_<?php echo $request['id']; ?>" name="transaction_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="screenshot_<?php echo $request['id']; ?>">Screenshot:</label>
                                    <input type="file" id="screenshot_<?php echo $request['id']; ?>" name="screenshot" accept="image/*" required>
                                </div>
                                <button type="submit" name="submit_payment">Submit Payment</button>
                            </form>
                        <?php } elseif ($request['status'] == 'Accepted') { ?>
                            <a href="<?php echo htmlspecialchars($request['video_call_link']); ?>" class="action-btn join-btn" target="_blank">Join Meeting</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>