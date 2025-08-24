<?php
session_start();
include 'db.php';

// Check admin login for security
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Publish or Delete actions
    if (isset($_POST['publish_id'])) {
        $publish_id = intval($_POST['publish_id']);
        $conn->query("UPDATE achievement_stories SET is_published = 1 WHERE id = $publish_id");
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        // Optionally, delete media file
        $result = $conn->query("SELECT media_path FROM achievement_stories WHERE id = $delete_id");
        if ($result && $row = $result->fetch_assoc()) {
            if ($row['media_path'] && file_exists($row['media_path'])) {
                unlink($row['media_path']);
            }
        }
        $conn->query("DELETE FROM achievement_stories WHERE id = $delete_id");
    }
}

// Fetch all stories with user full name
$result = $conn->query("
    SELECT achievement_stories.*, CONCAT(users.first_name, ' ', users.last_name) AS full_name 
    FROM achievement_stories 
    JOIN users ON achievement_stories.user_id = users.id
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin - Manage Achievement Stories</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0; padding: 0;
            background: #f4f4f4;
            text-align: center;
        }
        .navbar {
            background: #1565C0;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .navbar ul {
            list-style: none;
            margin: 0; padding: 0;
            display: flex;
        }
        .navbar ul li {
            margin: 0 15px;
        }
        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }
        .navbar ul li a:hover {
            color: #FFD700;
        }
        .container {
            width: 90%;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-align: left;
        }
        h2 {
            color: #1565C0;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #1565C0;
            color: white;
        }
        tr:hover {
            background-color: #E1F5FE;
        }
        img.story-img {
            max-width: 100px;
            max-height: 80px;
            border-radius: 5px;
        }
        form {
            margin: 0;
        }
        button {
            margin: 2px;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease-in-out;
        }
        .publish-btn {
            background: #4CAF50;
            color: white;
        }
        .publish-btn:hover {
            background: #388E3C;
        }
        .delete-btn {
            background: #d32f2f;
            color: white;
        }
        .delete-btn:hover {
            background: #b71c1c;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <span>Admin - Manage Achievement Stories</span>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Achievement Stories</h2>
    <table>
        <thead>
            <tr>
                <th>Story ID</th>
                <th>User</th>
                <th>Title</th>
                <th>Description</th>
                <th>Media</th>
                <th>Published</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td>
                    <?php if ($row['media_path']): ?>
                        <img src="<?= htmlspecialchars($row['media_path']) ?>" alt="Story Media" class="story-img" />
                    <?php else: ?>
                        No media
                    <?php endif; ?>
                </td>
                <td><?= $row['is_published'] ? 'Yes' : 'No' ?></td>
                <td>
                    <?php if (!$row['is_published']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="publish_id" value="<?= $row['id'] ?>" />
                            <button type="submit" class="publish-btn">Publish</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure to delete this story?');">
                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>" />
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
