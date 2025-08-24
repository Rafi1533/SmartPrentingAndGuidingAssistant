<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Special Child Care</title>
    <?php
    session_start();
    include 'db.php';
   if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}

    $user_id = $_SESSION['user_id'];

    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $product_id = $_POST['product_id'];
        $quantity = 1;

        $sql_check = "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id";
        $result = $conn->query($sql_check);
        if ($result->num_rows > 0) {
            $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id=$user_id AND product_id=$product_id";
        } else {
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        }
        $conn->query($sql);
    }

    // Buy now
    if (isset($_POST['buy_now'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'] ?? 1;
        $phone_number = $_POST['phone_number'];
        $location = $_POST['location'];
        $sql_prod = "SELECT price, quantity FROM products WHERE id=$product_id";
        $prod = $conn->query($sql_prod)->fetch_assoc();
        if ($prod['quantity'] >= $quantity) {
            $total = $prod['price'] * $quantity;
            $sql = "INSERT INTO orders (user_id, product_id, quantity, total_amount, phone_number, location) VALUES ($user_id, $product_id, $quantity, $total, '$phone_number', '$location')";
            $conn->query($sql);
            $sql_update = "UPDATE products SET quantity = quantity - $quantity WHERE id=$product_id";
            $conn->query($sql_update);
            $sql_remove = "DELETE FROM cart WHERE user_id=$user_id AND product_id=$product_id";
            $conn->query($sql_remove);
        }
    }

    // Received
    if (isset($_POST['received'])) {
        $order_id = $_POST['order_id'];
        $sql = "UPDATE orders SET status='delivered' WHERE id=$order_id AND user_id=$user_id AND status='shipping'";
        $conn->query($sql);
    }

    $search_query = isset($_GET['search']) ? $_GET['search'] : '';
    $search_sql = $search_query ? " WHERE name LIKE '%$search_query%' OR details LIKE '%$search_query%'" : '';
    ?>
    <style>
        body {
    font-family: 'Poppins', sans-serif;
    background: #f0f8ff url('shop.png') no-repeat center center fixed;
    background-size: cover; /* ensures the image covers the entire background */
    color: #0a3d62;
    margin: 0;
    min-height: 100vh;
}
        .navbar {
            background-color: #9000ffff;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }
        .navbar:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
            font-weight: 500;
            transition: color 0.3s;
        }
        .navbar ul li a:hover {
            color: #ffd700;
        }
        .search-bar {
            margin: 20px auto;
            width: 50%;
            max-width: 600px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        .search-bar input:focus {
            border-color: #007bff;
        }
        .shop-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            padding: 20px 50px;
        }
        .product-card {
            background-color: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            max-height: 300px;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        }
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
        }
        .product-card h3 {
            margin: 8px 0 5px;
            font-size: 16px;
            color: #333;
        }
        .product-card p {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .product-card .buy-btn {
            background-color: #28a745;
            border: none;
            padding: 6px 12px;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 8px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .product-card .buy-btn:hover {
            background-color: #218838;
        }
        .out-of-stock {
            color: red;
            font-weight: bold;
            font-size: 12px;
        }
        .product-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .modal-content img {
            width: 100%;
            height: auto;
            border-radius: 6px;
        }
        .modal-content video {
            width: 100%;
            height: auto;
        }
        .modal-content p {
            max-height: 150px;
            overflow-y: auto;
        }
        .modal-content button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            transition: background 0.3s;
        }
        .modal-content button:hover {
            background-color: #0056b3;
        }
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 14px;
        }
        .cart-container, .history-container {
            padding: 30px;
            background-color: white;
            display: none;
            margin: 20px 50px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .cart-item, .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .received-btn {
            background-color: #ffc107;
            color: #333;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .received-btn:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

    

    <!-- Navbar -->
    <div class="navbar">
        <span>Special Child Care Shop</span>
        <ul>
            <li><a href="specialchild.php">Special Child Home</a></li>
            <li><a href="parent_logout.php">Logout</a></li>
            <li><a href="#" onclick="toggleSection('cart-container')">Cart (<?php 
                $sql_cart = "SELECT COUNT(*) as count FROM cart WHERE user_id=$user_id";
                echo $conn->query($sql_cart)->fetch_assoc()['count'];
            ?>)</a></li>
            <li><a href="#" onclick="toggleSection('history-container')">Purchase History</a></li>
        </ul>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo $search_query; ?>">
        </form>
    </div>

    <!-- Main Shop Section -->
    <div class="shop-container">
        <?php
        $sql = "SELECT * FROM products" . $search_sql;
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $stock = $row['quantity'] > 0 ? '' : '<span class="out-of-stock">Out of Stock</span>';
            echo "<div class='product-card' onclick=\"openProductModal('{$row['id']}')\">
                <img src='{$row['image']}' alt='Product Image'>
                <h3>{$row['name']}</h3>
                <p>" . substr($row['details'], 0, 30) . "...</p>
                <p>Tk {$row['price']}</p>
                $stock
                <button class='buy-btn' " . ($row['quantity'] == 0 ? 'disabled' : '') . ">Buy Now</button>
            </div>";
        }
        ?>
    </div>

    <!-- Product Details Modal -->
    <div class="product-modal" id="product-modal">
        <div class="modal-content">
            <img id="product-image" src="" alt="Product Image">
            <video id="product-video" controls style="display:none;"></video>
            <h3 id="product-title"></h3>
            <p id="product-description"></p>
            <p id="product-price"></p>
            <form method="POST">
                <input type="hidden" id="product-id" name="product_id">
                <input type="number" name="quantity" min="1" value="1" placeholder="Quantity">
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <textarea name="location" placeholder="Delivery Location" required></textarea>
                <button type="submit" name="buy_now">Buy Now (Cash on Delivery)</button>
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
        </div>
    </div>

    <!-- Cart Section -->
    <div class="cart-container" id="cart-container">
        <h2>Your Cart</h2>
        <?php
        $sql = "SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE user_id=$user_id";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<div class='cart-item'>
                <p>{$row['name']} - Tk {$row['price']} x {$row['quantity']}</p>
                <form method='POST'>
                    <input type='hidden' name='product_id' value='{$row['product_id']}'>
                    <input type='number' name='quantity' value='{$row['quantity']}' min='1'>
                    <input type='text' name='phone_number' placeholder='Phone Number' required>
                    <textarea name='location' placeholder='Delivery Location' required></textarea>
                    <button type='submit' name='buy_now' class='received-btn'>Buy</button>
                </form>
            </div>";
        }
        ?>
    </div>

    <!-- Purchase History Section -->
    <div class="history-container" id="history-container">
        <h2>Purchase History</h2>
        <?php
        $sql = "SELECT o.*, p.name FROM orders o JOIN products p ON o.product_id = p.id WHERE user_id=$user_id";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $received_btn = ($row['status'] == 'shipping') ? "<form method='POST'><input type='hidden' name='order_id' value='{$row['id']}'><button type='submit' name='received' class='received-btn'>Received</button></form>" : "";
            echo "<div class='history-item'>
                <p>{$row['name']} - Tk {$row['total_amount']} - Status: {$row['status']} - Date: {$row['order_date']}</p>
                $received_btn
            </div>";
        }
        ?>
    </div>

    <script>
        function openProductModal(productId) {
            fetch('get_product.php?id=' + productId)
                .then(response => response.json())
                .then(product => {
                    document.getElementById('product-title').innerText = product.name;
                    document.getElementById('product-description').innerText = product.details;
                    document.getElementById('product-image').src = product.image;
                    if (product.video) {
                        document.getElementById('product-video').src = product.video;
                        document.getElementById('product-video').style.display = 'block';
                    } else {
                        document.getElementById('product-video').style.display = 'none';
                    }
                    document.getElementById('product-price').innerText = 'Tk ' + product.price;
                    document.getElementById('product-id').value = product.id;
                    document.getElementById('product-modal').style.display = 'flex';
                });
        }

        window.onclick = function(event) {
            if (event.target === document.getElementById('product-modal')) {
                document.getElementById('product-modal').style.display = 'none';
            }
        }

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const otherSection = sectionId === 'cart-container' ? 'history-container' : 'cart-container';

            section.style.display = (section.style.display === "none" || section.style.display === "") ? "block" : "none";
            document.getElementById(otherSection).style.display = "none";
        }
    </script>

</body>
</html>