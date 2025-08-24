<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = (int)$_GET['user_id'];

// Fetch user details
$sql_user = "SELECT mu.*, u.first_name FROM maternity_users mu JOIN users u ON mu.user_id = u.id WHERE mu.user_id = $user_id";
$user_result = $conn->query($sql_user);
$user = $user_result->fetch_assoc();

// Fetch all task dates
$sql_task_dates = "SELECT DISTINCT task_date FROM maternity_tasks WHERE user_id = $user_id ORDER BY task_date DESC";
$task_dates = $conn->query($sql_task_dates);
$task_dates_count = $task_dates->num_rows;

// Debug query result
if (!$task_dates) {
    error_log("Task dates query failed: " . $conn->error);
}

// Handle task addition
if (isset($_POST['add_task'])) {
    $task_date = $conn->real_escape_string($_POST['task_date']);
    $task_title = $conn->real_escape_string($_POST['task_title']);
    $task_details = $conn->real_escape_string($_POST['task_details']);
    $task_time = $conn->real_escape_string($_POST['task_time']);
    $sql = "INSERT INTO maternity_tasks (user_id, task_date, task_title, task_details, task_time) VALUES ($user_id, '$task_date', '$task_title', '$task_details', '$task_time')";
    if ($conn->query($sql)) {
        header("Location: maternity_admin_user_profile.php?user_id=$user_id");
        exit;
    } else {
        $message = "Error adding task: " . $conn->error;
    }
}

