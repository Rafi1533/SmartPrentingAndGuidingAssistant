<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT first_name FROM users WHERE id = $user_id")->fetch_assoc();

// Handle child details form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_child_details'])) {
    $number_of_kids = (int)$_POST['number_of_kids'];
    $ages = $conn->real_escape_string($_POST['ages']);
    $twins = $conn->real_escape_string($_POST['twins']);
    $details = $conn->real_escape_string($_POST['details']);

    $sql = "SELECT id FROM parent_child_care_child_details WHERE user_id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $sql = "UPDATE parent_child_care_child_details SET number_of_kids = $number_of_kids, ages = '$ages', twins = '$twins', details = '$details' WHERE user_id = $user_id";
    } else {
        $sql = "INSERT INTO parent_child_care_child_details (user_id, number_of_kids, ages, twins, details) VALUES ($user_id, $number_of_kids, '$ages', '$twins', '$details')";
    }

    if ($conn->query($sql)) {
        $admin_id = $conn->query("SELECT id FROM admins LIMIT 1")->fetch_assoc()['id'];
        $message = "User {$user['first_name']} updated their child details.";
        $conn->query("INSERT INTO parent_child_care_notifications (admin_id, message, type) VALUES ($admin_id, '$message', 'profile_update')");
        $success_message = "Child details saved successfully!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Handle task completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $task_id = (int)$_POST['task_id'];
    $sql = "UPDATE parent_child_care_tasks SET status = 'completed' WHERE id = $task_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $admin_id = $conn->query("SELECT id FROM admins LIMIT 1")->fetch_assoc()['id'];
        $task_name = $conn->query("SELECT task_name FROM parent_child_care_tasks WHERE id = $task_id")->fetch_assoc()['task_name'];
        $message = "User {$user['first_name']} completed task: $task_name.";
        $conn->query("INSERT INTO parent_child_care_notifications (admin_id, message, type) VALUES ($admin_id, '$message', 'completed')");
    }
}

// Fetch child details
$sql_child_details = "SELECT * FROM parent_child_care_child_details WHERE user_id = $user_id";
$child_details_result = $conn->query($sql_child_details);
$has_child_details = $child_details_result->num_rows > 0;
$child_details = $child_details_result->fetch_assoc();

// Fetch tasks for the user
$sql_tasks = "SELECT * FROM parent_child_care_tasks WHERE user_id = $user_id ORDER BY created_at DESC";
$tasks = $conn->query($sql_tasks);

// Fetch videos for the user
$sql_videos = "SELECT * FROM parent_child_care_videos WHERE user_id = $user_id ORDER BY created_at DESC";
$videos = $conn->query($sql_videos);

// Fetch tips for the user
$sql_tips = "SELECT * FROM parent_child_care_tips WHERE user_id = $user_id ORDER BY created_at DESC";
$tips = $conn->query($sql_tips);

