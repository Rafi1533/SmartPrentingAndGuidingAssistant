<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
include 'db.php';

$errors = [];
$edit_mode = false;
$edit_id = null;
$name = $price = $description = $unit = $unit_price = '';
$picture_path = $video_path = '';

// Add/Edit Teaching Aid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_aid') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $unit = trim($_POST['unit']);
    $unit_price = floatval($_POST['unit_price']);

    if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
        $edit_mode = true;
        $edit_id = intval($_POST['edit_id']);
    }

    if (empty($name) || !$price || !$unit_price) {
        $errors[] = "Name, price and unit price are required.";
    }

    // Handle uploads
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_img_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES['picture']['type'], $allowed_img_types)) {
                $errors[] = "Picture must be JPG or PNG.";
            } else {
                $upload_dir = 'uploads/teaching_aids/pictures/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $picture_path = $upload_dir . uniqid() . '_' . basename($_FILES['picture']['name']);
                move_uploaded_file($_FILES['picture']['tmp_name'], $picture_path);
            }
        } else {
            $errors[] = "Error uploading picture.";
        }
    }

    if (isset($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_vid_types = ['video/mp4', 'video/webm', 'video/ogg'];
        if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
            if (!in_array($_FILES['video']['type'], $allowed_vid_types)) {
                $errors[] = "Video must be mp4, webm, or ogg.";
            } else {
                $upload_dir = 'uploads/teaching_aids/videos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $video_path = $upload_dir . uniqid() . '_' . basename($_FILES['video']['name']);
                move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
            }
        } else {
            $errors[] = "Error uploading video.";
        }
    }

    if (empty($errors)) {
        if ($edit_mode) {
            $stmt = $conn->prepare("SELECT picture, video FROM teaching_aid_items WHERE id = ?");
            $stmt->bind_param("i", $edit_id);
            $stmt->execute();
            $stmt->bind_result($old_picture, $old_video);
            $stmt->fetch();
            $stmt->close();

            $final_picture = $picture_path ?: $old_picture;
            $final_video = $video_path ?: $old_video;

            $stmt = $conn->prepare("UPDATE teaching_aid_items SET name=?, price=?, description=?, unit=?, unit_price=?, picture=?, video=? WHERE id=?");
            $stmt->bind_param("sdssdssi", $name, $price, $description, $unit, $unit_price, $final_picture, $final_video, $edit_id);
            if ($stmt->execute()) {
                header("Location: admin_teaching_aid.php?msg=updated");
                exit;
            } else {
                $errors[] = "DB update error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO teaching_aid_items (name, price, description, unit, unit_price, picture, video) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssdss", $name, $price, $description, $unit, $unit_price, $picture_path, $video_path);
            if ($stmt->execute()) {
                header("Location: admin_teaching_aid.php?msg=added");
                exit;
            } else {
                $errors[] = "DB insert error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Delete Teaching Aid
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT picture, video FROM teaching_aid_items WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->bind_result($del_picture, $del_video);
    if ($stmt->fetch()) {
        if ($del_picture && file_exists($del_picture)) unlink($del_picture);
        if ($del_video && file_exists($del_video)) unlink($del_video);
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM teaching_aid_items WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        header("Location: admin_teaching_aid.php?msg=deleted");
        exit;
    }
    $stmt->close();
}

// Fetch for edit
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM teaching_aid_items WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $data = $res->fetch_assoc();
        $name = $data['name'];
        $price = $data['price'];
        $description = $data['description'];
        $unit = $data['unit'];
        $unit_price = $data['unit_price'];
        $picture_path = $data['picture'];
        $video_path = $data['video'];
        $edit_mode = true;
    }
    $stmt->close();
}

// Fetch all teaching aids for listing
$teaching_aids = $conn->query("SELECT * FROM teaching_aid_items ORDER BY created_at DESC");

// --- Order status update by admin ---

if (isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];

    // Only allow Processing or Shipping from admin
    if (in_array($new_status, ['Processing', 'Shipping'])) {
        $stmt = $conn->prepare("UPDATE teaching_aid_orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_teaching_aid.php?msg=order_updated");
        exit;
    }
}

// Fetch orders excluding Delivered (those should be hidden from admin)
$orders = $conn->query("SELECT o.*, t.name as aid_name FROM teaching_aid_orders o JOIN teaching_aid_items t ON o.teaching_aid_id = t.id WHERE o.status != 'Delivered' ORDER BY o.created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Teaching Aid Management & Orders</title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: 'Poppins', sans-serif;
    margin: 0; background: #e6f0ff;
    color: #0a3d62;
  }
  nav {
    background: #0a74da;
    padding: 15px 30px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    font-size: 16px;
    transition: color 0.3s ease;
  }
  nav a:hover {
    color: #a0cfff;
  }
  .container {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 20px 50px;
  }
  h1, h2 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
  }
  form {
    background: white;
    border-radius: 12px;
    padding: 25px 30px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    margin-bottom: 40px;
  }
  form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
  }
  form input[type=text],
  form input[type=number],
  form textarea,
  form input[type=file],
  form select {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 2px solid #0a74da;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
  }
  form input[type=text]:focus,
  form input[type=number]:focus,
  form textarea:focus,
  form input[type=file]:focus,
  form select:focus {
    border-color: #005bb5;
    outline: none;
  }
  form textarea {
    resize: vertical;
    min-height: 80px;
  }
  form button {
    background: #0a74da;
    color: white;
    font-weight: 700;
    font-size: 18px;
    padding: 14px 0;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s ease;
    width: 100%;
  }
  form button:hover {
    background: #005bb5;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 60px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
  }
  th, td {
    text-align: left;
    padding: 14px 15px;
    border-bottom: 1px solid #ddd;
  }
  th {
    background: #0a74da;
    color: white;
    font-weight: 700;
    font-size: 16px;
  }
  tr:hover {
    background-color: #d6e6ff;
  }
  .action-btn {
    background: #0a74da;
    border: none;
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    margin-right: 6px;
    transition: background 0.3s ease;
  }
  .action-btn:hover {
    background: #005bb5;
  }
  .delete-btn {
    background: #e74c3c;
  }
  .delete-btn:hover {
    background: #b93225;
  }
  .errors {
    background: #f8d7da;
    color: #842029;
    border-radius: 8px;
    padding: 14px 20px;
    margin-bottom: 20px;
    font-weight: 600;
    border: 1px solid #f5c2c7;
  }
  .success {
    background: #d1e7dd;
    color: #0f5132;
    border-radius: 8px;
    padding: 14px 20px;
    margin-bottom: 20px;
    font-weight: 600;
    border: 1px solid #badbcc;
  }
