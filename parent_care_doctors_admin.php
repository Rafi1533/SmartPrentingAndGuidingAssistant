<?php
// parent_care_doctors_admin.php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin_login.php");
  exit;
}

include 'db.php';

$errors = [];
$success = '';

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_doctor'])) {
    $name        = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $details     = trim($_POST['details'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');

    if ($name === '' || $designation === '') {
        $errors[] = "Name and Designation are required.";
    }

    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
        if (!in_array($_FILES['photo']['type'], $allowed)) {
          $errors[] = "Photo must be JPG/PNG/WEBP.";
        } else {
          $dir = 'uploads/parent_care_doctors/';
          if (!is_dir($dir)) { mkdir($dir, 0777, true); }
          $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
          $fname = uniqid('doc_').'.'.$ext;
          $target = $dir.$fname;
          if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $errors[] = "Failed to upload photo.";
          } else {
            $photoPath = $target;
          }
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO Parent_portal_doctors (name, designation, details, email, contact, photo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $designation, $details, $email, $contact, $photoPath);
        if ($stmt->execute()) {
          $success = "Doctor added successfully.";
        } else {
          $errors[] = "DB error: ".$stmt->error;
          if ($photoPath && file_exists($photoPath)) unlink($photoPath);
        }
        $stmt->close();
    }
}

$doctors = $conn->query("SELECT * FROM Parent_portal_doctors ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Parent Care — Doctors (Admin)</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root {
    --blue: #0a74da;
    --blue-dark: #0659a5;
    --bg: #f7fbff;
    --card: #ffffff;
    --text: #0a3d62;
    --muted: #6c7a89;
    --border: #e3eef9;
    --shadow: rgba(10, 116, 218, .12);
    --hover-bg: #f0f5fb;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
  }

  .navbar {
    background: var(--blue);
    color: #fff;
    padding: 14px 24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow: 0 8px 18px var(--shadow);
  }
  .navbar .brand { font-weight:700; letter-spacing:.3px; }
  .navbar a {
    color:#fff; text-decoration:none; margin-left:16px; font-weight:600; padding:8px 12px;
    border-radius:8px; transition:.25s ease;
  }
  .navbar a:hover { background: rgba(255,255,255,.12); transform: translateY(-1px); }

  .container { max-width: 1200px; margin: 28px auto; padding: 0 16px 50px; }
  .grid {
    display:grid; grid-template-columns: 2fr 3fr; gap: 28px;
  }
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius:14px;
    padding: 24px;
    box-shadow: 0 8px 20px var(--shadow);
    transition: transform .2s, box-shadow .2s;
    border-color: blue;
  }
  .card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px var(--shadow); }
  h1 { margin:0 0 20px; font-size: 1.8rem; }
  h2 { margin:0 0 16px; font-size: 1.3rem; color: var(--muted); }

  label { display:block; font-weight:600; margin:12px 0 6px; }
  input[type=text], input[type=email], textarea, input[type=file] {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #0aa3dabd; /* blue border */
    border-radius: 10px;
    background: #fff;
    outline: none;
    transition: box-shadow .2s ease, border-color .2s ease;
    font-size: 1rem;
  }
  textarea { min-height: 120px; resize: vertical; }
  input:focus, textarea:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 4px rgba(10,116,218, .12);
  }
  .actions { margin-top:20px; display:flex; gap:10px; }
  .btn {
    appearance:none; border:1px solid transparent; padding:12px 16px; border-radius:10px; font-weight:700; cursor:pointer;
    transition:.25s ease; text-decoration:none; display:inline-block; font-size:1rem;
  }
  .btn-primary { background: var(--blue); color:#fff; }
  .btn-primary:hover { background: var(--blue-dark); transform: translateY(-1px); }
  .btn-outline {
    border:1px solid var(--blue); color: var(--blue); background:#fff;
  }
  .btn-outline:hover { background: var(--hover-bg); }

  .alert { padding:14px 16px; border-radius:10px; margin-bottom:16px; font-weight:600; }
  .alert.error { background:#ffe3e6; color:#a3313d; border:1px solid #ffc9cf; }
  .alert.success { background:#e8f6ee; color:#1e6b3f; border:1px solid #c8ead7; }

  table { width:100%; border-collapse: collapse; }
  th, td { padding:12px 10px; border-bottom:1px solid var(--border); text-align:left; font-size:.95rem; }
  th { color: var(--muted); font-weight:700; }
  tr:hover { background: var(--hover-bg); }
  .doctor-photo { width:50px; height:50px; border-radius:50%; object-fit:cover; border:1px solid var(--border); }
  .row-actions a, .row-actions form button {
    font-size:.95rem; margin-right:8px; color: var(--blue); text-decoration:none; border:none; background:none; cursor:pointer;
  }
  .row-actions a:hover, .row-actions form button:hover { text-decoration: underline; }
  .row-actions form { display:inline; }

  /* Border sections titles */
  .card h2 {
    border-bottom: 1px solid var(--border);
    padding-bottom: 8px;
    margin-bottom:16px;
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="brand">Parent Care — Doctors (Admin)</div>
  <div>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="parent_care_doctors_admin.php">Doctors</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <h1>Manage Doctors</h1>
  <div class="grid">
    <div class="card">
      <h2>Add New Doctor</h2>

      <?php if ($errors): ?>
        <div class="alert error"><?php echo implode('<br>', $errors); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" novalidate>
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required />

        <label for="designation">Designation *</label>
        <input type="text" id="designation" name="designation" required />

        <label for="email">Email</label>
        <input type="email" id="email" name="email" />

        <label for="contact">Contact</label>
        <input type="text" id="contact" name="contact" />

        <label for="details">Details</label>
        <textarea id="details" name="details" placeholder="Short bio, expertise, qualifications..."></textarea>

        <label for="photo">Profile Picture (JPG/PNG/WEBP)</label>
        <input type="file" id="photo" name="photo" accept="image/*" />

        <div class="actions">
          <button class="btn btn-primary" type="submit" name="create_doctor">Add Doctor</button>
          <button class="btn btn-outline" type="reset">Reset</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>All Doctors</h2>
      <div style="overflow:auto;">
        <table>
          <thead>
            <tr>
              <th>Photo</th>
              <th>Name</th>
              <th>Designation</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($d = $doctors->fetch_assoc()): ?>
            <tr>
              <td><?php if ($d['photo'] && file_exists($d['photo'])): ?>
                  <img class="doctor-photo" src="<?php echo htmlspecialchars($d['photo']); ?>" alt="">
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($d['name']); ?></td>
              <td><?php echo htmlspecialchars($d['designation']); ?></td>
              <td><?php echo htmlspecialchars($d['email']); ?></td>
              <td><?php echo htmlspecialchars($d['contact']); ?></td>
              <td class="row-actions">
                <a href="parent_care_doctors_admin_edit.php?id=<?php echo $d['id']; ?>">Edit</a>
                <form method="POST" action="parent_care_doctors_admin_delete.php" onsubmit="return confirm('Delete this doctor?');">
                  <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                  <button type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>