// Handle task update
if (isset($_POST['update_task'])) {
    $task_id = (int)$_POST['task_id'];
    $task_title = $conn->real_escape_string($_POST['task_title']);
    $task_details = $conn->real_escape_string($_POST['task_details']);
    $task_time = $conn->real_escape_string($_POST['task_time']);
    $sql = "UPDATE maternity_tasks SET task_title='$task_title', task_details='$task_details', task_time='$task_time' WHERE id=$task_id AND user_id=$user_id";
    if ($conn->query($sql)) {
        header("Location: maternity_admin_user_profile.php?user_id=$user_id");
        exit;
    } else {
        $message = "Error updating task: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity User Profile - Admin</title>
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
        }
        body.dark .form-section {
            background: #1e2a4f;
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
        .task-link {
            color: var(--cool-blue);
            text-decoration: none;
            transition: color var(--transition);
        }
        .task-link:hover {
            color: var(--cool-blue-dark);
        }
        body.dark .task-link {
            color: var(--cool-blue-light);
        }
        body.dark .task-link:hover {
            color: var(--white);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--box-shadow-light);
            color: var(--cool-gray-dark);
            position: relative;
        }
        body.dark .modal-content {
            background: var(--cool-gray-dark);
            color: var(--cool-blue-light);
        }
        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--cool-gray-dark);
            background: var(--cool-gray-light);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background var(--transition), color var(--transition);
        }
        .modal-close:hover {
            background: var(--cool-blue-light);
            color: var(--cool-blue-dark);
        }
        body.dark .modal-close {
            color: var(--cool-blue-light);
            background: #1e2a4f;
        }
        body.dark .modal-close:hover {
            background: #2a3a6a;
            color: var(--white);
        }
        .modal-close-btn {
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
            width: 100%;
            text-align: center;
        }
        .modal-close-btn:hover, .modal-close-btn:focus {
            background: var(--cool-blue);
            color: var(--white);
            box-shadow: 0 8px 24px rgba(49, 97, 185, 0.6);
            transform: scale(1.05);
        }
        body.dark .modal-close-btn {
            background: var(--cool-blue-light);
            color: var(--cool-gray-darker);
            border-color: var(--cool-blue-light);
        }
        body.dark .modal-close-btn:hover, body.dark .modal-close-btn:focus {
            background: var(--cool-blue-dark);
            color: var(--white);
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
        .no-tasks {
            text-align: center;
            font-style: italic;
            color: var(--cool-gray-dark);
            margin-top: 1rem;
        }
        body.dark .no-tasks {
            color: var(--cool-blue-light);
        }
        .message {
            margin: 1rem 0;
            padding: 12px 15px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-align: center;
            background: var(--error-red);
            color: var(--white);
        }
    </style>
    <script>
        function openTaskModal(date) {
            const modal = document.getElementById('task-modal');
            const modalContent = modal.querySelector('.modal-content');
            modalContent.innerHTML = `
                <span class="modal-close" onclick="closeModal('task-modal')">&times;</span>
                <h3>Tasks for ${date}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Details</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody id="task-table-${date}">
                    </tbody>
                </table>
                <button onclick="closeModal('task-modal')" class="modal-close-btn">Close</button>
            `;
            
            fetch(`maternity_fetch_tasks.php?user_id=<?php echo $user_id; ?>&date=${encodeURIComponent(date)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}, StatusText: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(tasks => {
                    const tbody = document.getElementById(`task-table-${date}`);
                    if (tasks.error) {
                        tbody.innerHTML = `<tr><td colspan="5" class="no-tasks">${tasks.error}</td></tr>`;
                    } else if (tasks.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="no-tasks">No tasks for this date.</td></tr>';
                    } else {
                        tasks.forEach(task => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${task.task_title}</td>
                                <td>${task.task_details}</td>
                                <td>${task.task_time}</td>
                                <td>${task.status}</td>
                                <td><button onclick="openEditTaskModal(${task.id}, '${task.task_title.replace(/'/g, "\\'")}', '${task.task_details.replace(/'/g, "\\'")}', '${task.task_time}')">Edit</button></td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching tasks:', error);
                    const tbody = document.getElementById(`task-table-${date}`);
                    tbody.innerHTML = '<tr><td colspan="5" class="no-tasks">Error loading tasks: ' + error.message + '</td></tr>';
                });
            
            modal.style.display = 'flex';
        }

        function openEditTaskModal(taskId, title, details, time) {
            const modal = document.getElementById('edit-task-modal');
            modal.querySelector('.modal-content').innerHTML = `
                <span class="modal-close" onclick="closeModal('edit-task-modal')">&times;</span>
                <h3>Edit Task</h3>
                <form method="POST">
                    <input type="hidden" name="task_id" value="${taskId}">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="task_title" value="${title.replace(/"/g, '&quot;')}" required>
                    </div>
                    <div class="form-group">
                        <label>Details</label>
                        <textarea name="task_details" required>${details}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="task_time" value="${time}" required>
                    </div>
                    <button type="submit" name="update_task">Update Task</button>
                    <button type="button" onclick="closeModal('edit-task-modal')" class="modal-close-btn">Close</button>
                </form>
            `;
            modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
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
        <h2>User Profile: <?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></h2>
        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="form-section">
            <h3>User Details</h3>
            <?php if ($user): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['number']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
                <p><strong>Pregnancy Months:</strong> <?php echo htmlspecialchars($user['pregnancy_months']); ?></p>
                <p><strong>Health Condition:</strong> <?php echo htmlspecialchars($user['health_condition']); ?></p>
            <?php else: ?>
                <p class="no-tasks">User not found.</p>
            <?php endif; ?>
        </div>

        <div class="form-section">
            <h3>Add New Task</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Task Date</label>
                    <input type="date" name="task_date" required>
                </div>
                <div class="form-group">
                    <label>Task Title</label>
                    <input type="text" name="task_title" required>
                </div>
                <div class="form-group">
                    <label>Task Details</label>
                    <textarea name="task_details" required></textarea>
                </div>
                <div class="form-group">
                    <label>Task Time</label>
                    <input type="time" name="task_time" required>
                </div>
                <button type="submit" name="add_task">Add Task</button>
            </form>
        </div>

        <h3>Task Dates</h3>
        <div>
            <?php if ($task_dates_count == 0): ?>
                <p class="no-tasks">No tasks assigned.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $task_dates->fetch_assoc()): ?>
                            <tr>
                                <td><a href="#" class="task-link" onclick="openTaskModal('<?php echo htmlspecialchars($row['task_date']); ?>')"><?php echo htmlspecialchars($row['task_date']); ?></a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="modal" id="task-modal">
            <div class="modal-content"></div>
        </div>
        <div class="modal" id="edit-task-modal">
            <div class="modal-content"></div>
        </div>
    </div>
</body>
</html>