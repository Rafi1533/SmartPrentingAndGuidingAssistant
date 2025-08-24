<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $description = $conn->real_escape_string($_POST['description']);
    $latitude = isset($_POST['latitude']) ? $conn->real_escape_string($_POST['latitude']) : NULL;
    $longitude = isset($_POST['longitude']) ? $conn->real_escape_string($_POST['longitude']) : NULL;
    $media_path = NULL;

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['media']['tmp_name'];
        $fileName = $_FILES['media']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploaded_emergencies/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0777, true);
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $media_path = $conn->real_escape_string($dest_path);
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO emergencies (parent_id, name, location, phone, description, media_path, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $name, $location, $phone, $description, $media_path, $latitude, $longitude);
    $stmt->execute();
    $stmt->close();

    header("Location: child_emergency.php");
    exit;
}

// Fetch emergencies for logged-in parent
$result = $conn->query("SELECT * FROM emergencies WHERE parent_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Emergency Assistance</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg-light: #e3f2fd;
    --bg-dark: #121212;
    --card-light: rgba(255,255,255,0.2);
    --card-dark: rgba(30,30,30,0.6);
    --text-light: #1e1e1e;
    --text-dark: #e5e5e5;
    --input-light: rgba(255,255,255,0.9);
    --input-dark: rgba(255,255,255,0.1);
    --primary-blue: #1565C0;
    --violet: #7B1FA2;
    --yellow: #FBC02D;
    --red: #E53935;
    --shadow: rgba(0,0,0,0.2);
    --radius: 25px;
    transition: all 0.4s;
}

body {
    margin:0; font-family:'Poppins', sans-serif; color: var(--text-light);
    display:flex; flex-direction:column; align-items:center; padding:40px 0;
    min-height:100vh; background: var(--bg-light);
}
body.dark { background: var(--bg-dark); color: var(--text-dark); }

/* Background Video */
#bg-video {
    position: fixed; top:0; left:0; width:100%; height:100%;
    object-fit:cover; z-index:-1; opacity:0.4;
}

