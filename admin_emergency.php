<?php
session_start();
include 'db.php';

// TODO: Add admin authentication check here if you want!

// Handle "Admitted" deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM emergencies WHERE id = $delete_id");
    header("Location: admin_emergency.php");
    exit();
}

$sql = "SELECT * FROM emergencies ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin - View Emergencies</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0; padding: 0; background: #f4f4f4; color: #333;
    }
    .navbar {
        background: #1565C0; color: white;
        padding: 15px; display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .navbar ul {
        list-style: none; margin: 0; padding: 0; display: flex;
    }
    .navbar ul li {
        margin: 0 15px;
    }
    .navbar ul li a {
        color: white; text-decoration: none; font-weight: bold;
        transition: color 0.3s ease-in-out;
    }
    .navbar ul li a:hover {
        color: #FFD700;
    }
    .container {
        width: 90%; margin: 30px auto; background: white;
        padding: 25px; border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    h2 {
        color: #1565C0; margin-bottom: 15px;
    }
    table {
        width: 100%; border-collapse: collapse; margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd; padding: 12px; text-align: left;
        vertical-align: middle;
    }
    th {
        background: #1565C0; color: white;
    }
    .media-preview img, .media-preview video {
        max-width: 150px; max-height: 150px;
        border-radius: 5px; cursor: pointer;
    }
    .media-preview img:hover, .media-preview video:hover {
        opacity: 0.8;
    }
    .location-btn, .action-btn {
        background: #d32f2f; padding: 8px 12px;
        border: none; color: white; border-radius: 5px;
        cursor: pointer; margin-right: 5px;
        font-size: 14px;
        transition: background 0.3s;
    }
    .location-btn:hover, .action-btn:hover {
        background: #b71c1c;
    }
    .action-btn {
        background-color: #4CAF50;
    }
    .action-btn:hover {
        background-color: #388E3C;
    }
</style>
</head>
<body>

<nav class="navbar">
    <span>Admin - View Emergencies</span>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Emergency Requests</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>Phone</th>
                <th>Description</th>
                <th>Media</th>
                <th>Live Location</th>
                <th>Submitted At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td class="media-preview">
                    <?php if ($row['media_path']): 
                        $ext = pathinfo($row['media_path'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($ext), ['mp4', 'webm', 'ogg'])): ?>
                            <video controls>
                                <source src="<?= htmlspecialchars($row['media_path']) ?>" type="video/<?= $ext ?>" />
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($row['media_path']) ?>" alt="Media" />
                        <?php endif; 
                    else: ?>
                        No Media
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['latitude'] && $row['longitude']): ?>
                    <button class="location-btn" onclick="openLocation(<?= $row['latitude'] ?>, <?= $row['longitude'] ?>)">View Location</button>
                    <?php else: ?>
                    N/A
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="admin_emergency.php?delete_id=<?= $row['id'] ?>" class="action-btn" onclick="return confirm('Mark as admitted and delete this emergency?')">Admitted</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function openLocation(lat, lon) {
    const url = `https://www.google.com/maps?q=${lat},${lon}`;
    window.open(url, '_blank');
}
</script>

</body>
</html>