</style>
</head>
<body>

<nav>
  <div>Admin Panel - Teaching Aid Management</div>
  <div>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_logout.php">Logout</a>
  </div>
</nav>

<div class="container">

  <h1><?php echo $edit_mode ? "Edit Teaching Aid" : "Add New Teaching Aid"; ?></h1>

  <?php if (!empty($errors)): ?>
    <div class="errors"><?php echo implode("<br>", $errors); ?></div>
  <?php elseif (isset($_GET['msg'])): ?>
    <div class="success">
      <?php
        $msg = $_GET['msg'];
        if ($msg === 'added') echo "Teaching Aid added successfully.";
        elseif ($msg === 'updated') echo "Teaching Aid updated successfully.";
        elseif ($msg === 'deleted') echo "Teaching Aid deleted successfully.";
        elseif ($msg === 'order_updated') echo "Order status updated.";
      ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_aid" />
    <?php if ($edit_mode): ?>
      <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_id); ?>" />
    <?php endif; ?>

    <label for="name">Name *</label>
    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>" />

    <label for="price">Price (Taka) *</label>
    <input type="number" step="0.01" id="price" name="price" required value="<?php echo htmlspecialchars($price); ?>" />

    <label for="unit">Unit</label>
    <input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($unit); ?>" placeholder="e.g., piece, set" />

    <label for="unit_price">Unit Price (Taka) *</label>
    <input type="number" step="0.01" id="unit_price" name="unit_price" required value="<?php echo htmlspecialchars($unit_price); ?>" />

    <label for="description">Description</label>
    <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>

    <label for="picture">Picture (JPG, PNG) <?php if ($edit_mode && $picture_path): ?><br><small>Current: <a href="<?php echo htmlspecialchars($picture_path); ?>" target="_blank">View</a></small><?php endif; ?></label>
    <input type="file" id="picture" name="picture" accept="image/jpeg,image/png" />

    <label for="video">Video (mp4, webm, ogg) <?php if ($edit_mode && $video_path): ?><br><small>Current: <a href="<?php echo htmlspecialchars($video_path); ?>" target="_blank">View</a></small><?php endif; ?></label>
    <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/ogg" />

    <button type="submit"><?php echo $edit_mode ? "Update Teaching Aid" : "Add Teaching Aid"; ?></button>
  </form>

  <h2>All Teaching Aids</h2>
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Price (Taka)</th>
        <th>Unit</th>
        <th>Unit Price</th>
        <th>Picture</th>
        <th>Video</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($teaching_aids && $teaching_aids->num_rows > 0): ?>
        <?php while($row = $teaching_aids->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo number_format($row['price'],2); ?></td>
            <td><?php echo htmlspecialchars($row['unit']); ?></td>
            <td><?php echo number_format($row['unit_price'],2); ?></td>
            <td>
              <?php if ($row['picture'] && file_exists($row['picture'])): ?>
                <a href="<?php echo htmlspecialchars($row['picture']); ?>" target="_blank">View</a>
              <?php else: ?>
                N/A
              <?php endif; ?>
            </td>
            <td>
              <?php if ($row['video'] && file_exists($row['video'])): ?>
                <a href="<?php echo htmlspecialchars($row['video']); ?>" target="_blank">View</a>
              <?php else: ?>
                N/A
              <?php endif; ?>
            </td>
            <td>
              <a class="action-btn" href="?edit=<?php echo $row['id']; ?>">Edit</a>
              <a class="action-btn delete-btn" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this teaching aid?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No teaching aids found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <h2>Orders</h2>
  <table>
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Teaching Aid</th>
        <th>Quantity</th>
        <th>Delivery Number</th>
        <th>Delivery Location</th>
        <th>Status</th>
        <th>Placed At</th>
        <th>Update Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while($order = $orders->fetch_assoc()): ?>
          <tr>
            <td><?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['aid_name']); ?></td>
            <td><?php echo $order['quantity']; ?></td>
            <td><?php echo htmlspecialchars($order['delivery_number']); ?></td>
            <td><?php echo htmlspecialchars($order['delivery_location']); ?></td>
            <td><?php echo $order['status']; ?></td>
            <td><?php echo $order['created_at']; ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>" />
                <select name="new_status" required>
                  <option value="" disabled selected>Change Status</option>
                  <?php if($order['status'] == 'Pending'): ?>
                    <option value="Processing">Processing</option>
                    <option value="Shipping">Shipping</option>
                  <?php elseif($order['status'] == 'Processing'): ?>
                    <option value="Shipping">Shipping</option>
                  <?php elseif($order['status'] == 'Shipping'): ?>
                    <option value="Processing">Processing</option>
                  <?php endif; ?>
                </select>
                <button type="submit" name="update_order_status" class="action-btn">Update</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No orders found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

</div>

</body>
</html>
