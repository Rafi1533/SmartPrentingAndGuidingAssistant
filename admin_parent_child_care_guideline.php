<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Fetch all users, sorted by id
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

// Fetch notifications for the admin
$sql_notifications = "SELECT * FROM parent_child_care_notifications WHERE admin_id = $admin_id AND is_read = 0 ORDER BY created_at DESC";
$notifications = $conn->query($sql_notifications);
$notification_count = $notifications->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Child Care Dashboard</title>
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
        h2 {
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
    </style>
</head>
<body>
    <div class="navbar">
        <span>Admin Child Care Dashboard</span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
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
        <h2>User Management</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Action</th>
            </tr>
            <?php while ($user = $users->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></td>
                    <td><a href="admin_parent_child_care_user_profile.php?user_id=<?php echo $user['id']; ?>" class="action-btn">View Profile</a></td>
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