<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle add group counseling
if (isset($_POST['add_group'])) {
    $details = $conn->real_escape_string($_POST['details']);
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $link = $conn->real_escape_string($_POST['link']);
    $sql = "INSERT INTO group_counselings (details, doctor_name, start_time, end_time, link) VALUES ('$details', '$doctor_name', '$start_time', '$end_time', '$link')";
    $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Group Counseling</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fad0c4, #fbc2eb);
            margin: 0;
            padding: 0;
            text-align: center;
            color: #333;
        }
        .navbar {
            background: linear-gradient(90deg, #ff6b6b, #6a11cb, #2575fc);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .navbar ul li a:hover {
            color: #f8c471;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        h2, h3 {
            color: #3498db;
            margin-bottom: 15px;
        }
        .form-section {
            margin: 20px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            border: 2px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #ff6b6b;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            width: 100%;
            transition: background 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #e74c3c;
            transform: scale(1.05);
        }
        .section-title {
            margin-top: 25px;
            color: #3498db;
            font-size: 1.4em;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <span>Admin Group Counseling</span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_counseling.php">Counseling</a></li>
            <li><a href="admin_group_counseling.php">Group Counseling</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Manage Group Counseling</h2>

        <!-- Add Group Counseling Form -->
        <div class="form-section">
            <h3>Add New Group Counseling</h3>
            <form action="" method="POST">
                <textarea name="details" placeholder="Details" required></textarea>
                <input type="text" name="doctor_name" placeholder="Doctor Name" required>
                <input type="datetime-local" name="start_time" required>
                <input type="datetime-local" name="end_time" required>
                <input type="text" name="link" placeholder="Video Call Link (Zoom/Google Meet)" required>
                <button type="submit" name="add_group">Add Group Counseling</button>
            </form>
        </div>

        <!-- Group Counseling Table -->
        <div class="section-title">Current Group Counseling Sessions</div>
        <table>
            <thead>
                <tr>
                    <th>Details</th>
                    <th>Doctor Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $now = date('Y-m-d H:i:s');
                $sql = "SELECT * FROM group_counselings WHERE end_time > '$now'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['details']) . "</td>
                            <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                            <td>{$row['start_time']}</td>
                            <td>{$row['end_time']}</td>
                            <td><a href='{$row['link']}' target='_blank'>" . htmlspecialchars($row['link']) . "</a></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No active group counseling sessions.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>