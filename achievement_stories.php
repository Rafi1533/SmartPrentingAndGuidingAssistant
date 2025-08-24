<?php
session_start();
include 'db.php'; // Your DB connection file

// Fetch all published achievement stories with user full name
$sql = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS full_name 
        FROM achievement_stories a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.is_published = 1
        ORDER BY a.id DESC";

$result = $conn->query($sql);

$allStories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $allStories[] = [
            'id' => $row['id'],
            'title' => $row['title'],            // Use story title here
            'user' => $row['full_name'],         // User full name as author
            'description' => $row['description'],
            'image' => $row['media_path'] ?: 'https://via.placeholder.com/300'
        ];
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Achievement Stories</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0; padding: 0;
            background: #f1f2f6;
            text-align: center;
        }
        /* Navbar */
        .navbar {
            background-color: #34495e;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar ul {
            list-style: none;
            margin: 0; padding: 0;
            display: flex;
        }
        .navbar ul li {
            margin: 0 20px;
        }
        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }
        .navbar ul li a:hover {
            color: #f39c12;
        }
        /* Achievement Story Section */
        .story-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 50px;
            margin-top: 30px;
        }
        .story-card {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            text-align: center;
        }
        .story-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .story-card h3 {
            margin-top: 15px;
            font-size: 20px;
            color: #2c3e50;
        }
        .story-card p {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 10px;
        }
        .story-card .view-story-btn {
            background-color: #f39c12;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease-in-out;
        }
        .story-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .story-card:hover .view-story-btn {
            background-color: #e67e22;
        }
        /* Modal */
        .story-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 60%;
            max-width: 700px;
            text-align: left;
            position: relative;
        }
        .modal-content h3 {
            margin-top: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .modal-content p {
            color: #7f8c8d;
            font-size: 16px;
        }
        .modal-content img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 20px;
        }
        /* Author name styling */
        #story-user {
            font-style: italic;
            color: #555;
            margin-top: 5px;
        }
        /* Close Button */
        .close-btn {
            background-color: #f39c12;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease-in-out;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 16px;
        }
        .close-btn:hover {
            background-color: #e67e22;
        }
        @media(max-width: 700px){
            .modal-content {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <span>Achievement Stories</span>
        <ul>
            <li><a href="specialchild.php">Special Children Home</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Achievement Story Section -->
    <div class="story-container">
        <?php foreach ($allStories as $story): ?>
            <div class="story-card" onclick="openStoryModal('<?php echo $story['id']; ?>')">
                <img src="<?php echo htmlspecialchars($story['image']); ?>" alt="Story Image" />
                <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                <p><?php echo htmlspecialchars(substr($story['description'], 0, 80)) . '...'; ?></p>
                <button class="view-story-btn">View Story</button>
            </div>
        <?php endforeach; ?>
        <?php if (empty($allStories)): ?>
            <p>No achievement stories published yet.</p>
        <?php endif; ?>
    </div>

    <!-- Achievement Story Details Modal -->
    <div class="story-modal" id="story-modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeStoryModal()">X</button>
            <h3 id="story-title"></h3>
            <p id="story-user"></p>
            <img id="story-image" src="" alt="Story Image" />
            <p id="story-description"></p>
        </div>
    </div>

    <script>
        // Pass PHP array to JS
        const stories = <?php echo json_encode($allStories); ?>;

        function openStoryModal(id) {
            const story = stories.find(s => s.id == id);
            if (!story) return;

            document.getElementById('story-title').textContent = story.title;
            document.getElementById('story-user').textContent = 'By: ' + story.user;
            document.getElementById('story-description').textContent = story.description;
            document.getElementById('story-image').src = story.image;
            document.getElementById('story-modal').style.display = 'flex';
        }

        function closeStoryModal() {
            document.getElementById('story-modal').style.display = 'none';
        }

        // Close modal on clicking outside content
        document.getElementById('story-modal').addEventListener('click', function(event){
            if(event.target === this){
                closeStoryModal();
            }
        });
    </script>

</body>
</html>