/* Navbar */
.navbar {
    width: 90%; max-width:900px;
    background: var(--primary-blue);
    padding: 12px 20px;
    display:flex; justify-content:space-between; align-items:center;
    border-radius: var(--radius);
    box-shadow:0 4px 15px var(--shadow);
    margin-bottom:30px;
}
body.dark .navbar { background: var(--violet); }
.navbar span { font-weight:600; font-size:18px; color:#fff; }
.navbar ul { list-style:none; display:flex; gap:12px; align-items:center; margin:0; padding:0; }
.navbar ul li a, .navbar ul li button {
    color:#fff; text-decoration:none; font-weight:500; cursor:pointer; 
    border:none; padding:5px 10px; border-radius:50px; transition:0.3s; font-size:13px; background:none;
}
.navbar ul li a:hover, .navbar ul li button:hover { background: rgba(255,255,255,0.2); }

/* Dark mode toggle */
#darkModeToggle { border: 1px solid #fff; }
body.dark #darkModeToggle:hover { background: rgba(255,255,255,0.15); }

/* Form Container */
.container {
    width: 90%; max-width:700px;
    background: var(--card-light);
    backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
    border: 2px solid rgba(255,255,255,0.3);
    padding: 35px; border-radius: var(--radius);
    box-shadow: 0 10px 30px var(--shadow), 0 0 20px rgba(21,101,192,0.3);
    display:flex; flex-direction:column; align-items:center;
    transition: all 0.5s ease;
}
body.dark .container { background: var(--card-dark); border:2px solid rgba(255,255,255,0.2); box-shadow:0 10px 30px var(--shadow),0 0 25px rgba(123,31,162,0.4); }

h2 { margin-bottom: 20px; color: var(--primary-blue); font-size:24px; font-weight:600; text-shadow:0 0 5px rgba(21,101,192,0.6);}
body.dark h2 { color: var(--violet); text-shadow:0 0 8px rgba(123,31,162,0.8); }

form { display:flex; flex-direction:column; gap:18px; width:100%; position: relative; }

input, textarea {
    width:100%; padding:14px; border-radius:15px; border:2px solid rgba(255,255,255,0.3);
    font-size:15px; background: var(--input-light); color:#000; font-weight:500; transition: all 0.3s ease;
}
body.dark input, body.dark textarea { background: var(--input-dark); border:2px solid rgba(255,255,255,0.4); color:#fff; }
input:focus, textarea:focus {
    outline:none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 15px var(--primary-blue);
}
body.dark input:focus, body.dark textarea:focus {
    border-color: var(--violet);
    box-shadow: 0 0 15px var(--violet);
}

/* Buttons */
button {
    padding:10px 18px; border-radius:50px; font-size:14px; font-weight:500; cursor:pointer; color:#fff; transition:0.3s; border:none; margin-top:5px;
}
.location-btn { background: var(--red); }
.location-btn:hover { background:#b71c1c; transform: translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.3);}
.submit-btn { background: var(--primary-blue); }
.submit-btn:hover { background:#0d47a1; transform: translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.3);}
.help-btn { background: var(--yellow); color:#000; }
.help-btn:hover { background: #fdd835; transform: translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.3);}
.violet-btn { background: var(--violet); }
.violet-btn:hover { background: #6a1b9a; transform: translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.3);}

/* Help Section */
.help-section { display:flex; justify-content:center; gap:10px; margin-top:20px; flex-wrap:wrap; }
.help-box { padding:12px 18px; border-radius:50px; cursor:pointer; transition:0.3s; font-weight:500; text-align:center; box-shadow:0 4px 12px var(--shadow); }
.help-box:hover { transform: translateY(-3px); box-shadow: 0 6px 14px var(--shadow); }

/* Table */
table { width:100%; border-collapse: collapse; margin-top:30px; text-align:center; font-size:14px; }
th, td { padding:10px; border:1px solid #ddd; vertical-align: middle; }
th { background: var(--primary-blue); color:#fff; }
body.dark th { background: var(--violet); }
.media-preview img, .media-preview video { max-width:140px; max-height:100px; border-radius:8px; cursor:pointer; }

/* Responsive */
@media(max-width:768px){
    .container, .navbar { width:95%; }
    .help-section { flex-direction:column; align-items:center; }
}
</style>

</head>
<body id="body">

<!-- Background Video -->
<video id="bg-video" autoplay muted loop>
    <source src="emergency.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<!-- Navbar -->
<nav class="navbar">
    <span>Emergency Assistance</span>
    <ul>
        <li><a href="parent_dashboard.php">Dashboard</a></li>
        <li><a href="parent_logout.php">Logout</a></li>
        <li><button id="darkModeToggle">Dark Mode</button></li>
    </ul>
</nav>

<div class="container">
    <h2>Emergency Form</h2>
    <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
        <input type="text" name="name" placeholder="Your Name" required />
        <input type="text" name="location" placeholder="Your Address" required />
        <input type="tel" name="phone" placeholder="Your Phone Number" required />
        <textarea name="description" rows="4" placeholder="Describe your emergency" required></textarea>
        <input type="file" name="media" accept="image/*,video/*" />
        <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
        <button type="button" class="location-btn" onclick="getLocation()">Share Live Location</button>
        <button type="submit" class="submit-btn">Submit</button>
    </form>

    <div class="help-section">
        <div class="help-box help-btn" onclick="callHelpline()">📞 Call 999</div>
    </div>

    <h2>Your Emergencies</h2>
    <?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Location</th><th>Description</th><th>Media</th><th>Live Location</th><th>Time</th><th>Ambulance</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                <td class="media-preview">
                    <?php if ($row['media_path']): 
                        $ext = strtolower(pathinfo($row['media_path'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['mp4','mov','avi'])): ?>
                            <video controls>
                                <source src="<?= htmlspecialchars($row['media_path']) ?>" type="video/<?= $ext ?>" />
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($row['media_path']) ?>" alt="Media" />
                        <?php endif; ?>
                    <?php else: ?> N/A <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['latitude'] && $row['longitude']): ?>
                        <button class="location-btn" onclick="openMap(<?= $row['latitude'] ?>, <?= $row['longitude'] ?>)">View Location</button>
                    <?php else: ?> N/A <?php endif; ?>
                </td>
                <td><?= $row['created_at'] ?></td>
                <td><button class="location-btn" onclick="callAmbulance()">Helpline</button></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="text-align:center; margin-top:20px;">You have not submitted any emergencies yet.</p>
    <?php endif; ?>
</div>

<script>
const body = document.getElementById('body');
const darkModeToggle = document.getElementById('darkModeToggle');
if(sessionStorage.getItem('darkMode')==='true'){ body.classList.add('dark'); darkModeToggle.textContent='Light Mode'; }
darkModeToggle.addEventListener('click', ()=>{
    body.classList.toggle('dark');
    if(body.classList.contains('dark')){
        darkModeToggle.textContent='Light Mode';
        sessionStorage.setItem('darkMode','true');
    } else {
        darkModeToggle.textContent='Dark Mode';
        sessionStorage.setItem('darkMode','false');
    }
});

function getLocation() {
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(pos=>{
            document.getElementById('latitude').value=pos.coords.latitude;
            document.getElementById('longitude').value=pos.coords.longitude;
            alert("Location Shared: "+pos.coords.latitude+", "+pos.coords.longitude);
        });
    } else { alert("Geolocation not supported"); }
}
function openMap(lat, lon){ window.open(`https://www.google.com/maps?q=${lat},${lon}`,'_blank'); }
function callAmbulance(){ window.location.href="tel:+8801842542469"; }
function callHelpline(){ window.location.href="tel:999"; }
function validateForm(){ return true; }
</script>
</body>
</html>
