<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Counseling</title>
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
        h2 {
            color: #3498db;
            margin-bottom: 15px;
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
        .join-btn {
            background: #3498db;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .join-btn:hover {
            background: #2980b9;
        }
        .section-title {
            margin-top: 25px;
            color: #3498db;
            font-size: 1.4em;
        }
    </style>
    <script>
        function checkTime(start, end, link) {
            const now = new Date();
            const startTime = new Date(start);
            const endTime = new Date(end);
            if (now >= startTime && now <= endTime) {
                return `<a href="${link}" class="join-btn" target="_blank">Join</a>`;
            } else if (now > endTime) {
                return 'Over';
            } else {
                return 'Upcoming';
            }
        }
    </script>
</head>
<body>

    <div class="navbar">
        <span>Group Counseling</span>
        <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="specialchild.html">Special Child Home</a></li>
            <li><a href="childcare.html">Child Care</a></li>
            <li><a href="autism_quiz.php">Autism Quiz</a></li>
            <li><a href="results.php">Quiz Results</a></li>
            <li><a href="counseling.php">Counseling</a></li>
            <li><a href="group_counseling.php">Group Counseling</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Group Counseling Sessions</h2>
        <div class="section-title">Upcoming Group Counseling</div>
        <table>
            <thead>
                <tr>
                    <th>Details</th>
                    <th>Doctor Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $now = date('Y-m-d H:i:s');
                $sql = "SELECT * FROM group_counselings WHERE end_time > '$now'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $action = "<script>document.write(checkTime('{$row['start_time']}', '{$row['end_time']}', '{$row['link']}'));</script>";
                        echo "<tr>
                            <td>" . htmlspecialchars($row['details']) . "</td>
                            <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                            <td>{$row['start_time']}</td>
                            <td>{$row['end_time']}</td>
                            <td>$action</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No upcoming group counseling sessions.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>