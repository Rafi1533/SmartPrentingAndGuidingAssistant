<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Book appointment
if (isset($_POST['book_appointment'])) {
    $session_id = (int)$_POST['session_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $appointment_time = $conn->real_escape_string($_POST['appointment_time']);
    $now = date('Y-m-d H:i:s');
    $appointment_datetime = "$appointment_date $appointment_time";

    if ($appointment_datetime < $now) {
        echo "Cannot book past sessions.";
        exit;
    }

    $sql_count = "SELECT COUNT(*) as count FROM adv_appointments WHERE user_id=$user_id AND status IN ('pending', 'confirmed', 'completed')";
    $session_count = $conn->query($sql_count)->fetch_assoc()['count'];

    if ($session_count >= 2) {
        $sql_payment = "SELECT status FROM adv_payments WHERE user_id=$user_id AND status='received' ORDER BY id DESC LIMIT 1";
        $payment_result = $conn->query($sql_payment);
        if ($payment_result->num_rows == 0) {
            echo "Payment required for additional sessions.";
            exit;
        }
    }

    $sql = "INSERT INTO adv_appointments (user_id, session_id, doctor_id, appointment_date, appointment_time) VALUES ($user_id, $session_id, $doctor_id, '$appointment_date', '$appointment_time')";
    $conn->query($sql);
}

// Submit one-to-one payment
if (isset($_POST['submit_payment'])) {
    $transaction_number = $conn->real_escape_string($_POST['transaction_number']);
    $target_dir = "Uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $screenshot = $target_dir . basename($_FILES["screenshot"]["name"]);
    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $screenshot)) {
        $sql = "INSERT INTO adv_payments (user_id, transaction_number, screenshot) VALUES ($user_id, '$transaction_number', '$screenshot')";
        $conn->query($sql);
    }
}

// Submit personal request
if (isset($_POST['submit_personal_request'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $details = $conn->real_escape_string($_POST['details']);
    $sql = "INSERT INTO adv_personal_requests (user_id, title, details) VALUES ($user_id, '$title', '$details')";
    $conn->query($sql);
}

// Submit personal payment
if (isset($_POST['submit_personal_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $transaction_number = $conn->real_escape_string($_POST['transaction_number']);
    $target_dir = "Uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $screenshot = $target_dir . basename($_FILES["screenshot"]["name"]);
    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $screenshot)) {
        $sql = "UPDATE adv_personal_requests SET transaction_number='$transaction_number', screenshot='$screenshot' WHERE id=$request_id AND user_id=$user_id";
        $conn->query($sql);
    }
}

// Cancel appointment
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $sql = "UPDATE adv_appointments SET status='canceled' WHERE id=$appointment_id AND user_id=$user_id AND status='pending'";
    $conn->query($sql);
}

$payment_methods = $conn->query("SELECT * FROM adv_payment_methods LIMIT 1")->fetch_assoc() ?? ['bkash' => '', 'rocket' => '', 'nagad' => ''];

$sql_count = "SELECT COUNT(*) as count FROM adv_appointments WHERE user_id=$user_id AND status IN ('pending', 'confirmed', 'completed')";
$session_count = $conn->query($sql_count)->fetch_assoc()['count'];

$sql_payment = "SELECT status FROM adv_payments WHERE user_id=$user_id ORDER BY id DESC LIMIT 1";
$payment_result = $conn->query($sql_payment);
$payment_status = $payment_result->num_rows > 0 ? $payment_result->fetch_assoc()['status'] : 'none';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Counseling</title>
    <style>
    /* === Base & Body === */
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #e6d6f6ff, #2575fc); /* blue to violet gradient */
    margin: 0;
    padding: 0;
    color: #000; /* text color black */
    min-height: 100vh;
}

