<?php
// get_product.php (for AJAX)
include 'db.php';
$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id=$id";
$result = $conn->query($sql)->fetch_assoc();
echo json_encode($result);
?>