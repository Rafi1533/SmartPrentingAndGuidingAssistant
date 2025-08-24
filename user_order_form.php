<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Your cart is empty.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_number = trim($_POST['delivery_number'] ?? '');
    $delivery_location = trim($_POST['delivery_location'] ?? '');

    if (!$delivery_number) {
        $errors[] = "Delivery number is required.";
    }
    if (!$delivery_location) {
        $errors[] = "Delivery location is required.";
    }

    if (empty($errors)) {
        // Insert each order item separately (one row per teaching aid)
        foreach ($cart as $aid_id => $qty) {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, teaching_aid_id, quantity, delivery_number, delivery_location, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("iiiss", $_SESSION['user_id'], $aid_id, $qty, $delivery_number, $delivery_location);
            $stmt->execute();
        }
        unset($_SESSION['cart']);
        $success = "Order placed successfully!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Place Order</title>
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
    max-width: 600px;
    margin: 30px auto;
    padding: 0 20px 50px;
  }
  h1 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
  }
  form {
    background: white;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
  }
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
  }
  input[type=text] {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 2px solid #0a74da;
    font-size: 16px;
    transition: border-color 0.3s ease;
  }
  input[type=text]:focus {
    border-color: #005bb5;
    outline: none;
  }
  button {
    background: #0a74da;
    color: white;
    padding: 14px 20px;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    width: 100%;
    transition: background 0.3s ease;
  }
  button:hover {
    background: #005bb5;
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
    text-align: center;
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
  <h1>Place Your Order</h1>

  <?php if ($errors): ?>
    <div class="errors"><?php echo implode("<br>", $errors); ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
    <p style="text-align:center;"><a href="user_teaching_aid_store.php">Back to Store</a></p>
  <?php else: ?>
    <form method="POST" novalidate>
      <label for="delivery_number">Delivery Number</label>
      <input type="text" id="delivery_number" name="delivery_number" required placeholder="e.g. 017XXXXXXXX" />

      <label for="delivery_location">Delivery Location</label>
      <input type="text" id="delivery_location" name="delivery_location" required placeholder="Enter delivery address" />

      <button type="submit">Submit Order</button>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