/* === Navbar === */
.navbar {
    background: rgba(255, 255, 255, 0.8); /* light transparent navbar */
    backdrop-filter: blur(10px);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 15px 15px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.navbar span {
    font-size: 1.4em;
    font-weight: 600;
    color: #0b1f4f; /* dark blue */
}
.navbar ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
}
.navbar ul li {
    margin: 0 15px;
    position: relative;
}
.navbar ul li a {
    color: #0b1f4f; /* dark blue */
    text-decoration: none;
    font-weight: 500;
    padding: 5px 0;
    transition: color 0.3s ease, transform 0.2s ease;
}
.navbar ul li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    background: #4facfe; /* lighter blue underline */
    left: 0;
    bottom: -2px;
    transition: width 0.3s ease;
}
.navbar ul li a:hover {
    color: #4facfe;
}
.navbar ul li a:hover::after {
    width: 100%;
}

/* === Container & Cards === */
.container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
}
.form-section, .section-content, table {
    background: rgba(255, 255, 255, 0.85); /* slightly transparent white for divs */
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 20px;
    transition: transform 0.2s ease;
}
.form-section:hover, .section-content:hover {
    transform: translateY(-3px);
}

/* === Headings === */
h2, h3 {
    color: #0b1f4f; /* dark blue headings */
    font-weight: 600;
    margin-bottom: 15px;
}

/* === Inputs & Selects === */
input, textarea, select {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border-radius: 8px;
    border: 1px solid #4facfe;
    background: rgba(255,255,255,0.7);
    color: #000;
    font-size: 14px;
    transition: all 0.3s ease;
}
input:focus, textarea:focus, select:focus {
    border-color: #2575fc;
    outline: none;
    box-shadow: 0 0 5px rgba(39, 174, 254, 0.3);
}

/* === Buttons === */
button, .join-btn, .action-btn {
    background: linear-gradient(90deg, #2575fc, #6a11cb); /* blue to violet gradient */
    color: #fff;
    padding: 10px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}
button:hover, .join-btn:hover, .action-btn:hover {
    background: linear-gradient(90deg, #6a11cb, #2575fc); /* reverse gradient on hover */
}

/* === Section Titles & Collapse === */
.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    font-size: 1.15em;
    font-weight: 600;
    color: #0b1f4f;
    padding: 10px 15px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.7);
    transition: all 0.3s ease;
}
.section-title:hover {
    background: rgba(39, 174, 254, 0.2);
}
.section-title::after {
    content: '▼';
    font-size: 0.9em;
    transition: transform 0.3s ease;
}
.section-title.collapsed::after {
    transform: rotate(180deg);
}
.section-content {
    max-height: 1000px;
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 1;
}
.section-content.collapsed {
    max-height: 0;
    opacity: 0;
}

/* === Tables === */
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: rgba(255, 255, 255, 0.9); /* soft white */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid rgba(39, 174, 254,0.5); /* subtle blue border */
    color: #000;
}

th {
    background: #2575fc; /* solid blue header */
    color: #ffffff;
    font-weight: 600;
}

tr:nth-child(even) {
    background: rgba(39, 174, 254, 0.1); /* soft blue for alternate rows */
}

tr:hover {
    background: rgba(39, 174, 254, 0.2);
    transition: background 0.2s ease;
}

/* === Payment Message === */
.payment-message {
    color: #2575fc;
    font-weight: 600;
}

/* === Scrollbars Minimal === */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.4);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb {
    background: rgba(39,174,254,0.5);
    border-radius: 10px;
}

    </style>
    <script>
        function checkPersonalSession(end, link) {
            const now = new Date();
            const endTime = new Date(end);
            if (now > endTime) {
                return 'Ended';
            } else {
                return `<a href="${link}" class="join-btn" target="_blank">Join Meeting</a>`;
            }
        }
        function checkOneToOneSession(start, link) {
            const now = new Date();
            const startTime = new Date(start);
            const endTime = new Date(startTime.getTime() + 60*60*1000); // Assume 1-hour session
            if (now > endTime) {
                return 'Ended';
            } else {
                return `<a href="${link}" class="join-btn" target="_blank">Join Meeting</a>`;
            }
        }
        // Toggle collapse/expand
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.section-title').forEach(title => {
                title.addEventListener('click', () => {
                    const content = title.nextElementSibling;
                    title.classList.toggle('collapsed');
                    content.classList.toggle('collapsed');
                });
            });
        });
    </script>
