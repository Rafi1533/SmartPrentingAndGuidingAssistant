<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT o.*, t.name AS teaching_aid_name FROM orders o JOIN teaching_aid_items t ON o.teaching_aid_id = t.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Orders - Teaching Aid Store</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: #f0f8ff;
    color: #0a3d62;
    margin: 0;
    min-height: 100vh;
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
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px 50px;
  }
  h1 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: center;
  }
  th {
    background-color: #0a74da;
    color: white;
  }
  .status {
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    color: white;
    display: inline-block;
  }
  .status.Pending {
    background-color: #f0ad4e;
  }
  .status.Processing {
    background-color: #5bc0de;
  }
  .status.Shipping {
    background-color: #0275d8;
  }
  .status.Delivered {
    background-color: #5cb85c;
  }
</style>
</head>
<body>

<nav>
  <div>Teaching Aid Store</div>
  <div>
    <a href="user_teaching_aid_store.php">Store</a>
    <a href="user_cart.php">Cart</a>
    <a href="user_order_status.php">My Orders</a>
    <a href="parent_logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <h1>My Orders</h1>

  <?php if ($result->num_rows === 0): ?>
    <p>You have no orders yet. <a href="user_teaching_aid_store.php">Order now</a></p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Teaching Aid</th>
          <th>Quantity</th>
          <th>Delivery Number</th>
          <th>Delivery Location</th>
          <th>Status</th>
          <th>Order Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['teaching_aid_name']); ?></td>
            <td><?php echo intval($row['quantity']); ?></td>
            <td><?php echo htmlspecialchars($row['delivery_number']); ?></td>
            <td><?php echo htmlspecialchars($row['delivery_location']); ?></td>
            <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
            <td><?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
