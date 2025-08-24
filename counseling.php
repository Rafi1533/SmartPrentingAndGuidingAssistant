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

    $sql_count = "SELECT COUNT(*) as count FROM appointments WHERE user_id=$user_id AND status IN ('pending', 'confirmed', 'completed')";
    $session_count = $conn->query($sql_count)->fetch_assoc()['count'];

    if ($session_count >= 2) {
        $sql_payment = "SELECT status FROM payments WHERE user_id=$user_id AND status='received' ORDER BY id DESC LIMIT 1";
        $payment_result = $conn->query($sql_payment);
        if ($payment_result->num_rows == 0) {
            echo "Payment required for additional sessions.";
            exit;
        }
    }

    $sql = "INSERT INTO appointments (user_id, session_id, doctor_id, appointment_date, appointment_time) VALUES ($user_id, $session_id, $doctor_id, '$appointment_date', '$appointment_time')";
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
        $sql = "INSERT INTO payments (user_id, transaction_number, screenshot) VALUES ($user_id, '$transaction_number', '$screenshot')";
        $conn->query($sql);
    }
}

// Submit personal request
if (isset($_POST['submit_personal_request'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $details = $conn->real_escape_string($_POST['details']);
    $sql = "INSERT INTO personal_requests (user_id, title, details) VALUES ($user_id, '$title', '$details')";
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
        $sql = "UPDATE personal_requests SET transaction_number='$transaction_number', screenshot='$screenshot' WHERE id=$request_id AND user_id=$user_id";
        $conn->query($sql);
    }
}

// Cancel appointment
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $sql = "UPDATE appointments SET status='canceled' WHERE id=$appointment_id AND user_id=$user_id AND status='pending'";
    $conn->query($sql);
}

$payment_methods = $conn->query("SELECT * FROM payment_methods LIMIT 1")->fetch_assoc() ?? ['bkash' => '', 'rocket' => '', 'nagad' => ''];

$sql_count = "SELECT COUNT(*) as count FROM appointments WHERE user_id=$user_id AND status IN ('pending', 'confirmed', 'completed')";
$session_count = $conn->query($sql_count)->fetch_assoc()['count'];

$sql_payment = "SELECT status FROM payments WHERE user_id=$user_id ORDER BY id DESC LIMIT 1";
$payment_result = $conn->query($sql_payment);
$payment_status = $payment_result->num_rows > 0 ? $payment_result->fetch_assoc()['status'] : 'none';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counseling</title>
    <style>
      /* Base Styles */
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(145deg, #e0f7fa, #ffe0b2);
    margin: 0;
    padding: 0;
    color: #333;
    line-height: 1.6;
}

/* Navbar */
.navbar {
    background: linear-gradient(135deg, #007bff, #00bcd4);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.navbar span {
    font-size: 1.4em;
    font-weight: 700;
    color: #fff;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
}

.navbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}

.navbar ul li {
    margin: 0 15px;
}

.navbar ul li a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.navbar ul li a:hover {
    color: #00ffc3;
    background: rgba(255,255,255,0.2);
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

/* Container */
.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 30px 35px;
    background: rgba(255, 255, 255, 0.15); /* semi-transparent */
    backdrop-filter: blur(12px); /* frosted effect */
    -webkit-backdrop-filter: blur(12px);
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    transition: all 0.3s ease;
    color: #000000ff; /* text color for readability */
}

/* Headings */
h2, h3 {
    font-weight: 700;
    margin-bottom: 15px;
    color: #000000ff; /* bright text for contrast */
    text-shadow: 1px 1px 4px rgba(0,0,0,0.7); /* shadow for readability */
}

/* Sections / Form Sections */
.form-section {
    margin: 25px 0;
    padding: 20px 25px;
    background: rgba(255, 255, 255, 0.12); /* semi-transparent */
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    color: #000000ff; /* text color for readability */
}

.form-section:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

/* Inputs & Textareas */
input, textarea, select {
    width: 100%;
    padding: 12px 14px;
    margin: 6px 0;
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 8px;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.2);
    color: #000000ff;
    transition: all 0.3s ease;
}

input::placeholder, textarea::placeholder {
    color: rgba(0, 0, 0, 0.7); /* readable placeholder */
}

input:focus, textarea:focus, select:focus {
    border-color: #00bcd4;
    box-shadow: 0 0 12px rgba(0,188,212,0.4);
    outline: none;
}

