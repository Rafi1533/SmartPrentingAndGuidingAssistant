<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php'); exit;
}
function esc($s){ return htmlspecialchars($s); }
$msg = '';

// Handle admin forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add autism type
    if (isset($_POST['add_type'])) {
        $name = trim($_POST['name']); $desc = trim($_POST['description']);
        $stmt = $conn->prepare("INSERT INTO therapy_autism_types (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc); $stmt->execute(); $stmt->close();
        $msg = "Autism type added.";
    }

    // Edit autism type
    if (isset($_POST['edit_type'])) {
        $id = intval($_POST['type_id']);
        $name = trim($_POST['name']);
        $desc = trim($_POST['description']);
        $stmt = $conn->prepare("UPDATE therapy_autism_types SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        $stmt->execute(); $stmt->close();
        $msg = "Autism type updated.";
    }

    // Delete autism type
    if (isset($_POST['delete_type'])) {
        $id = intval($_POST['type_id']);
        $stmt = $conn->prepare("DELETE FROM therapy_autism_types WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute(); $stmt->close();
        $msg = "Autism type deleted.";
    }

    // Add tutorial
    if (isset($_POST['add_tutorial'])) {
        $type = intval($_POST['autism_type_id']);
        $title = trim($_POST['title']);
        $details = trim($_POST['details']);
        $video_url = trim($_POST['video_url']);
        $stmt = $conn->prepare("INSERT INTO therapy_tutorials (autism_type_id, title, details, video_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $type, $title, $details, $video_url); $stmt->execute(); $stmt->close();
        $msg = "Tutorial added.";
    }

    // Edit tutorial
    if (isset($_POST['edit_tutorial'])) {
        $id = intval($_POST['tutorial_id']);
        $type = intval($_POST['autism_type_id']);
        $title = trim($_POST['title']);
        $details = trim($_POST['details']);
        $video_url = trim($_POST['video_url']);
        $stmt = $conn->prepare("UPDATE therapy_tutorials SET autism_type_id=?, title=?, details=?, video_url=? WHERE id=?");
        $stmt->bind_param("isssi", $type, $title, $details, $video_url, $id);
        $stmt->execute(); $stmt->close();
        $msg = "Tutorial updated.";
    }

    // Delete tutorial
    if (isset($_POST['delete_tutorial'])) {
        $id = intval($_POST['tutorial_id']);
        $stmt = $conn->prepare("DELETE FROM therapy_tutorials WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute(); $stmt->close();
        $msg = "Tutorial deleted.";
    }

    // Assign session to a request
    if (isset($_POST['assign_session'])) {
        $request_id = intval($_POST['request_id']);
        $user_id = intval($_POST['user_id']);
        $autism_type_id = intval($_POST['autism_type_id']);
        $doctor_name = trim($_POST['doctor_name']);
        $start_dt = trim($_POST['start_datetime']);
        $end_dt = trim($_POST['end_datetime']);
        $video_link = trim($_POST['video_call_link']);
        $bkash = trim($_POST['bkash_number']);

        $stmt = $conn->prepare("INSERT INTO therapy_sessions (request_id, user_id, autism_type_id, doctor_name, start_datetime, end_datetime, video_call_link, bkash_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssss", $request_id, $user_id, $autism_type_id, $doctor_name, $start_dt, $end_dt, $video_link, $bkash);
        if ($stmt->execute()) {
            $stmt->close();
            $u = $conn->prepare("UPDATE therapy_requests SET status='Assigned' WHERE id = ?");
            $u->bind_param("i", $request_id); $u->execute(); $u->close();
            $msg = "Session assigned to request #$request_id.";
        } else {
            $msg = "DB error: " . $conn->error;
        }
    }

    // Mark payment received
    if (isset($_POST['mark_received'])) {
        $payment_id = intval($_POST['payment_id']);
        $stmt = $conn->prepare("UPDATE therapy_payments SET status='Received', received_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $payment_id); $stmt->execute(); $stmt->close();
        $msg = "Payment marked received.";
    }

    // Reject payment
    if (isset($_POST['reject_payment'])) {
        $payment_id = intval($_POST['payment_id']);
        $stmt = $conn->prepare("UPDATE therapy_payments SET status='Rejected' WHERE id = ?");
        $stmt->bind_param("i", $payment_id); $stmt->execute(); $stmt->close();
        $msg = "Payment rejected.";
    }
}

// Fetch lists
$types = $conn->query("SELECT * FROM therapy_autism_types ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$tutorials = $conn->query("SELECT tt.*, at.name AS autism_name FROM therapy_tutorials tt JOIN therapy_autism_types at ON tt.autism_type_id = at.id ORDER BY tt.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$requests = $conn->query("SELECT r.*, u.first_name, u.last_name, at.name AS autism_name FROM therapy_requests r JOIN users u ON r.user_id = u.id JOIN therapy_autism_types at ON r.autism_type_id = at.id ORDER BY r.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$sessions = $conn->query("SELECT s.*, u.first_name, u.last_name, at.name AS autism_name FROM therapy_sessions s JOIN users u ON s.user_id = u.id JOIN therapy_autism_types at ON s.autism_type_id = at.id ORDER BY s.start_datetime DESC")->fetch_all(MYSQLI_ASSOC);
$payments = $conn->query("SELECT p.*, u.first_name, u.last_name, s.start_datetime FROM therapy_payments p JOIN users u ON p.user_id = u.id JOIN therapy_sessions s ON p.session_id = s.id ORDER BY p.uploaded_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin — Therapy Panel</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{--blue:#1565C0;--muted:#6b7280}
body{font-family:Inter,Segoe UI,Roboto,Arial;margin:0;background:#f5f8fb;color:#0b1721}
.nav{background:linear-gradient(90deg,var(--blue),#2b6fb3);color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1100px;margin:18px auto;padding:16px}
.card{background:#fff;border-radius:10px;padding:16px;margin-bottom:16px;box-shadow:0 6px 18px rgba(16,24,40,.06)}
h2{color:var(--blue);margin:0 0 10px}
label{display:block;margin-top:8px;color:var(--muted)}
input,textarea,select{width:100%;padding:10px;border:1px solid #e6edf3;border-radius:8px;margin-top:6px;box-sizing:border-box; border-color:rgba(16, 24, 40, 0.43);}
button{background:var(--blue);color:#fff;padding:10px 12px;border:none;border-radius:8px;cursor:pointer}
button.edit-btn, button.delete-btn {
  background: transparent; 
  color: var(--blue); 
  border: 1px solid var(--blue); 
  padding: 4px 8px; 
  border-radius: 6px; 
  cursor: pointer; 
  margin-right: 6px;
  font-size: 13px;
}
button.edit-btn:hover, button.delete-btn:hover {
  background: var(--blue);
  color: #fff;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 12px;
  border: 1px solid #cbd5e1;
}
th, td {
  padding: 8px;
  border: 1px solid #cbd5e1;
  text-align: left;
}
th {
  background: #f8fbff;
  color: var(--blue);
}
.small {
  font-size: 13px;
  color: var(--muted);
}
.details-form {
  margin-top: 8px;
  padding: 10px;
  background: #fafcff;
  border-radius: 8px;
}
/* Modal styles for edit forms */
.modal {
  display: none; 
  position: fixed; 
  z-index: 9999; 
  left: 0; top: 0; width: 100%; height: 100%; 
  overflow: auto; 
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 480px;
  box-shadow: 0 0 10px rgba(0,0,0,0.25);
  position: relative;
}
.close-btn {
  position: absolute;
  right: 12px;
  top: 12px;
  font-size: 20px;
  font-weight: bold;
  border: none;
  background: none;
  cursor: pointer;
  color: var(--blue);
}
.navbar {
            background: linear-gradient(90deg, #ff6b6b, #6a11cb, #2575fc);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .navbar ul li {
            margin: 0 15px;
        }
        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .navbar ul li a:hover {
            color: #f8c471;
        }
</style>
</head>
<body>

<div class="navbar">
        <span>Admin Therapy</span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_counseling.php">Counseling</a></li>
            <li><a href="admin_group_counseling.php">Group Counseling</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </div>

<div class="container">
<?php if($msg): ?>
  <div class="card"><strong><?= esc($msg) ?></strong></div>
<?php endif; ?>

<!-- Add Autism Type Form -->
<div class="card">
  <h2>Add Autism Type</h2>
  <form method="post">
    <label>Name</label><input name="name" required>
    <label>Description</label><textarea name="description" rows="3"></textarea>
    <button name="add_type" type="submit">Add Type</button>
  </form>
</div>

<!-- Uploaded Autism Types Table -->
<div class="card">
  <h2>Uploaded Autism Types</h2>
  <?php if(count($types) === 0): ?>
    <p class="small">No autism types uploaded yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($types as $t): ?>
          <tr>
            <td><?= $t['id'] ?></td>
            <td><?= esc($t['name']) ?></td>
            <td><?= esc($t['description']) ?></td>
            <td>
              <button class="edit-btn" onclick="openEditTypeModal(<?= $t['id'] ?>, '<?= esc(addslashes($t['name'])) ?>', '<?= esc(addslashes($t['description'])) ?>')">Edit</button>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this autism type?');">
                <input type="hidden" name="type_id" value="<?= $t['id'] ?>">
                <button class="delete-btn" name="delete_type" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Add Therapy Tutorial Form -->
<div class="card">
  <h2>Add Therapy Tutorial</h2>
  <form method="post">
    <label>Autism Type</label>
    <select name="autism_type_id" required>
      <option value="">Select...</option>
      <?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?>
    </select>
    <label>Title</label><input name="title" required>
    <label>Details</label><textarea name="details" rows="4"></textarea>
    <label>Video URL (YouTube)</label><input name="video_url" required>
    <button name="add_tutorial" type="submit" style="margin:20px;">Add Tutorial</button>
  </form>
</div>

<!-- Uploaded Therapy Tutorials Table -->
<div class="card">
  <h2>Uploaded Therapy Tutorials</h2>
  <?php if(count($tutorials) === 0): ?>
    <p class="small">No tutorials uploaded yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Title</th>
          <th>Details</th>
          <th>Video URL</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($tutorials as $tut): ?>
          <tr>
            <td><?= $tut['id'] ?></td>
            <td><?= esc($tut['autism_name']) ?></td>
            <td><?= esc($tut['title']) ?></td>
            <td><?= esc($tut['details']) ?></td>
            <td><a href="<?= esc($tut['video_url']) ?>" target="_blank">View Video</a></td>
            <td>
              <button class="edit-btn" onclick="openEditTutorialModal(<?= $tut['id'] ?>, <?= $tut['autism_type_id'] ?>, '<?= esc(addslashes($tut['title'])) ?>', '<?= esc(addslashes($tut['details'])) ?>', '<?= esc(addslashes($tut['video_url'])) ?>')">Edit</button>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this tutorial?');">
                <input type="hidden" name="tutorial_id" value="<?= $tut['id'] ?>">
                <button class="delete-btn" name="delete_tutorial" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Session Requests -->
<div class="card">
  <h2>Session Requests</h2>
  <table>
    <thead><tr><th>ID</th><th>User</th><th>Type</th><th>Title</th><th>When</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach($requests as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= esc($r['first_name'].' '.$r['last_name']) ?></td>
          <td><?= esc($r['autism_name']) ?></td>
          <td><?= esc($r['title']) ?></td>
          <td class="small"><?= $r['created_at'] ?></td>
          <td>
            <?php if($r['status']==='Pending'): ?>
              <details>
                <summary><button style="background:#fff;color:var(--blue);border:1px solid #e6edf3;padding:8px;border-radius:6px;cursor:pointer">Assign</button></summary>
                <div class="details-form">
                  <form method="post">
                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $r['user_id'] ?>">
                    <input type="hidden" name="autism_type_id" value="<?= $r['autism_type_id'] ?>">
                    <label>Doctor Name</label><input name="doctor_name" required>
                    <label>Start</label><input name="start_datetime" type="datetime-local" required>
                    <label>End</label><input name="end_datetime" type="datetime-local" required>
                    <label>Video Call Link</label><input name="video_call_link">
                    <label>bKash Number</label><input name="bkash_number" required>
                    <button name="assign_session" type="submit">Assign Session</button>
                  </form>
                </div>
              </details>
            <?php else: ?>
              <span class="small"><?= esc($r['status']) ?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Scheduled Sessions -->
<div class="card">
  <h2>Scheduled Sessions</h2>
  <table>
    <thead><tr><th>ID</th><th>User</th><th>Type</th><th>Doctor</th><th>Start</th><th>End</th><th>Call</th></tr></thead>
    <tbody>
      <?php foreach($sessions as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= esc($s['first_name'].' '.$s['last_name']) ?></td>
          <td><?= esc($s['autism_name']) ?></td>
          <td><?= esc($s['doctor_name']) ?></td>
          <td><?= esc($s['start_datetime']) ?></td>
          <td><?= esc($s['end_datetime']) ?></td>
          <td><?php if($s['video_call_link']): ?><a href="<?= esc($s['video_call_link']) ?>" target="_blank">Link</a><?php else: echo 'N/A'; endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Payments -->
<div class="card">
  <h2>Payments</h2>
  <table>
    <thead><tr><th>ID</th><th>User</th><th>Session Start</th><th>TX ID</th><th>Screenshot</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach($payments as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= esc($p['first_name'].' '.$p['last_name']) ?></td>
          <td class="small"><?= esc($p['start_datetime']) ?></td>
          <td><?= esc($p['tx_id']) ?></td>
          <td><?= $p['tx_screenshot_path'] ? '<a href="'.esc($p['tx_screenshot_path']).'" target="_blank">View</a>' : 'N/A' ?></td>
          <td><?= esc($p['amount']) ?></td>
          <td><?= esc($p['status']) ?></td>
          <td>
            <?php if($p['status'] === 'Pending'): ?>
              <form method="post" style="display:inline"><input type="hidden" name="payment_id" value="<?= $p['id'] ?>"><button name="mark_received" type="submit">Mark Received</button></form>
              <form method="post" style="display:inline"><input type="hidden" name="payment_id" value="<?= $p['id'] ?>"><button name="reject_payment" type="submit">Reject</button></form>
            <?php else: ?>
              <span class="small"><?= esc($p['status']) ?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</div>

<!-- Edit Autism Type Modal -->
<div id="editTypeModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="editTypeModalTitle">
  <div class="modal-content">
    <button class="close-btn" onclick="closeEditTypeModal()" aria-label="Close">&times;</button>
    <h3 id="editTypeModalTitle">Edit Autism Type</h3>
    <form method="post" id="editTypeForm">
      <input type="hidden" name="type_id" id="edit_type_id">
      <label>Name</label>
      <input name="name" id="edit_type_name" required>
      <label>Description</label>
      <textarea name="description" id="edit_type_description" rows="3"></textarea>
      <button name="edit_type" type="submit">Save Changes</button>
    </form>
  </div>
</div>

<!-- Edit Tutorial Modal -->
<div id="editTutorialModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="editTutorialModalTitle">
  <div class="modal-content">
    <button class="close-btn" onclick="closeEditTutorialModal()" aria-label="Close">&times;</button>
    <h3 id="editTutorialModalTitle">Edit Therapy Tutorial</h3>
    <form method="post" id="editTutorialForm">
      <input type="hidden" name="tutorial_id" id="edit_tutorial_id">
      <label>Autism Type</label>
      <select name="autism_type_id" id="edit_tutorial_autism_type_id" required>
        <option value="">Select...</option>
        <?php foreach($types as $t): ?>
          <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Title</label>
      <input name="title" id="edit_tutorial_title" required>
      <label>Details</label>
      <textarea name="details" id="edit_tutorial_details" rows="4"></textarea>
      <label>Video URL (YouTube)</label>
      <input name="video_url" id="edit_tutorial_video_url" required>
      <button name="edit_tutorial" type="submit">Save Changes</button>
    </form>
  </div>
</div>

<script>
function openEditTypeModal(id, name, description) {
  document.getElementById('edit_type_id').value = id;
  document.getElementById('edit_type_name').value = name;
  document.getElementById('edit_type_description').value = description;
  document.getElementById('editTypeModal').style.display = 'block';
  document.getElementById('editTypeModal').setAttribute('aria-hidden', 'false');
}
function closeEditTypeModal() {
  document.getElementById('editTypeModal').style.display = 'none';
  document.getElementById('editTypeModal').setAttribute('aria-hidden', 'true');
}

function openEditTutorialModal(id, autism_type_id, title, details, video_url) {
  document.getElementById('edit_tutorial_id').value = id;
  document.getElementById('edit_tutorial_autism_type_id').value = autism_type_id;
  document.getElementById('edit_tutorial_title').value = title;
  document.getElementById('edit_tutorial_details').value = details;
  document.getElementById('edit_tutorial_video_url').value = video_url;
  document.getElementById('editTutorialModal').style.display = 'block';
  document.getElementById('editTutorialModal').setAttribute('aria-hidden', 'false');
}
function closeEditTutorialModal() {
  document.getElementById('editTutorialModal').style.display = 'none';
  document.getElementById('editTutorialModal').setAttribute('aria-hidden', 'true');
}

// Close modals on outside click
window.onclick = function(event) {
  let modalType = document.getElementById('editTypeModal');
  let modalTutorial = document.getElementById('editTutorialModal');
  if (event.target == modalType) {
    closeEditTypeModal();
  }
  if (event.target == modalTutorial) {
    closeEditTutorialModal();
  }
}
</script>

</body>
</html>
