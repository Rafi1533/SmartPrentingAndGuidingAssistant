<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

$errors = [];
$success = '';

// Update quantities or remove items on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = intval($id);
            $qty = intval($qty);
            if ($qty < 1) {
                unset($cart[$id]);
            } else {
                $cart[$id] = $qty;
            }
        }
        $_SESSION['cart'] = $cart;
        $success = "Cart updated.";
    } elseif (isset($_POST['checkout'])) {
        if (empty($cart)) {
            $errors[] = "Cart is empty.";
        } else {
            header("Location: user_order_form.php");
            exit();
        }
    }
}

if (!empty($cart)) {
    $ids = implode(',', array_keys($cart));
    $sql = "SELECT * FROM teaching_aid_items WHERE id IN ($ids)";
    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[$row['id']] = $row;
    }
} else {
    $items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cart - Teaching Aid Store</title>
<style>
    body {
    font-family: 'Poppins', sans-serif;
    background: #f0f8ff url('shop.png') no-repeat center center fixed;
    background-size: cover; /* ensures the image covers the entire background */
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
    margin-bottom: 20px;
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
  input[type="number"] {
    width: 60px;
    padding: 6px;
    border-radius: 6px;
    border: 2px solid #0a74da;
    font-size: 16px;
  }
  input[type="number"]:focus {
    border-color: #005bb5;
    outline: none;
  }
  button {
    background: #0a74da;
    color: white;
    padding: 10px 20px;
    font-weight: 700;
    font-size: 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  button:hover {
    background: #005bb5;
  }
  .message {
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
  }
  .error {
    color: #842029;
  }
  .success {
    color: #0f5132;
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
  <h1>Your Cart</h1>

  <?php if ($errors): ?>
    <div class="message error"><?php echo implode("<br>", $errors); ?></div>
  <?php elseif ($success): ?>
    <div class="message success"><?php echo $success; ?></div>
  <?php endif; ?>

  <?php if (empty($cart)): ?>
    <p>Your cart is empty. <a href="user_teaching_aid_store.php">Go to Store</a></p>
  <?php else: ?>
    <form method="POST">
      <table>
        <thead>
          <tr>
            <th>Teaching Aid</th>
            <th>Price (Taka)</th>
            <th>Quantity</th>
            <th>Total (Taka)</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $grandTotal = 0;
            foreach ($cart as $id => $qty):
                $item = $items[$id];
                $total = $item['price'] * $qty;
                $grandTotal += $total;
          ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td><?php echo number_format($item['price'], 2); ?></td>
              <td>
                <input type="number" name="quantities[<?php echo $id; ?>]" min="0" value="<?php echo $qty; ?>" />
              </td>
              <td><?php echo number_format($total, 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" style="text-align:right;">Grand Total:</th>
            <th><?php echo number_format($grandTotal, 2); ?> Taka</th>
          </tr>
        </tfoot>
      </table>

      <div style="text-align:center;">
        <button type="submit" name="update_cart">Update Cart</button>
        <button type="submit" name="checkout">Proceed to Checkout</button>
      </div>
    </form>
  <?php endif; ?>
</div>

</body>
</html>
