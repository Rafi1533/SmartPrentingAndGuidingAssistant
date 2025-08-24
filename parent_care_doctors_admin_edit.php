<?php
// parent_care_doctors_admin_edit.php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid ID.");
}
$id = (int)$_GET['id'];

// Fetch
$stmt = $conn->prepare("SELECT * FROM Parent_portal_doctors WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) { die("Doctor not found."); }

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_doctor'])) {
    $name        = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $details     = trim($_POST['details'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');

    if ($name === '' || $designation === '') { $errors[] = "Name and Designation are required."; }

    // Handle new photo (optional)
    $newPhoto = $doctor['photo'];
    if (!empty($_FILES['photo']['name'])) {
      $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
      if (!in_array($_FILES['photo']['type'], $allowed)) {
        $errors[] = "Photo must be JPG/PNG/WEBP.";
      } else {
        $dir = 'uploads/parent_care_doctors/';
        if (!is_dir($dir)) { mkdir($dir,0777,true); }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('doc_').'.'.$ext;
        $target = $dir.$fname;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
          $errors[] = "Failed to upload new photo.";
        } else {
          // remove old
          if ($doctor['photo'] && file_exists($doctor['photo'])) unlink($doctor['photo']);
          $newPhoto = $target;
        }
      }
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE Parent_portal_doctors SET name=?, designation=?, details=?, email=?, contact=?, photo=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $designation, $details, $email, $contact, $newPhoto, $id);
        if ($stmt->execute()) {
          $success = "Doctor updated successfully.";
          // refresh base info
          $doctor['name'] = $name; $doctor['designation'] = $designation; $doctor['details'] = $details;
          $doctor['email'] = $email; $doctor['contact'] = $contact; $doctor['photo'] = $newPhoto;
        } else {
          $errors[] = "DB error: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Doctor — Parent Care (Admin)</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root { --blue:#0a74da; --blue-dark:#0a5bb0; --bg:#f7fbff; --card:#fff; --border:#e3eef9; --text:#0a3d62; --shadow:rgba(10,116,218,.12); --muted:#6c7a89; }
  *{box-sizing:border-box}
  body{margin:0;font-family:'Poppins',system-ui; background:var(--bg); color:var(--text);}
  .navbar{background:var(--blue); color:#fff; padding:14px 24px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 8px 18px var(--shadow);}
  .navbar a{color:#fff;text-decoration:none;margin-left:16px;font-weight:600;padding:8px 12px;border-radius:8px;transition:.25s}
  .navbar a:hover{background:rgba(255,255,255,.12); transform:translateY(-1px);}
  .container{max-width:800px;margin:28px auto;padding:0 16px 50px;}
  .card{background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:0 10px 24px var(--shadow); padding:22px;}
  h1{margin:0 0 14px;font-size:1.4rem}
  label{display:block;margin:12px 0 6px;font-weight:600}
  input[type=text], input[type=email], textarea, input[type=file]{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px}
  textarea{min-height:110px}
  .doc-photo{width:84px;height:84px;border-radius:50%;object-fit:cover;border:1px solid var(--border);display:block;margin:8px 0}
  .alert{padding:12px;border-radius:10px;margin-bottom:12px;font-weight:600}
  .alert.error{background:#ffe3e6;color:#a3313d;border:1px solid #ffc9cf}
  .alert.success{background:#e8f6ee;color:#1e6b3f;border:1px solid #c8ead7}
  .actions{margin-top:16px;display:flex;gap:10px}
  .btn{padding:12px 16px;border-radius:10px;border:1px solid transparent;cursor:pointer;font-weight:700}
  .btn-primary{background:var(--blue);color:#fff}
  .btn-primary:hover{background:var(--blue-dark)}
  .btn-outline{border-color:var(--blue); color:var(--blue); background:#fff}
  .btn-outline:hover{background:rgba(10,116,218,.08)}
</style>
</head>
<body>

<nav class="navbar">
  <div>Edit Doctor</div>
  <div>
    <a href="parent_care_doctors_admin.php">Back</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="card">
    <h1><?php echo htmlspecialchars($doctor['name']); ?></h1>

    <?php if ($errors): ?><div class="alert error"><?php echo implode('<br>', $errors); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success"><?php echo $success; ?></div><?php endif; ?>

    <?php if ($doctor['photo'] && file_exists($doctor['photo'])): ?>
      <img class="doc-photo" src="<?php echo htmlspecialchars($doctor['photo']); ?>" alt="">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label>Name *</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required />

      <label>Designation *</label>
      <input type="text" name="designation" value="<?php echo htmlspecialchars($doctor['designation']); ?>" required />

      <label>Email</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" />

      <label>Contact</label>
      <input type="text" name="contact" value="<?php echo htmlspecialchars($doctor['contact']); ?>" />

      <label>Details</label>
      <textarea name="details"><?php echo htmlspecialchars($doctor['details']); ?></textarea>

      <label>Replace Photo (optional)</label>
      <input type="file" name="photo" accept="image/*" />

      <div class="actions">
        <button class="btn btn-primary" type="submit" name="update_doctor">Save Changes</button>
        <a class="btn btn-outline" href="parent_care_doctors_admin.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