// Fetch notifications for the user
$sql_notifications = "SELECT * FROM parent_child_care_notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC";
$notifications = $conn->query($sql_notifications);
$notification_count = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Care Guidance</title>
    <style>
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
            --info-blue: #3498db;
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
            transition: color 0.3s, transform 0.3s;
        }
        .navbar ul li a:hover {
            color: #f8c471;
            transform: scale(1.1);
        }
        .dark-toggle {
            cursor: pointer;
            background: transparent;
            border: 2px solid var(--white);
            color: var(--white);
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 6px;
            transition: background-color 0.3s, color 0.3s;
        }
        .dark-toggle:hover {
            background-color: var(--white);
            color: var(--blue);
        }
        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        .notification-bell .badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--error-red);
            color: var(--white);
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
        }
        .notification-dropdown {
            position: absolute;
            top: 60px;
            right: 20px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            color: black;
        }
        body.dark .notification-dropdown {
            background: var(--gray-dark);
        }
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body.dark .notification-item {
            border-bottom: 1px solid var(--blue-dark);
        }
        .notification-item button {
            background: transparent;
            border: none;
            color: var(--blue);
            cursor: pointer;
        }
        .notification-item button:hover {
            color: var(--blue-dark);
        }
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: var(--white);
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }
        .notification.overdue { background: var(--error-red); }
        .notification.completed { background: var(--success-green); }
        .notification.new_task, .notification.new_video, .notification.new_tip { background: var(--info-blue); }
        .notification .close-btn {
            margin-left: 10px;
            cursor: pointer;
            background: none;
            border: none;
            color: var(--white);
        }
        .container {
            max-width: 800px;
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
        h2 {
            color: var(--blue);
            margin-bottom: 20px;
        }
        .greeting {
            font-size: 22px;
            margin-bottom: 20px;
        }
        .tips {
            background: #dff0d8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #3c763d;
            font-size: 18px;
        }
        body.dark .tips {
            background: #2e7d32;
            color: var(--white);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 16px;
            text-align: center;
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
        .action-btn {
            background-color: var(--blue);
            color: var(--white);
            padding: 6px 12px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s;
        }
        .action-btn:hover {
            background-color: var(--blue-dark);
            transform: translateY(-2px);
        }
        .action-input {
            transform: scale(1.2);
        }
        .video-tutorials {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
        }
        body.dark .video-tutorials {
            background-color: var(--gray-dark);
        }
        .video-tutorials h3 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .video-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .video-item {
            width: 45%;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .video-item:hover {
            transform: scale(1.05);
        }
        .video-item iframe {
            width: 100%;
            height: 200px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="number"], input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        body.dark input[type="number"], body.dark input[type="text"], body.dark textarea {
            border-color: var(--blue-dark);
            background: var(--gray-dark);
            color: var(--white);
        }
        input[type="radio"] {
            margin-right: 5px;
        }
        button[type="submit"] {
            background-color: var(--blue);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button[type="submit"]:hover {
            background-color: var(--blue-dark);
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            background: var(--success-green);
            color: var(--white);
        }
        .error {
            background: var(--error-red);
            color: var(--white);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span>Child Care Guidance</span>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li>
                <div class="notification-bell" id="notificationBell">
                    🔔 <span class="badge"><?php echo $notification_count; ?></span>
                </div>
                <div class="notification-dropdown" id="notificationDropdown">
                    <?php while ($notification = $notifications->fetch_assoc()) { ?>
                        <div class="notification-item">
                            <span><?php echo htmlspecialchars($notification['message']); ?> <br><small><?php echo $notification['created_at']; ?></small></span>
                            <button onclick="markNotificationRead(<?php echo $notification['id']; ?>)">Mark as read</button>
                        </div>
                    <?php } ?>
                </div>
            </li>
            <li><button class="dark-toggle" id="darkToggle">Dark Mode</button></li>
        </ul>
    </div>

    <div class="container">
        <h2>Child Care Guidance</h2>
        <div class="greeting">Hello, <?php echo htmlspecialchars($user['first_name']); ?>!</div>

        <!-- Child Details Form -->
        <h3>Child Details</h3>
        <form method="POST">
            <div class="form-group">
                <label for="number_of_kids">Number of Kids:</label>
                <input type="number" id="number_of_kids" name="number_of_kids" value="<?php echo $has_child_details ? $child_details['number_of_kids'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="ages">Ages (comma-separated):</label>
                <input type="text" id="ages" name="ages" value="<?php echo $has_child_details ? htmlspecialchars($child_details['ages']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Twins:</label>
                <input type="radio" id="twins_yes" name="twins" value="yes" <?php echo $has_child_details && $child_details['twins'] == 'yes' ? 'checked' : ''; ?> required> Yes
                <input type="radio" id="twins_no" name="twins" value="no" <?php echo $has_child_details && $child_details['twins'] == 'no' ? 'checked' : ''; ?>> No
            </div>
            <div class="form-group">
                <label for="details">Details:</label>
                <textarea id="details" name="details" required><?php echo $has_child_details ? htmlspecialchars($child_details['details']) : ''; ?></textarea>
            </div>
            <button type="submit" name="save_child_details">Save Child Details</button>
        </form>
        <?php if (isset($success_message)) echo "<div class='message success'>$success_message</div>"; ?>
        <?php if (isset($error_message)) echo "<div class='message error'>$error_message</div>"; ?>

        <!-- Tips Section -->
        <h3>Personalized Tips</h3>
        <?php if ($tips->num_rows > 0) { ?>
            <div class="tips">
                <?php while ($tip = $tips->fetch_assoc()) { ?>
                    <p><?php echo htmlspecialchars($tip['tip_text']); ?></p>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No tips available.</p>
        <?php } ?>

        <!-- Tasks Table -->
        <h3>Your Tasks</h3>
        <table id="todo-table">
            <tr>
                <th>Task</th>
                <th>Time Left</th>
                <th>Action</th>
            </tr>
            <?php while ($task = $tasks->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                    <td data-time="<?php echo $task['time_left']; ?>" data-task-id="<?php echo $task['id']; ?>">
                        <?php
                        if ($task['status'] == 'completed') {
                            echo "Submitted";
                        } elseif ($task['status'] == 'due') {
                            echo "Due";
                        } else {
                            $minutes = floor($task['time_left'] / 60);
                            $seconds = $task['time_left'] % 60;
                            echo "$minutes:" . str_pad($seconds, 2, "0", STR_PAD_LEFT);
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($task['status'] != 'completed') { ?>
                            <form method="POST">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="checkbox" name="task_complete" class="action-input" onchange="this.form.submit()">
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <!-- Video Tutorials Section -->
        <div class="video-tutorials">
            <h3>Child Care Video Tutorials</h3>
            <?php if ($videos->num_rows > 0) { ?>
                <div class="video-list">
                    <?php while ($video = $videos->fetch_assoc()) { ?>
                        <div class="video-item">
                            <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                            <iframe src="<?php echo htmlspecialchars($video['url']); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <p>No videos available.</p>
            <?php } ?>
        </div>
    </div>

    <!-- Notifications -->
    <div id="notifications-container"></div>

    <script>
        // Dark mode toggle
        const darkToggle = document.getElementById('darkToggle');
        const body = document.body;
        function setDarkMode(enabled) {
            if (enabled) {
                body.classList.add('dark');
                darkToggle.textContent = 'Light Mode';
                localStorage.setItem('darkMode', 'enabled');
            } else {
                body.classList.remove('dark');
                darkToggle.textContent = 'Dark Mode';
                localStorage.setItem('darkMode', 'disabled');
            }
        }
        darkToggle.addEventListener('click', () => {
            setDarkMode(!body.classList.contains('dark'));
        });
        window.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('darkMode') === 'enabled') {
                setDarkMode(true);
            }
        });

        // Notification dropdown
        const notificationBell = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        notificationBell.addEventListener('click', () => {
            notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Mark notification as read
        function markNotificationRead(notificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `notification_id=${notificationId}`
            }).then(() => {
                location.reload();
            });
        }

        // Task countdown and notifications
        setInterval(() => {
            const tasks = document.querySelectorAll('#todo-table td[data-time]');
            tasks.forEach(task => {
                let timeLeft = parseInt(task.getAttribute('data-time'));
                let taskId = task.getAttribute('data-task-id');
                if (timeLeft > 0) {
                    timeLeft--;
                    task.setAttribute('data-time', timeLeft);
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    task.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                    if (timeLeft === 600) {
                        showNotification(`Task due in 10 minutes: ${task.parentElement.cells[0].textContent}`, 'new_task');
                        fetch('add_notification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `user_id=<?php echo $user_id; ?>&message=Task due in 10 minutes: ${task.parentElement.cells[0].textContent}&type=new_task`
                        });
                    } else if (timeLeft === 0) {
                        task.textContent = "Due";
                        task.removeAttribute('data-time');
                        showNotification(`Task overdue: ${task.parentElement.cells[0].textContent}`, 'overdue');
                        fetch('add_notification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `user_id=<?php echo $user_id; ?>&message=Task overdue: ${task.parentElement.cells[0].textContent}&type=overdue`
                        });
                        fetch('add_notification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `admin_id=<?php echo $admin_id; ?>&message=User <?php echo $user['first_name']; ?> has overdue task: ${task.parentElement.cells[0].textContent}&type=overdue`
                        });
                        fetch('update_task_status.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `task_id=${taskId}&status=due`
                        });
                    }
                }
            });
        }, 1000);

        // Show notification
        function showNotification(message, type) {
            const container = document.getElementById('notifications-container');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `${message} <button class="close-btn">✖</button>`;
            container.appendChild(notification);
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
                notification.remove();
            }, 5000);
            notification.querySelector('.close-btn').addEventListener('click', () => {
                notification.style.display = 'none';
                notification.remove();
            });
        }
    </script>
</body>
</html>