<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $number = $conn->real_escape_string($_POST['number']);
    $location = $conn->real_escape_string($_POST['location']);
    $pregnancy_months = (int)$_POST['pregnancy_months'];
    $health_condition = $conn->real_escape_string($_POST['health_condition']);
    
    // Validate phone number (10-15 digits, optional +)
    if (!preg_match('/^\+?[0-9]{10,15}$/', $number)) {
        $message = "Invalid phone number format.";
    } else {
        // Check if profile exists
        $sql = "SELECT id FROM maternity_users WHERE user_id = $user_id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // Update profile
            $sql = "UPDATE maternity_users SET name='$name', number='$number', location='$location', pregnancy_months=$pregnancy_months, health_condition='$health_condition', last_updated=CURRENT_TIMESTAMP WHERE user_id=$user_id";
        } else {
            // Insert new profile
            $sql = "INSERT INTO maternity_users (user_id, name, number, location, pregnancy_months, health_condition) VALUES ($user_id, '$name', '$number', '$location', $pregnancy_months, '$health_condition')";
        }
        
        if ($conn->query($sql)) {
            $message = "Profile saved successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Mark task complete
if (isset($_POST['complete_task'])) {
    $task_id = (int)$_POST['task_id'];
    $sql = "UPDATE maternity_tasks SET status='completed' WHERE id=$task_id AND user_id=$user_id";
    if ($conn->query($sql)) {
        header("Location: maternity_user_dashboard.php");
        exit;
    }
}

// Get all tasks
$sql_all_tasks = "SELECT * FROM maternity_tasks WHERE user_id=$user_id ORDER BY task_date DESC, task_time";
$all_tasks = $conn->query($sql_all_tasks);
$task_count = $all_tasks->num_rows;

// Check if profile exists
$sql_profile = "SELECT * FROM maternity_users WHERE user_id=$user_id";
$profile = $conn->query($sql_profile);
$has_profile = $profile->num_rows > 0;
$profile_data = $profile->fetch_assoc();

// Fetch new tasks (created in last 5 minutes)
$sql_new_tasks = "SELECT * FROM maternity_tasks WHERE user_id=$user_id AND created_at >= NOW() - INTERVAL 5 MINUTE AND status='pending'";
$new_tasks = $conn->query($sql_new_tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity User Dashboard</title>
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
        h2, h3 {
            margin: 0 0 1.5rem 0;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
        }
        h3 {
            font-size: 1.5rem;
        }
        .form-section {
            margin: 1.5rem 0;
            padding: 1rem;
            background: var(--cool-gray-light);
            border-radius: var(--border-radius);
            display: none;
        }
        .form-section.active {
            display: block;
        }
        body.dark .form-section {
            background: #1e2a4f;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        input, textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--border-radius);
            border: 1.8px solid #a6b8d9;
            font-size: 1rem;
            background: var(--cool-gray-light);
            color: var(--cool-gray-dark);
            transition: border-color var(--transition), background var(--transition);
        }
        body.dark input, body.dark textarea {
            background: #1e2a4f;
            color: var(--cool-blue-light);
            border-color: #3b4c7d;
        }
        input:focus, textarea:focus {
            border-color: var(--cool-blue);
            background: var(--white);
            outline: none;
            box-shadow: 0 0 8px var(--cool-blue-light);
        }
        body.dark input:focus, body.dark textarea:focus {
            background: #2a3a6a;
            color: var(--white);
            border-color: var(--cool-blue-light);
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            margin-top: 1rem;
            padding: 14px;
            border: 1.8px solid var(--cool-blue);
            border-radius: 30px;
            background: var(--white);
            color: var(--cool-blue);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background var(--transition), color var(--transition), box-shadow var(--transition);
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
        }
        button:hover, button:focus {
            background: var(--cool-blue);
            color: var(--white);
            box-shadow: 0 8px 24px rgba(49, 97, 185, 0.6);
            transform: scale(1.05);
        }
        body.dark button {
            background: var(--cool-blue-light);
            color: var(--cool-gray-darker);
            border-color: var(--cool-blue-light);
        }
        body.dark button:hover, body.dark button:focus {
            background: var(--cool-blue-dark);
            color: var(--white);
        }
        .message {
            margin: 1rem 0;
            padding: 12px 15px;
            border-radius: var(--border-radius);
            font-weight: 600;
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
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--cool-blue);
            vertical-align: middle;
            margin: 0;
        }
        .no-tasks {
            text-align: center;
            font-style: italic;
            color: var(--cool-gray-dark);
            margin-top: 1rem;
        }
        body.dark .no-tasks {
            color: var(--cool-blue-light);
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
        let tasks = <?php
            $task_array = [];
            while ($task = $all_tasks->fetch_assoc()) {
                $task_array[] = $task;
            }
            $all_tasks->data_seek(0); // Reset cursor for HTML
            echo json_encode($task_array);
        ?>;
        
        function checkTasks() {
            const now = new Date();
            console.log('Current time:', now.toISOString()); // Debug
            console.log('Tasks:', tasks); // Debug
            tasks.forEach(task => {
                const taskDateTime = new Date(`${task.task_date}T${task.task_time}+06:00`);
                const tenMinAfter = new Date(taskDateTime.getTime() + 10 * 60 * 1000);
                
                console.log('Task:', task.task_title, 'Time:', taskDateTime.toISOString(), 'Status:', task.status); // Debug
                if (now >= taskDateTime && now < tenMinAfter && task.status === 'pending') {
                    showNotification(`Time for task: ${task.task_title}`);
                } else if (now >= tenMinAfter && task.status === 'pending') {
                    showNotification(`Reminder: Complete task "${task.task_title}"!`);
                }
            });
        }

        function showNotification(message) {
            console.log('Showing notification:', message); // Debug
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

        document.addEventListener('DOMContentLoaded', () => {
            showNotification('Welcome to your dashboard!'); // Test notification
            checkTasks();
            setInterval(checkTasks, 60000); // Check every minute

            // New task notifications
            <?php while ($new_task = $new_tasks->fetch_assoc()): ?>
                showNotification('New task assigned: <?php echo htmlspecialchars($new_task['task_title']); ?>');
            <?php endwhile; ?>

            // Update Profile button handler
            const updateBtn = document.getElementById('update-profile-btn');
            if (updateBtn) {
                updateBtn.addEventListener('click', () => {
                    const formSection = document.getElementById('profile-section');
                    formSection.classList.toggle('active');
                });
            }
        });
    </script>
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="Primary navigation">
        <div class="logo">Maternity Care Portal</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>Your Maternity Dashboard</h2>

        <div class="form-section" id="profile-section" <?php echo !$has_profile ? 'class="form-section active"' : ''; ?>>
            <h3>Your Pregnancy Profile</h3>
            <?php if (isset($message)): ?>
                <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" aria-labelledby="profileTitle">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($profile_data['name']) ? htmlspecialchars($profile_data['name']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="number">Phone Number</label>
                    <input type="tel" id="number" name="number" value="<?php echo isset($profile_data['number']) ? htmlspecialchars($profile_data['number']) : ''; ?>" placeholder="+8801234567890" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo isset($profile_data['location']) ? htmlspecialchars($profile_data['location']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="pregnancy_months">Pregnancy Duration (Months)</label>
                    <input type="number" id="pregnancy_months" name="pregnancy_months" min="1" max="9" value="<?php echo isset($profile_data['pregnancy_months']) ? $profile_data['pregnancy_months'] : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="health_condition">Health Condition</label>
                    <textarea id="health_condition" name="health_condition" required><?php echo isset($profile_data['health_condition']) ? htmlspecialchars($profile_data['health_condition']) : ''; ?></textarea>
                </div>
                <button type="submit" name="save_profile">Save Profile</button>
            </form>
        </div>

        <?php if ($has_profile): ?>
            <button id="update-profile-btn">Update Profile</button>
        <?php endif; ?>

        <h3>All Tasks</h3>
        <div>
            <?php if ($task_count == 0): ?>
                <p class="no-tasks">No tasks assigned.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Task Title</th>
                            <th>Details</th>
                            <th>Time</th>
                            <th>Complete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $all_tasks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['task_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['task_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['task_details']); ?></td>
                                <td><?php echo htmlspecialchars($row['task_time']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                                            <input type="checkbox" name="complete_task" onchange="this.form.submit()" aria-label="Mark task <?php echo htmlspecialchars($row['task_title']); ?> as complete">
                                        </form>
                                    <?php else: ?>
                                        Completed
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>