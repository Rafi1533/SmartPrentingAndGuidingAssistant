<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Autism Screening Results</title>
    <style>
       /* Base Styles */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    text-align: center;
    color: #333;
    background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb);
}

/* Navbar Styles */
.navbar {
    background: linear-gradient(90deg, #ff6b6b, #6a11cb, #2575fc);
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    border-radius: 0 0 20px 20px;
}

.navbar span {
    font-weight: bold;
    font-size: 18px;
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
    font-weight: 600;
    transition: all 0.3s ease-in-out;
    padding: 5px 10px;
    border-radius: 5px;
}

.navbar ul li a:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.05);
}

/* Container Styles */
.container {
    max-width: 900px;
    margin: 50px auto;
    padding: 30px;
    background: rgba(255, 255, 255, 0.44);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: all 0.3s ease-in-out;
}

/* Heading */
.container h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #2c3e50;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    transition: background 0.3s ease-in-out;
}

th {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    font-weight: 600;
}

tr:hover {
    background: rgba(52, 152, 219, 0.15);
    transform: scale(1.01);
}

/* Responsive Table */
@media screen and (max-width: 768px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
    }
    .navbar ul {
        flex-direction: column;
        width: 100%;
    }
    .navbar ul li {
        margin: 10px 0;
    }
    table, th, td {
        font-size: 14px;
    }
}
#bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
}

/* Dark overlay for readability */
.overlay {
    position: fixed;
    top: 0; 
    left: 0;
    width: 100%; 
    height: 100%;
    background: rgba(0,0,0,0.3); /* Adjust darkness */
    z-index: -1;
}

/* Keep content above video */
.navbar, .container {
    position: relative;
    z-index: 1;
}

    </style>
</head>
<body>
    <!-- Video Background -->
<video autoplay muted loop id="bg-video">
    <source src="quiz.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<!-- Optional Dark Overlay for readability -->
<div class="overlay"></div>


    <div class="navbar">
        <span>Your Autism Screening Results</span>
        <ul>
            <li><a href="autism_quiz.php">Back to Quiz</a></li>
            <li><a href="parent_dashboard.php">Home</a></li>
            <li><a href="specialchild.php">Special Child Home</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h2>Your Past Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Score</th>
                    <th>Risk Level</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM autism_results WHERE user_id = $user_id ORDER BY date DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . ucfirst($row['section']) . "</td>
                            <td>{$row['red_flags']}</td>
                            <td>{$row['risk_level']}</td>
                            <td>{$row['date']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No results found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>