<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all maternity users, ordered by last_updated or created_at
$sql_users = "SELECT mu.*, u.email, u.first_name FROM maternity_users mu JOIN users u ON mu.user_id = u.id ORDER BY COALESCE(mu.last_updated, mu.created_at) DESC";
$users = $conn->query($sql_users);

// Check for users with no tasks for today
$today = date('Y-m-d');
$sql_no_tasks = "SELECT mu.user_id, u.first_name FROM maternity_users mu JOIN users u ON mu.user_id = u.id 
                 WHERE NOT EXISTS (
                     SELECT 1 FROM maternity_tasks mt WHERE mt.user_id = mu.user_id AND mt.task_date = '$today'
                 )";
$no_tasks_users = $conn->query($sql_no_tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity Admin Dashboard</title>
    <style>
        :root {
            --cool-blue: #4a90e2;
            --cool-blue-light: #8bb9ff;
            --cool-blue-dark: #3161b9;
            --cool-gray-light: #f0f4f8;
            --cool-gray-dark: #2f3e5e;
            --cool-gray-darker: #1a2640;
            --white: #fff;
            --error-red: #e94b4b;
            --success-green: #3cb371;
            --transition: 0.3s ease;
            --border-radius: 8px;
            --box-shadow-light: 0 4px 20px rgba(74, 144, 226, 0.15);
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--cool-gray-light);
            color: var(--cool-gray-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        body.dark {
            background: var(--cool-gray-darker);
            color: var(--cool-blue-light);
        }
        nav.navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: var(--white);
            box-shadow: var(--box-shadow-light);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        body.dark nav.navbar {
            background: var(--cool-gray-dark);
        }
        nav.navbar .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--cool-blue);
        }
        body.dark nav.navbar .logo {
            color: var(--cool-blue-light);
        }
        nav.navbar .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        nav.navbar a {
            text-decoration: none;
            color: var(--cool-gray-dark);
            font-weight: 600;
            padding-bottom: 4px;
            transition: color var(--transition), border-bottom-color var(--transition);
            border-bottom: 2px solid transparent;
        }
        nav.navbar a:hover,
        nav.navbar a:focus {
            color: var(--cool-blue);
            border-bottom-color: var(--cool-blue);
        }
        body.dark nav.navbar a {
            color: var(--cool-blue-light);
        }
        body.dark nav.navbar a:hover,
        body.dark nav.navbar a:focus {
            color: var(--white);
            border-bottom-color: var(--white);
        }
        .container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-light);
            color: var(--cool-gray-dark);
        }
        body.dark .container {
            background: var(--cool-gray-dark);
            color: var(--cool-blue-light);
        }
        h2 {
            margin: 0 0 1.5rem 0;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 12px;
            border: 1px solid #a6b8d9;
            text-align: left;
        }
        th {
            background: var(--cool-blue);
            color: var(--white);
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: var(--cool-gray-light);
        }
        tr:hover {
            background: var(--cool-blue-light);
            transition: background var(--transition);
        }
        body.dark tr:nth-child(even) {
            background: #1e2a4f;
        }
        body.dark tr:hover {
            background: #2a3a6a;
        }
        .user-link {
            color: var(--cool-blue);
            text-decoration: none;
            transition: color var(--transition);
        }
        .user-link:hover {
            color: var(--cool-blue-dark);
        }
        body.dark .user-link {
            color: var(--cool-blue-light);
        }
        body.dark .user-link:hover {
            color: var(--white);
        }
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--error-red);
            color: var(--white);
            padding: 12px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-light);
            display: none;
            z-index: 1000;
        }
    </style>
    <script>
        <?php if ($no_tasks_users->num_rows > 0): ?>
            <?php while ($row = $no_tasks_users->fetch_assoc()): ?>
                showNotification("No tasks assigned for <?php echo htmlspecialchars($row['first_name']); ?> today!");
            <?php endwhile; ?>
        <?php endif; ?>

        function showNotification(message) {
            console.log('Showing admin notification:', message); // Debug
            const notification = document.createElement('div');
            notification.classList.add('notification');
            notification.textContent = message;
            document.body.appendChild(notification);
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
                notification.remove();
            }, 5000);
        }
    </script>
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="Primary navigation">
        <div class="logo">Maternity Admin Dashboard</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>Manage Maternity Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Location</th>
                    <th>Pregnancy Months</th>
                    <th>Health Condition</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><a href="maternity_admin_user_profile.php?user_id=<?php echo $row['user_id']; ?>" class="user-link"><?php echo htmlspecialchars($row['name']); ?></a></td>
                        <td><?php echo htmlspecialchars($row['number']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo $row['pregnancy_months']; ?></td>
                        <td><?php echo htmlspecialchars($row['health_condition']); ?></td>
                        <td><?php echo $row['last_updated'] ?: $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>