<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid teaching aid.");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM teaching_aid_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Teaching aid not found.");
}

$item = $result->fetch_assoc();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1.";
    }

    if (empty($errors)) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $quantity;
        } else {
            $_SESSION['cart'][$id] = $quantity;
        }
        $success = "Added to cart!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo htmlspecialchars($item['name']); ?> - Details</title>
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
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px 50px;
  }
  h1 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
  }
  .content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
  }
  .media {
    flex: 1 1 280px;
  }
  .media img, .media video {
    max-width: 100%;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  .details {
    flex: 2 1 400px;
  }
  .details p {
    font-size: 16px;
    line-height: 1.5;
    margin-bottom: 18px;
  }
  form {
    margin-top: 20px;
  }
  form label {
    font-weight: 600;
    margin-right: 10px;
  }
  form input[type=number] {
    width: 80px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 2px solid #0a74da;
    font-size: 16px;
    transition: border-color 0.3s ease;
  }
  form input[type=number]:focus {
    border-color: #005bb5;
    outline: none;
  }
  form button {
    background: #0a74da;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 16px;
    margin-left: 15px;
    transition: background 0.3s ease;
  }
  form button:hover {
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
  <h1><?php echo htmlspecialchars($item['name']); ?></h1>

  <?php if (!empty($errors)): ?>
    <div class="errors"><?php echo implode("<br>", $errors); ?></div>
  <?php elseif ($success): ?>
    <div class="success"><?php echo $success; ?></div>
  <?php endif; ?>

  <div class="content">
    <div class="media">
      <?php if ($item['picture'] && file_exists($item['picture'])): ?>
        <img src="<?php echo htmlspecialchars($item['picture']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
      <?php endif; ?>
      <?php if ($item['video'] && file_exists($item['video'])): ?>
        <video controls>
          <source src="<?php echo htmlspecialchars($item['video']); ?>" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      <?php endif; ?>
    </div>
    <div class="details">
      <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
      <p><strong>Price:</strong> <?php echo number_format($item['price'],2); ?> Taka</p>
      <p><strong>Unit:</strong> <?php echo htmlspecialchars($item['unit']); ?></p>
      <p><strong>Unit Price:</strong> <?php echo number_format($item['unit_price'],2); ?> Taka</p>

      <form method="POST" novalidate>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" min="1" value="1" required />
        <button type="submit" name="add_to_cart">Add to Cart</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