/* Buttons */
button {
    background: linear-gradient(135deg, #00bcd4, #007bff);
    color: #fff;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(135deg, #007bff, #00bcd4);
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
}

/* Special Buttons */
.join-btn, .action-btn {
    background: linear-gradient(135deg, #4caf50, #81c784);
    padding: 8px 14px;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
    border-radius: 8px;
    transition: all 0.3s ease;
    color: #fff;
}

.join-btn:hover, .action-btn:hover {
    background: linear-gradient(135deg, #388e3c, #66bb6a);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.25);
}


/* Collapsible Sections */
.section-title {
    margin-top: 20px;
    font-size: 1.15em;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: #212121; /* deep dark color */
    color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.section-title:hover {
    background: #424242;
    transform: translateY(-1px);
}

.section-title::after {
    content: '▼';
    font-size: 0.85em;
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
    margin-top: 12px;
    background: #f1f8e9;
    padding: 15px;
    border-radius: 8px;
}

.section-content.collapsed {
    max-height: 0;
    opacity: 0;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.6); /* Semi-transparent white */
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    border: 2px solid rgba(173, 216, 230, 0.8); /* Light blue outer border */
}

/* Table Head */
th {
    background: rgba(0, 123, 255, 0.8); /* Blue with transparency */
    color: #000; /* Black text for readability */
    font-weight: 600;
    text-align: center;
    padding: 14px;
    border: 1px solid rgba(173, 216, 230, 0.8); /* Light blue cell borders */
}

/* Table Cells */
td {
    padding: 14px;
    text-align: left;
    color: #000; /* Black text */
    border: 1px solid rgba(173, 216, 230, 0.8); /* Light blue cell borders */
}

/* Row Striping */
tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.4); /* Subtle alternate row */
}

/* Hover Effect */
tr:hover {
    background: rgba(0, 188, 212, 0.2); /* Soft aqua hover */
    transition: background 0.2s ease;
}

/* Payment Message */
.payment-message {
    color: #d32f2f;
    font-weight: 600;
    margin: 10px 0;
}

/* Hidden Payment Form */
.payment-form {
    display: none;
}
/* Video Background */
.video-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1; /* Behind all content */
}

.video-background video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    transform: translate(-50%, -50%);
    object-fit: cover;
    filter: brightness(1); /* Darkens video for readability */
}

/* Ensure content is on top */
.container, .navbar {
    position: relative;
    z-index: 1;
}


/* Responsive */
@media (max-width: 768px) {
    .navbar ul {
        flex-direction: column;
        align-items: flex-start;
    }
    .navbar ul li {
        margin: 8px 0;
    }
    .container {
        padding: 20px;
        margin: 20px 15px;
    }
}
</style>
    </script>
</head>
<body>
    <div class="video-background">
    <video autoplay muted loop playsinline>
        <source src="normalcoun.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>


    <div class="navbar">
        <span>Counseling</span>
        <ul>
            <li><a href="parent_dashboard.php">Home</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>One-to-One Sessions</h2>

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
                    $sql = "SELECT * FROM sessions";
                    $result = $conn->query($sql);
                    $now = date('Y-m-d');
                    while ($row = $result->fetch_assoc()) {
                        $session_id = $row['id'];
                        $sql_doctors = "SELECT * FROM session_doctors WHERE session_id=$session_id";
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
                    $sql = "SELECT a.*, s.title, d.doctor_name FROM appointments a JOIN sessions s ON a.session_id = s.id JOIN session_doctors d ON a.doctor_id = d.id WHERE a.user_id=$user_id";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $now = new DateTime();
                        $appointment_datetime = new DateTime($row['appointment_date'] . ' ' . $row['appointment_time']);
                        $status = $row['status'];
                        if ($now > $appointment_datetime && $status == 'confirmed') {
                            $status = 'ended';
                            $sql_update = "UPDATE appointments SET status='ended' WHERE id={$row['id']}";
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
        <div class="section-title">Request for Personal Counseling</div>
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
        <div class="section-title">Your Personal Counseling Requests</div>
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
                    $sql = "SELECT * FROM personal_requests WHERE user_id=$user_id";
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
    <script>
// Collapse/Expand sections
document.querySelectorAll('.section-title').forEach(title=>{
    title.addEventListener('click', ()=>{
        title.classList.toggle('collapsed');
        title.nextElementSibling.classList.toggle('collapsed');
    });
});
</script>

</body>
</html>