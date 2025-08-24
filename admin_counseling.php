<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle add session
if (isset($_POST['add_session'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $details = $conn->real_escape_string($_POST['details']);
    $sql = "INSERT INTO sessions (title, details) VALUES ('$title', '$details')";
    $conn->query($sql);
    $session_id = $conn->insert_id;

    // Add doctors
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_POST["doctor_name_$i"]) && !empty($_POST["doctor_name_$i"])) {
            $doctor_name = $conn->real_escape_string($_POST["doctor_name_$i"]);
            $time_slot = $conn->real_escape_string($_POST["time_slot_$i"]);
            $sql = "INSERT INTO session_doctors (session_id, doctor_name, time_slot) VALUES ($session_id, '$doctor_name', '$time_slot')";
            $conn->query($sql);
        }
    }
}

// Handle update appointment status and link
if (isset($_POST['update_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $link = $conn->real_escape_string($_POST['link']);
    $sql = "UPDATE appointments SET status='$status', link='$link' WHERE id=$appointment_id";
    $conn->query($sql);
}

// Handle one-to-one payment confirmation
if (isset($_POST['confirm_payment'])) {
    $payment_id = (int)$_POST['payment_id'];
    $sql = "UPDATE payments SET status='received' WHERE id=$payment_id";
    $conn->query($sql);
}

// Handle one-to-one payment rejection
if (isset($_POST['reject_payment'])) {
    $payment_id = (int)$_POST['payment_id'];
    $sql = "UPDATE payments SET status='rejected' WHERE id=$payment_id";
    $conn->query($sql);
}

// Handle personal request assignment
if (isset($_POST['assign_personal'])) {
    $request_id = (int)$_POST['request_id'];
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $bkash_number = $conn->real_escape_string($_POST['bkash_number']);
    $amount = (float)$_POST['amount'];
    $link = $conn->real_escape_string($_POST['link']);
    $sql = "UPDATE personal_requests SET status='assigned', doctor_name='$doctor_name', start_time='$start_time', end_time='$end_time', bkash_number='$bkash_number', amount=$amount, link='$link' WHERE id=$request_id";
    $conn->query($sql);
}

// Handle personal payment confirmation
if (isset($_POST['confirm_personal_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $sql = "UPDATE personal_requests SET status='paid' WHERE id=$request_id";
    $conn->query($sql);
}

// Handle personal payment rejection
if (isset($_POST['reject_personal_payment'])) {
    $request_id = (int)$_POST['request_id'];
    $sql = "UPDATE personal_requests SET status='rejected', transaction_number='', screenshot='' WHERE id=$request_id";
    $conn->query($sql);
}

// Handle payment methods update
if (isset($_POST['update_payment_methods'])) {
    $bkash = $conn->real_escape_string($_POST['bkash']);
    $rocket = $conn->real_escape_string($_POST['rocket']);
    $nagad = $conn->real_escape_string($_POST['nagad']);
    $sql = "UPDATE payment_methods SET bkash='$bkash', rocket='$rocket', nagad='$nagad' WHERE id=1";
    if ($conn->query($sql) === FALSE) {
        $sql = "INSERT INTO payment_methods (bkash, rocket, nagad) VALUES ('$bkash', '$rocket', '$nagad')";
        $conn->query($sql);
    }
}

$payment_methods = $conn->query("SELECT * FROM payment_methods LIMIT 1")->fetch_assoc() ?? ['bkash' => '', 'rocket' => '', 'nagad' => ''];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Counseling Management</title>
<style>
/* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* Body */
body {
    font-family: 'Inter', sans-serif;
    background: #fff;
    color: #333;
    line-height: 1.6;
}

/* Navbar */
.navbar {
    background: #1b0049ff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.navbar ul {
    list-style: none;
    display: flex;
}
.navbar ul li {
    margin-left: 20px;
}
.navbar ul li a {
    color: #fff;
    text-decoration: none;
    transition: 0.3s;
}
.navbar ul li a:hover {
    color: #cce7ff;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 15px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Section Cards */
.card {
    background: #f0f8ff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

/* Headings */
.card h3, .section-title {
    margin-bottom: 15px;
    color: #007bff;
}

/* Form Inputs */
input, textarea, select, button {
    font-family:'Inter', sans-serif;
}
input, textarea, select {
    width: 100%;
    padding: 8px 12px;
    margin: 5px 0 15px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
input:focus, textarea:focus, select:focus {
    border-color:#007bff;
    outline:none;
    box-shadow:0 0 5px rgba(0,123,255,0.3);
}

/* Buttons */
button {
    padding: 10px 16px;
    background:#007bff;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    transition: 0.3s;
}
button:hover {
    background:#0056b3;
    transform: translateY(-2px);
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    background: #e6f0ff;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    text-align: left;
    font-size: 14px;
}
th {
    background: #007bff;
    color: #fff;
}
tr:nth-child(even){background: #f0f8ff;}
tr:hover{background:#d0e7ff;}

/* Action Buttons */
.action-btn { background:#28a745; color:#fff; padding:6px 10px; margin-right:5px; border-radius:5px; font-size:13px; transition:0.3s; }
.action-btn:hover { background:#218838; transform: translateY(-2px);}
.reject-btn { background:#dc3545;}
.reject-btn:hover { background:#c82333; transform: translateY(-2px);}

/* Section Titles */
.section-title {
    font-size:1.3em;
    color:#0056b3;
    margin-bottom:10px;
}

/* Personal Counseling Full Width */
.personal-card {
    width: 100%;
}

/* Responsive */
@media(max-width:768px){
    .navbar { flex-direction: column; align-items: flex-start; }
    .navbar ul { flex-direction: column; width: 100%; }
    .navbar ul li { margin: 10px 0; }
    input, textarea, select { font-size:13px; }
    button { font-size:14px; }
}
</style>
</head>
<body>

<div class="navbar">
    <span>Admin Counseling Management</span>
    <ul>
        <li><a href="admin_dashboard.php">Home</a></li>
        <li><a href="admin_counseling.php">Counseling</a></li>
        <li><a href="admin_group_counseling.php">Group Counseling</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">

    <!-- Add Session -->
    <div class="card">
        <h3>Add New Session</h3>
        <form action="" method="POST">
            <input type="text" name="title" placeholder="Session Title" required>
            <textarea name="details" placeholder="Session Details" required></textarea>
            <h3>Add Doctors (3-5)</h3>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="text" name="doctor_name_<?php echo $i; ?>" placeholder="Doctor Name <?php echo $i; ?>">
                <input type="text" name="time_slot_<?php echo $i; ?>" placeholder="Time Slot <?php echo $i; ?>">
            <?php endfor; ?>
            <button type="submit" name="add_session">Add Session</button>
        </form>
    </div>

    <!-- Booked Appointments -->
    <div class="card">
        <div class="section-title">Booked Appointments</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Session Title</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT a.*, s.title, d.doctor_name FROM appointments a JOIN sessions s ON a.session_id = s.id JOIN session_doctors d ON a.doctor_id = d.id";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $actions = "<form method='POST'>
                    <input type='hidden' name='appointment_id' value='{$row['id']}'>
                    <select name='status'>
                        <option value='pending'" . ($row['status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                        <option value='confirmed'" . ($row['status'] == 'confirmed' ? ' selected' : '') . ">Confirmed</option>
                        <option value='completed'" . ($row['status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                        <option value='canceled'" . ($row['status'] == 'canceled' ? ' selected' : '') . ">Canceled</option>
                        <option value='ended'" . ($row['status'] == 'ended' ? ' selected' : '') . ">Ended</option>
                    </select>
                    <input type='text' name='link' placeholder='Zoom/Link' value='{$row['link']}'>
                    <button type='submit' name='update_appointment' class='action-btn'>Update</button>
                </form>";
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                    <td>" . htmlspecialchars($row['title']) . "</td>
                    <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                    <td>{$row['appointment_date']}</td>
                    <td>{$row['appointment_time']}</td>
                    <td>" . ucfirst($row['status']) . "</td>
                    <td>" . ($row['link'] ? "<a href='{$row['link']}' target='_blank'>Link</a>" : 'N/A') . "</td>
                    <td>$actions</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Payment Methods -->
    <div class="card">
        <h3>Update Payment Methods</h3>
        <form action="" method="POST">
            <input type="text" name="bkash" placeholder="Bkash Number" value="<?php echo htmlspecialchars($payment_methods['bkash']); ?>" required>
            <input type="text" name="rocket" placeholder="Rocket Number" value="<?php echo htmlspecialchars($payment_methods['rocket']); ?>" required>
            <input type="text" name="nagad" placeholder="Nagad Number" value="<?php echo htmlspecialchars($payment_methods['nagad']); ?>" required>
            <button type="submit" name="update_payment_methods">Update</button>
        </form>
    </div>

    <!-- One-to-One Payments -->
    <div class="card">
        <div class="section-title">One-to-One Payments</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Transaction</th>
                    <th>Screenshot</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM payments";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $actions = ($row['status'] == 'pending') ? "<form method='POST' style='display:inline;'>
                    <input type='hidden' name='payment_id' value='{$row['id']}'>
                    <button type='submit' name='confirm_payment' class='action-btn'>Received</button>
                    <button type='submit' name='reject_payment' class='action-btn reject-btn'>Reject</button>
                </form>" : ucfirst($row['status']);
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                    <td>" . htmlspecialchars($row['transaction_number']) . "</td>
                    <td>" . ($row['screenshot'] ? "<a href='{$row['screenshot']}' target='_blank'><img src='{$row['screenshot']}' width='50'></a>" : 'N/A') . "</td>
                    <td>" . ucfirst($row['status']) . "</td>
                    <td>$actions</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Personal Counseling Requests -->
    <div class="card personal-card">
        <div class="section-title">Personal Counseling Requests</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Doctor</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Bkash</th>
                    <th>Amount</th>
                    <th>Link</th>
                    <th>Transaction</th>
                    <th>Screenshot</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM personal_requests";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $actions = '';
                if ($row['status'] == 'pending') {
                    $actions = "<form method='POST'>
                        <input type='hidden' name='request_id' value='{$row['id']}'>
                        <input type='text' name='doctor_name' placeholder='Doctor'>
                        <input type='datetime-local' name='start_time'>
                        <input type='datetime-local' name='end_time'>
                        <input type='text' name='bkash_number' placeholder='Bkash'>
                        <input type='number' name='amount' placeholder='Amount'>
                        <input type='text' name='link' placeholder='Link'>
                        <button type='submit' name='assign_personal' class='action-btn'>Assign</button>
                    </form>";
                } elseif ($row['status'] == 'assigned' && $row['transaction_number']) {
                    $actions = "<form method='POST' style='display:inline;'>
                        <input type='hidden' name='request_id' value='{$row['id']}'>
                        <button type='submit' name='confirm_personal_payment' class='action-btn'>Received</button>
                        <button type='submit' name='reject_personal_payment' class='action-btn reject-btn'>Reject</button>
                    </form>";
                } else {
                    $actions = ucfirst($row['status']);
                }
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['user_id']}</td>
                    <td>" . htmlspecialchars($row['title']) . "</td>
                    <td>" . htmlspecialchars($row['details']) . "</td>
                    <td>" . ucfirst($row['status']) . "</td>
                    <td>" . ($row['doctor_name'] ? htmlspecialchars($row['doctor_name']) : 'N/A') . "</td>
                    <td>" . ($row['start_time'] ?: 'N/A') . "</td>
                    <td>" . ($row['end_time'] ?: 'N/A') . "</td>
                    <td>" . ($row['bkash_number'] ?: 'N/A') . "</td>
                    <td>" . ($row['amount'] ?: 'N/A') . "</td>
                    <td>" . ($row['link'] ? "<a href='{$row['link']}' target='_blank'>Link</a>" : 'N/A') . "</td>
                    <td>" . ($row['transaction_number'] ?: 'N/A') . "</td>
                    <td>" . ($row['screenshot'] ? "<a href='{$row['screenshot']}' target='_blank'><img src='{$row['screenshot']}' width='50'></a>" : 'N/A') . "</td>
                    <td>$actions</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
