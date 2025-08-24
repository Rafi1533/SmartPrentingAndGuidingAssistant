<?php
session_start();
include 'db.php';

// For demo, assuming user logged in with user_id in session
if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    // Handle file upload
    $media_path = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['media']['type'], $allowed_types)) {
            $upload_dir = 'uploads/achievements/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid() . '_' . basename($_FILES['media']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
                $media_path = $target_file;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO achievement_stories (user_id, title, description, media_path, is_published, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param('isss', $user_id, $title, $description, $media_path);
    if ($stmt->execute()) {
        $message = "Story uploaded successfully and awaiting admin approval.";
    } else {
        $message = "Failed to upload story.";
    }
    $stmt->close();
}

// Fetch user's stories to display
$stmt = $conn->prepare("SELECT * FROM achievement_stories WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stories_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Achievement Story Upload</title>
    <style>
        /* Your CSS from before with small tweaks */
         body {
    font-family: 'Poppins', sans-serif;
    background: #f0f8ff url('story.png') no-repeat center center fixed;
    background-size: cover; /* ensures the image covers the entire background */
    color: #0a3d62;
    margin: 0;
    min-height: 100vh;
}
        .navbar {
            background: #1565C0;
            color: white;
            padding: 15px;
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
            width: 80%;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-align: left;
            border-color: rgba(0, 204, 255, 1);
            border-width: 10px;
            background: rgba(255, 228, 56, 0.33);
        }
        h2 {
            color: #1565C0;
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            margin-bottom: 30px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #01141aab;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #2fc2d3ff;
            color: white;
            padding: 12px;
            border: none;
            font-size: 18px;
            cursor: pointer;
            margin-top: 15px;
            border-radius: 20px;
            transition: background 0.3s ease-in-out;
        }
        button:hover {
            background: #9C27B0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #9C27B0;
            color: white;
        }
        tr:hover {
            background-color: #E1BEE7;
        }
        img.story-img {
            max-width: 100px;
            max-height: 80px;
            border-radius: 5px;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <span>Achievement Story Upload</span>
    <ul>
        <li><a href="parent_dashboard.php">Home</a></li>
        <li><a href="childcare.php">Child's Care</a></li>
        <li><a href="parentcare.php">Parent's Care</a></li>
        <li><a href="parent_logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Upload Achievement Story</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Story Title" required />
        <textarea name="description" placeholder="Story Description" rows="4" required></textarea>
        <input type="file" name="media" accept="image/*" />
        <button type="submit">Upload Story</button>
    </form>

    <h3>Your Uploaded Stories</h3>
    <table>
        <thead>
            <tr>
                <th>Story ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Media</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stories_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <?php if ($row['media_path']): ?>
                            <img src="<?= htmlspecialchars($row['media_path']) ?>" alt="Story Media" class="story-img" />
                        <?php else: ?>
                            No media
                        <?php endif; ?>
                    </td>
                    <td><?= $row['is_published'] ? 'Published' : 'Pending' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
