<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit();
}

$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Prepared statement with LIKE for search
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM teaching_aid_items WHERE name LIKE ? ORDER BY created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("s", $likeSearch);
} else {
    $stmt = $conn->prepare("SELECT * FROM teaching_aid_items ORDER BY created_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Teaching Aid Store</title>
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
  form.search-form {
    margin-bottom: 25px;
    text-align: center;
  }
  form input[type="search"] {
    width: 280px;
    padding: 10px 14px;
    font-size: 16px;
    border-radius: 10px;
    border: 2px solid #0a74da;
    transition: border-color 0.3s ease;
  }
  form input[type="search"]:focus {
    border-color: #005bb5;
    outline: none;
  }
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(250px,1fr));
    gap: 20px;
  }
  .card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    padding: 20px;
    cursor: pointer;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .card:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  }
  .card img {
    max-width: 100%;
    height: 140px;
    object-fit: contain;
    border-radius: 12px;
    margin-bottom: 15px;
  }
  .card h3 {
    margin: 0 0 10px 0;
    font-weight: 700;
    color: #0a3d62;
  }
  .card p {
    margin: 0;
    font-weight: 600;
    color: #0071e3;
  }
</style>
</head>
<body>

<nav>
  <div>Teaching Aid Store</div>
  <div>
    <a href="parent_dashboard.php">Home</a>
    <a href="user_cart.php">Cart</a>
    <a href="user_order_status.php">My Orders</a>
    <a href="parent_logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <h1>Teaching Aids</h1>

  <form method="GET" class="search-form" role="search" aria-label="Search Teaching Aids">
    <input type="search" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>" />
  </form>

  <div class="grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="card" onclick="window.location.href='user_teaching_aid_detail.php?id=<?php echo $row['id']; ?>'">
        <?php if ($row['picture'] && file_exists($row['picture'])): ?>
          <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" loading="lazy" />
        <?php else: ?>
          <img src="placeholder.png" alt="No image available" loading="lazy" />
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
        <p><?php echo number_format($row['price'],2); ?> Taka</p>
      </div>
    <?php endwhile; ?>
  </div>
</div>

</body>
</html>
