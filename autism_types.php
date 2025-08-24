<?php
include 'db.php';
$autism_types = $conn->query("SELECT * FROM autism_types")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Autism Types</title>
<style>
  body { font-family: Poppins, sans-serif; background:#f9f9f9; padding:20px;}
  .container {max-width: 900px; margin: auto; background:white; padding: 30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
  h1 {color:#673AB7;}
  .type-card {border-bottom: 1px solid #ddd; padding: 15px 0;}
  .type-card:last-child {border:none;}
  h2 {margin-bottom: 5px; color:#333;}
  p {color:#555; font-size: 1.1rem;}
</style>
</head>
<body>
  <div class="container">
    <h1>Autism Types</h1>
    <?php foreach($autism_types as $type): ?>
      <div class="type-card">
        <h2><?= htmlspecialchars($type['name']) ?></h2>
        <p><?= nl2br(htmlspecialchars($type['description'])) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