</head>
<body>

    <div class="navbar">
        <span>Advanced Counseling</span>
        <ul>
            <li><a href="parent_dashboard.html">Home</a></li>
            <li><a href="specialchild.php">Special Child Home</a></li>
            <li><a href="adv_group_counseling.php">Advanced Group Counseling</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Advanced One-to-One Sessions</h2>

        <!-- Payment Form or Message for One-to-One -->
        <?php if ($session_count >= 2 && $payment_status != 'received'): ?>
            <div class="section-title">Payment Information</div>
            <div class="section-content">
                <div class="form-section">
                    <?php if ($payment_status == 'pending'): ?>
                        <p class="payment-message">Waiting for admin approval...</p>
                    <?php else: ?>
                        <div class="payment-form" style="display: block;">
                            <h3>Payment Required</h3>
                            <p>You need to pay 2000 Taka for additional sessions.</p>
                            <p>Bkash: <?php echo htmlspecialchars($payment_methods['bkash']); ?></p>
                            <p>Rocket: <?php echo htmlspecialchars($payment_methods['rocket']); ?></p>
                            <p>Nagad: <?php echo htmlspecialchars($payment_methods['nagad']); ?></p>
                            <?php if ($payment_status == 'rejected'): ?>
                                <p class="payment-message">Your payment was rejected. You need to pay again.</p>
                            <?php endif; ?>
                            <form action="" method="POST" enctype="multipart/form-data">
                                <input type="text" name="transaction_number" placeholder="Transaction Number" required>
                                <input type="file" name="screenshot" accept="image/*" required>
                                <button type="submit" name="submit_payment">Submit Payment</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sessions Table -->
        <?php if ($session_count < 2 || $payment_status == 'received'): ?>
        <div class="section-title">Available Sessions</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Doctors</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM adv_sessions";
                    $result = $conn->query($sql);
                    $now = date('Y-m-d');
                    while ($row = $result->fetch_assoc()) {
                        $session_id = $row['id'];
                        $sql_doctors = "SELECT * FROM adv_session_doctors WHERE session_id=$session_id";
                        $doctors_result = $conn->query($sql_doctors);
                        $doctors = '';
                        while ($doctor = $doctors_result->fetch_assoc()) {
                            $doctors .= htmlspecialchars($doctor['doctor_name']) . " (" . htmlspecialchars($doctor['time_slot']) . ")<br>";
                        }

                        $action = "<form method='POST'>
                            <input type='hidden' name='session_id' value='$session_id'>
                            <select name='doctor_id' required>
                                <option value=''>Select Doctor</option>";
                        $doctors_result = $conn->query($sql_doctors);
                        while ($doctor = $doctors_result->fetch_assoc()) {
                            $action .= "<option value='{$doctor['id']}'>" . htmlspecialchars($doctor['doctor_name']) . " (" . htmlspecialchars($doctor['time_slot']) . ")</option>";
                        }
                        $action .= "</select>
                            <input type='date' name='appointment_date' min='$now' required>
                            <input type='time' name='appointment_time' required>
                            <button type='submit' name='book_appointment'>Book</button>
                        </form>";

                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['details']) . "</td>
                            <td>$doctors</td>
                            <td>$action</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Booked Appointments -->
        <div class="section-title">Your Booked Appointments</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Session Title</th>
                        <th>Doctor Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT a.*, s.title, d.doctor_name FROM adv_appointments a JOIN adv_sessions s ON a.session_id = s.id JOIN adv_session_doctors d ON a.doctor_id = d.id WHERE a.user_id=$user_id";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $now = new DateTime();
                        $appointment_datetime = new DateTime($row['appointment_date'] . ' ' . $row['appointment_time']);
                        $status = $row['status'];
                        if ($now > $appointment_datetime && $status == 'confirmed') {
                            $status = 'ended';
                            $sql_update = "UPDATE adv_appointments SET status='ended' WHERE id={$row['id']}";
                            $conn->query($sql_update);
                        } elseif ($status == 'confirmed') {
                            $status = 'taken';
                        } elseif ($status == 'pending') {
                            $status = 'booked';
                        }
                        $link = ($row['link'] && $status == 'taken') ? "<script>document.write(checkOneToOneSession('{$row['appointment_date']} {$row['appointment_time']}', '{$row['link']}'));</script>" : ($status == 'ended' ? 'Ended' : 'Pending');
                        $actions = ($row['status'] == 'pending') ? "<form method='POST'><input type='hidden' name='appointment_id' value='{$row['id']}'><button type='submit' name='cancel_appointment' class='action-btn'>Cancel</button></form>" : 'No Actions';
                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                            <td>{$row['appointment_date']}</td>
                            <td>{$row['appointment_time']}</td>
                            <td>" . ucfirst($status) . "</td>
                            <td>$link</td>
                            <td>$actions</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Personal Counseling Request -->
        <div class="section-title">Request for Advanced Personal Counseling</div>
        <div class="section-content">
            <div class="form-section">
                <h3>Submit Request</h3>
                <form action="" method="POST">
                    <input type="text" name="title" placeholder="Title" required>
                    <textarea name="details" placeholder="Details" required></textarea>
                    <button type="submit" name="submit_personal_request">Submit Request</button>
                </form>
            </div>
        </div>

        <!-- Personal Requests Table -->
        <div class="section-title">Your Advanced Personal Counseling Requests</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Doctor Name</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Bkash Number</th>
                        <th>Amount (Tk)</th>
                        <th>Link</th>
                        <th>Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM adv_personal_requests WHERE user_id=$user_id";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $payment_form = '';
                        $payment_status = '';
                        if ($row['status'] == 'assigned' && !$row['transaction_number']) {
                            $payment_form = "<form method='POST' enctype='multipart/form-data'>
                                <input type='hidden' name='request_id' value='{$row['id']}'>
                                <p>Pay {$row['amount']} Tk to {$row['bkash_number']}</p>
                                <input type='text' name='transaction_number' placeholder='Transaction Number' required>
                                <input type='file' name='screenshot' accept='image/*' required>
                                <button type='submit' name='submit_personal_payment'>Submit Payment</button>
                            </form>";
                        } elseif ($row['status'] == 'assigned' && $row['transaction_number']) {
                            $payment_status = 'Waiting for admin approval...';
                        } elseif ($row['status'] == 'rejected') {
                            $payment_form = "<form method='POST' enctype='multipart/form-data'>
                                <input type='hidden' name='request_id' value='{$row['id']}'>
                                <p class='payment-message'>Your payment was rejected. You need to pay again.</p>
                                <p>Pay {$row['amount']} Tk to {$row['bkash_number']}</p>
                                <input type='text' name='transaction_number' placeholder='Transaction Number' required>
                                <input type='file' name='screenshot' accept='image/*' required>
                                <button type='submit' name='submit_personal_payment'>Submit Payment</button>
                            </form>";
                        }
                        $link = ($row['status'] == 'paid' && $row['link']) ? "<script>document.write(checkPersonalSession('{$row['end_time']}', '{$row['link']}'));</script>" : 'N/A';
                        echo "<tr>
                            <td>" . htmlspecialchars($row['title']) . "</td>
                            <td>" . htmlspecialchars($row['details']) . "</td>
                            <td>" . ucfirst($row['status']) . "</td>
                            <td>" . ($row['doctor_name'] ? htmlspecialchars($row['doctor_name']) : 'N/A') . "</td>
                            <td>" . ($row['start_time'] ?: 'N/A') . "</td>
                            <td>" . ($row['end_time'] ?: 'N/A') . "</td>
                            <td>" . ($row['bkash_number'] ? htmlspecialchars($row['bkash_number']) : 'N/A') . "</td>
                            <td>" . ($row['amount'] ?: 'N/A') . "</td>
                            <td>$link</td>
                            <td>$payment_status</td>
                        </tr>";
                        if ($payment_form) {
                            echo "<tr><td colspan='10'>$payment_form</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>