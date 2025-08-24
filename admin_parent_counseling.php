<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_response'])) {
    $request_id = (int)$_POST['request_id'];
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $doctor_details = $conn->real_escape_string($_POST['doctor_details']);
    $session_date = $conn->real_escape_string($_POST['session_date']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $bkash_number = $conn->real_escape_string($_POST['bkash_number']);
    $amount = (float)$_POST['amount'];
    $video_call_link = $conn->real_escape_string($_POST['video_call_link']);
    
    $sql = "INSERT INTO parentcoun_counseling_responses (request_id, doctor_name, doctor_details, session_date, start_time, end_time, bkash_number, amount, video_call_link) 
            VALUES ($request_id, '$doctor_name', '$doctor_details', '$session_date', '$start_time', '$end_time', '$bkash_number', $amount, '$video_call_link')";
    if ($conn->query($sql)) {
        $sql = "UPDATE parentcoun_counseling_requests SET status = 'Responded' WHERE id = $request_id";
        $conn->query($sql);
        $message = "Response submitted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle payment approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $sql = "UPDATE parentcoun_payment_submissions SET status = 'Accepted' WHERE request_id = $request_id";
    if ($conn->query($sql)) {
        $sql = "UPDATE parentcoun_counseling_requests SET status = 'Accepted' WHERE id = $request_id";
        $conn->query($sql);
        $message = "Payment approved successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reject_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $sql = "UPDATE parentcoun_payment_submissions SET status = 'Rejected' WHERE request_id = $request_id";
    if ($conn->query($sql)) {
        $sql = "UPDATE parentcoun_counseling_requests SET status = 'Rejected' WHERE id = $request_id";
        $conn->query($sql);
        $message = "Payment rejected.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch all requests
$requests = $conn->query("SELECT r.*, u.first_name, u.email, rs.doctor_name, rs.doctor_details, rs.session_date, rs.start_time, rs.end_time, rs.bkash_number, rs.amount, rs.video_call_link, ps.transaction_number, ps.screenshot_path, ps.status as payment_status
    FROM parentcoun_counseling_requests r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN parentcoun_counseling_responses rs ON r.id = rs.request_id
    LEFT JOIN parentcoun_payment_submissions ps ON r.id = ps.request_id
    ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Counseling Management - Smart Parenting Assistant</title>
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
            max-width: 1200px;
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

        input[type="text"], input[type="date"], input[type="time"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        body.dark input[type="text"], body.dark input[type="date"], body.dark input[type="time"], body.dark input[type="number"], body.dark textarea {
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

        .approve-btn {
            background-color: var(--success-green);
        }

        .approve-btn:hover {
            background-color: #2e8b57;
        }

        .reject-btn {
            background-color: var(--error-red);
        }

        .reject-btn:hover {
            background-color: #c0392b;
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
            border: 1px solid #0000004c;
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
    </style>
</head>
<body>
    <div class="navbar">
        <span>Counseling Management</span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Counseling Requests</h2>
        <?php if (isset($message)) { ?>
            <div class="<?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <table>
            <tr>
                <th>User</th>
                <th>Title</th>
                <th>Details</th>
                <th>Status</th>
                <th>Response</th>
                <th>Payment</th>
                <th>Action</th>
            </tr>
            <?php while ($request = $requests->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['first_name'] . ' (' . $request['email'] . ')'); ?></td>
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
                            <p><strong>Video Call Link:</strong> <a href="<?php echo htmlspecialchars($request['video_call_link']); ?>" target="_blank">Link</a></p>
                        <?php } else { ?>
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <div class="form-group">
                                    <label for="doctor_name_<?php echo $request['id']; ?>">Doctor Name:</label>
                                    <input type="text" id="doctor_name_<?php echo $request['id']; ?>" name="doctor_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctor_details_<?php echo $request['id']; ?>">Doctor Details:</label>
                                    <textarea id="doctor_details_<?php echo $request['id']; ?>" name="doctor_details" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="session_date_<?php echo $request['id']; ?>">Session Date:</label>
                                    <input type="date" id="session_date_<?php echo $request['id']; ?>" name="session_date" required>
                                </div>
                                <div class="form-group">
                                    <label for="start_time_<?php echo $request['id']; ?>">Start Time:</label>
                                    <input type="time" id="start_time_<?php echo $request['id']; ?>" name="start_time" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_time_<?php echo $request['id']; ?>">End Time:</label>
                                    <input type="time" id="end_time_<?php echo $request['id']; ?>" name="end_time" required>
                                </div>
                                <div class="form-group">
                                    <label for="bkash_number_<?php echo $request['id']; ?>">bKash Number:</label>
                                    <input type="text" id="bkash_number_<?php echo $request['id']; ?>" name="bkash_number" required>
                                </div>
                                <div class="form-group">
                                    <label for="amount_<?php echo $request['id']; ?>">Amount (BDT):</label>
                                    <input type="number" id="amount_<?php echo $request['id']; ?>" name="amount" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="video_call_link_<?php echo $request['id']; ?>">Video Call Link:</label>
                                    <input type="text" id="video_call_link_<?php echo $request['id']; ?>" name="video_call_link" required>
                                </div>
                                <button type="submit" name="submit_response">Submit Response</button>
                            </form>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($request['transaction_number']) { ?>
                            <p><strong>Transaction Number:</strong> <?php echo htmlspecialchars($request['transaction_number']); ?></p>
                            <p><strong>Screenshot:</strong> <a href="<?php echo htmlspecialchars($request['screenshot_path']); ?>" target="_blank">View</a></p>
                            <p><strong>Status:</strong> <?php echo $request['payment_status']; ?></p>
                        <?php } else { ?>
                            <p>No payment submitted.</p>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($request['transaction_number'] && $request['payment_status'] == 'Pending') { ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="approve_payment" class="action-btn approve-btn">Received</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <button type="submit" name="reject_payment" class="action-btn reject-btn">Reject</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>