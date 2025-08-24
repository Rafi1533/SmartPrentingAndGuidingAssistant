<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = (int)$_GET['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
if (!$user) {
    header("Location: admin_parent_child_care_guideline.php");
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $sql = "UPDATE users SET first_name = '$first_name', phone = '$phone' WHERE id = $user_id";
    if ($conn->query($sql)) {
        $message = "Profile updated successfully!";
        $admin_id = $_SESSION['admin_id'];
        $conn->query("INSERT INTO parent_child_care_notifications (admin_id, message, type) VALUES ($admin_id, 'Profile updated for user: $first_name', 'profile_update')");
    } else {
        $message = "Error: " . $conn->error;
    }
    $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
}

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $time_left = (int)$_POST['time_left'];
    $sql = "INSERT INTO parent_child_care_tasks (user_id, task_name, time_left) VALUES ($user_id, '$task_name', $time_left)";
    if ($conn->query($sql)) {
        $message = "Task added successfully!";
        $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ($user_id, 'New task assigned: $task_name', 'new_task')");
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle task edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_task'])) {
    $task_id = (int)$_POST['task_id'];
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $time_left = (int)$_POST['time_left'];
    $sql = "UPDATE parent_child_care_tasks SET task_name = '$task_name', time_left = $time_left WHERE id = $task_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Task updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = (int)$_POST['task_id'];
    $sql = "DELETE FROM parent_child_care_tasks WHERE id = $task_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Task deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle video addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_video'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['url']);
    $sql = "INSERT INTO parent_child_care_videos (user_id, title, url) VALUES ($user_id, '$title', '$url')";
    if ($conn->query($sql)) {
        $message = "Video added successfully!";
        $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ($user_id, 'New video added: $title', 'new_video')");
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle video edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_video'])) {
    $video_id = (int)$_POST['video_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['url']);
    $sql = "UPDATE parent_child_care_videos SET title = '$title', url = '$url' WHERE id = $video_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Video updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle video deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_video'])) {
    $video_id = (int)$_POST['video_id'];
    $sql = "DELETE FROM parent_child_care_videos WHERE id = $video_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Video deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle tip addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tip'])) {
    $tip_text = $conn->real_escape_string($_POST['tip_text']);
    $sql = "INSERT INTO parent_child_care_tips (user_id, tip_text) VALUES ($user_id, '$tip_text')";
    if ($conn->query($sql)) {
        $message = "Tip added successfully!";
        $conn->query("INSERT INTO parent_child_care_notifications (user_id, message, type) VALUES ($user_id, 'New tip added: $tip_text', 'new_tip')");
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle tip edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_tip'])) {
    $tip_id = (int)$_POST['tip_id'];
    $tip_text = $conn->real_escape_string($_POST['tip_text']);
    $sql = "UPDATE parent_child_care_tips SET tip_text = '$tip_text' WHERE id = $tip_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Tip updated successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Handle tip deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_tip'])) {
    $tip_id = (int)$_POST['tip_id'];
    $sql = "DELETE FROM parent_child_care_tips WHERE id = $tip_id AND user_id = $user_id";
    if ($conn->query($sql)) {
        $message = "Tip deleted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch user tasks
$tasks = $conn->query("SELECT * FROM parent_child_care_tasks WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch user videos
$videos = $conn->query("SELECT * FROM parent_child_care_videos WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch user tips
$tips = $conn->query("SELECT * FROM parent_child_care_tips WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch child details
$child_details = $conn->query("SELECT * FROM parent_child_care_child_details WHERE user_id = $user_id")->fetch_assoc();

// Fetch notifications for the admin
$notifications = $conn->query("SELECT * FROM parent_child_care_notifications WHERE admin_id = {$_SESSION['admin_id']} AND is_read = 0 ORDER BY created_at DESC");
$notification_count = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile: <?php echo htmlspecialchars($user['first_name']); ?></title>
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
            color: var(--black);
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }
        .notification.overdue { background: var(--error-red); }
        .notification.completed { background: var(--success-green); }
        .notification.new_user, .notification.profile_update { background: var(--info-blue); }
        .notification .close-btn {
            margin-left: 10px;
            cursor: pointer;
            background: none;
            border: none;
            color: var(--white);
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
            text-decoration: none;
            transition: background 0.3s, transform 0.2s;
        }
        .action-btn:hover {
            background-color: var(--blue-dark);
            transform: translateY(-2px);
        }
        .delete-btn {
            background-color: var(--error-red);
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        body.dark input[type="text"], body.dark input[type="number"], body.dark textarea {
            border-color: var(--blue-dark);
            background: var(--gray-dark);
            color: var(--white);
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
        <span>User Profile: <?php echo htmlspecialchars($user['first_name']); ?></span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_parent_child_care_guideline.php">Back to Dashboard</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
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
        <h2>User Profile</h2>
        <?php if (isset($message)) { ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <!-- Profile Update Form -->
        <h3>Update Profile</h3>
        <form method="POST">
            <div class="form-group">
                <label for="first_name">Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- Child Details -->
        <h3>Child Details</h3>
        <?php if ($child_details) { ?>
            <p>Number of Kids: <?php echo $child_details['number_of_kids']; ?></p>
            <p>Ages: <?php echo htmlspecialchars($child_details['ages']); ?></p>
            <p>Twins: <?php echo $child_details['twins']; ?></p>
            <p>Details: <?php echo htmlspecialchars($child_details['details']); ?></p>
        <?php } else { ?>
            <p>No child details available.</p>
        <?php } ?>

        <!-- Task Management -->
        <h3>Tasks</h3>
        <form method="POST">
            <div class="form-group">
                <label for="task_name">Task Name:</label>
                <input type="text" id="task_name" name="task_name" required>
            </div>
            <div class="form-group">
                <label for="time_left">Time Left (seconds):</label>
                <input type="number" id="time_left" name="time_left" required>
            </div>
            <button type="submit" name="add_task">Add Task</button>
        </form>
        <table>
            <tr>
                <th>Task</th>
                <th>Time Left</th>
                <th>Status</th>
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
                    <td><?php echo $task['status']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
                            <input type="number" name="time_left" value="<?php echo $task['time_left']; ?>" required>
                            <button type="submit" name="edit_task" class="action-btn">Edit</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" name="delete_task" class="action-btn delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <!-- Video Management -->
        <h3>Videos</h3>
        <form method="POST">
            <div class="form-group">
                <label for="title">Video Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="url">Video URL:</label>
                <input type="text" id="url" name="url" required>
            </div>
            <button type="submit" name="add_video">Add Video</button>
        </form>
        <div class="video-tutorials">
            <h3>Child Care Video Tutorials</h3>
            <?php if ($videos->num_rows > 0) { ?>
                <div class="video-list">
                    <?php while ($video = $videos->fetch_assoc()) { ?>
                        <div class="video-item">
                            <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                            <iframe src="<?php echo htmlspecialchars($video['url']); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <input type="text" name="title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                                <input type="text" name="url" value="<?php echo htmlspecialchars($video['url']); ?>" required>
                                <button type="submit" name="edit_video" class="action-btn">Edit</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" name="delete_video" class="action-btn delete-btn">Delete</button>
                            </form>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <p>No videos available.</p>
            <?php } ?>
        </div>

        <!-- Tip Management -->
        <h3>Tips</h3>
        <form method="POST">
            <div class="form-group">
                <label for="tip_text">Tip Text:</label>
                <textarea id="tip_text" name="tip_text" required></textarea>
            </div>
            <button type="submit" name="add_tip">Add Tip</button>
        </form>
        <table>
            <tr>
                <th>Tip</th>
                <th>Action</th>
            </tr>
            <?php while ($tip = $tips->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($tip['tip_text']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                            <textarea name="tip_text" required><?php echo htmlspecialchars($tip['tip_text']); ?></textarea>
                            <button type="submit" name="edit_tip" class="action-btn">Edit</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="tip_id" value="<?php echo $tip['id']; ?>">
                            <button type="submit" name="delete_tip" class="action-btn delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

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