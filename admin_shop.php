<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Management</title>
    <?php
    session_start();
    include 'db.php';
    if (!isset($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit;
    }

    // Handle add product
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $details = $_POST['details'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $image = '';
        $video = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "Uploads/";
            $image = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $target_dir = "Uploads/";
            $video = $target_dir . basename($_FILES["video"]["name"]);
            move_uploaded_file($_FILES["video"]["tmp_name"], $video);
        }

        $sql = "INSERT INTO products (name, details, image, video, price, quantity) VALUES ('$name', '$details', '$image', '$video', $price, $quantity)";
        $conn->query($sql);
    }

    // Handle update product
    if (isset($_POST['update_product'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $details = $_POST['details'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $image = $_POST['existing_image'];
        $video = $_POST['existing_video'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "Uploads/";
            $image = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $target_dir = "Uploads/";
            $video = $target_dir . basename($_FILES["video"]["name"]);
            move_uploaded_file($_FILES["video"]["tmp_name"], $video);
        }

        $sql = "UPDATE products SET name='$name', details='$details', image='$image', video='$video', price=$price, quantity=$quantity WHERE id=$id";
        $conn->query($sql);
    }

    // Handle delete
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM products WHERE id=$id";
        $conn->query($sql);
    }

    // Handle update status (only pending or shipping)
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        if ($status == 'pending' || $status == 'shipping') {
            $sql = "UPDATE orders SET status='$status' WHERE id=$order_id";
            $conn->query($sql);
        }
    }
    ?>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
            text-align: center;
        }
        .navbar {
            background: #007bff;
            color: white;
            padding: 12px;
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
            margin: 0 12px;
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
        .container {
            width: 85%;
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        h2, h3 {
            color: #007bff;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .form-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        .form-section:hover {
            transform: translateY(-2px);
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 14px;
            transition: border 0.3s;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #007bff;
        }
        button {
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
            border-radius: 4px;
            transition: background 0.3s, transform 0.3s;
        }
        button:hover {
            background: #218838;
            transform: scale(1.02);
        }
        .action-btn {
            display: inline-block;
            background: #ffc107;
            color: #333;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin: 4px;
            transition: background 0.3s;
        }
        .action-btn:hover {
            background: #e0a800;
        }
        .section-title {
            margin-top: 25px;
            color: #007bff;
            font-size: 1.4em;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <span>Shop Management</span>
        <ul>
            <li><a href="admin_dashboard.php">Home</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Manage Products</h2>

        <!-- Add Product Form -->
        <div class="form-section">
            <h3>Add New Product</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="name" placeholder="Product Name" required>
                <textarea name="details" placeholder="Product Details" required></textarea>
                <input type="file" name="image" accept="image/*">
                <input type="file" name="video" accept="video/*">
                <input type="number" name="price" placeholder="Product Price (Tk)" step="0.01" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>

        <!-- Product Table -->
        <h3>Added Products</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Details</th>
                    <th>Image</th>
                    <th>Video</th>
                    <th>Price (Tk)</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM products";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['details']}</td>
                        <td>" . ($row['image'] ? "<img src='{$row['image']}' width='50'>" : "") . "</td>
                        <td>" . ($row['video'] ? "<video width='50' controls><source src='{$row['video']}'></video>" : "") . "</td>
                        <td>Tk {$row['price']}</td>
                        <td>{$row['quantity']}</td>
                        <td>
                            <a class='action-btn' href='?edit={$row['id']}'>Edit</a>
                            <a class='action-btn' href='?delete={$row['id']}' onclick='return confirm(\"Delete?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $sql = "SELECT * FROM products WHERE id=$id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            echo "
            <div class='form-section'>
                <h3>Update Product</h3>
                <form action='' method='POST' enctype='multipart/form-data'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <input type='hidden' name='existing_image' value='{$row['image']}'>
                    <input type='hidden' name='existing_video' value='{$row['video']}'>
                    <input type='text' name='name' value='{$row['name']}' required>
                    <textarea name='details' required>{$row['details']}</textarea>
                    <input type='file' name='image' accept='image/*'>
                    <input type='file' name='video' accept='video/*'>
                    <input type='number' name='price' value='{$row['price']}' step='0.01' required>
                    <input type='number' name='quantity' value='{$row['quantity']}' required>
                    <button type='submit' name='update_product'>Update Product</button>
                </form>
            </div>";
        }
        ?>

        <!-- Ordered Products Section -->
        <div class="section-title">Ordered Products</div>
        <table class="sold-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Amount (Tk)</th>
                    <th>Phone Number</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM orders";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $actions = ($row['status'] != 'delivered') ? "
                        <form method='POST'>
                            <input type='hidden' name='order_id' value='{$row['id']}'>
                            <select name='status'>
                                <option " . ($row['status'] == 'pending' ? 'selected' : '') . ">pending</option>
                                <option " . ($row['status'] == 'shipping' ? 'selected' : '') . ">shipping</option>
                            </select>
                            <button type='submit' name='update_status'>Update</button>
                        </form>" : "No Actions";
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['user_id']}</td>
                        <td>{$row['product_id']}</td>
                        <td>{$row['quantity']}</td>
                        <td>Tk {$row['total_amount']}</td>
                        <td>{$row['phone_number']}</td>
                        <td>{$row['location']}</td>
                        <td>{$row['order_date']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['payment_method']}</td>
                        <td>$actions</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>