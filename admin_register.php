<?php
include "db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $area = $_POST['area'];
    $nid_number = trim($_POST['nid_number']);
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($area) || empty($nid_number) || empty($password) || empty($repassword)) {
        $errors[] = "All fields are required.";
    }

    // Validate password match
    if ($password !== $repassword) {
        $errors[] = "Passwords do not match.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate file uploads
    if (!isset($_FILES['nid_card_photo']) || $_FILES['nid_card_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "NID card photo upload error.";
    }

    if (!isset($_FILES['admin_photo']) || $_FILES['admin_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Admin photo upload error.";
    }

    // Allowed image mime types
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!in_array($_FILES['nid_card_photo']['type'], $allowed_types)) {
        $errors[] = "NID card photo must be JPG or PNG.";
    }

    if (!in_array($_FILES['admin_photo']['type'], $allowed_types)) {
        $errors[] = "Admin photo must be JPG or PNG.";
    }

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) {
        $errors[] = "Email already registered.";
    }
    $check_email->close();

    if (count($errors) === 0) {
        // Upload folders
        $nid_folder = "uploads/nid_photos/";
        $admin_photo_folder = "uploads/admin_photos/";

        // Create folders if not exist
        if (!is_dir($nid_folder)) mkdir($nid_folder, 0777, true);
        if (!is_dir($admin_photo_folder)) mkdir($admin_photo_folder, 0777, true);

        // Generate unique file names
        $nid_file_name = uniqid() . "_" . basename($_FILES['nid_card_photo']['name']);
        $admin_file_name = uniqid() . "_" . basename($_FILES['admin_photo']['name']);

        $nid_target = $nid_folder . $nid_file_name;
        $admin_target = $admin_photo_folder . $admin_file_name;

        // Move uploaded files
        if (move_uploaded_file($_FILES['nid_card_photo']['tmp_name'], $nid_target) && move_uploaded_file($_FILES['admin_photo']['tmp_name'], $admin_target)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert admin into DB
            $stmt = $conn->prepare("INSERT INTO admins (first_name, last_name, email, phone, area, nid_card_photo, nid_number, admin_photo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $area, $nid_target, $nid_number, $admin_target, $hashed_password);

            if ($stmt->execute()) {
                header("Location: admin_login.php?msg=registered");
                exit;
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errors[] = "Failed to upload files.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Registration</title>
    <style>
        /* Navbar */
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background: linear-gradient(45deg, #8e44ad, #3498db);
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav.navbar {
            background: rgba(0,0,0,0.4);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 22px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        }
        nav.navbar a {
            color: #f39c12;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        nav.navbar a:hover {
            color: #f1c40f;
            text-decoration: underline;
        }

        /* Container */
        .container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        form {
            background: rgba(0,0,0,0.5);
            padding: 30px 35px;
            border-radius: 12px;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.7);
        }
        h2 {
            text-align: center;
            margin-bottom: 24px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 15px;
            margin-bottom: 6px;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            outline: none;
            transition: box-shadow 0.3s ease;
        }
        input[type="file"] {
            padding: 5px 10px;
            background: white;
            color: black;
            border-radius: 6px;
        }
        input:focus, select:focus {
            box-shadow: 0 0 8px #f39c12;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #f39c12;
            color: white;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #d78c0a;
        }
        .errors {
            background: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 700;
            text-align: center;
        }
        p.login-link {
            text-align: center;
            margin-top: 20px;
            color: #ddd;
            font-weight: 600;
        }
        p.login-link a {
            color: #f1c40f;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        p.login-link a:hover {
            color: #f39c12;
            text-decoration: underline;
        }
        .eye-btn {
            position: absolute;
            right: 16px;
            top: 42%;
            cursor: pointer;
            user-select: none;
            font-size: 18px;
            color: #f39c12;
        }
        .password-wrapper {
            position: relative;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div>Admin Panel</div>
        <div>
            <a href="admin_login.php">Login</a>
        </div>
    </nav>

    <div class="container">
        <form method="POST" enctype="multipart/form-data" onsubmit="return validatePasswords()">
            <h2>Register as Admin</h2>
            <?php if (!empty($errors)) {
                echo '<div class="errors">'.implode("<br>", $errors).'</div>';
            } ?>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" placeholder="First Name" required />

            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" placeholder="Last Name" required />

            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Email" required />

            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="Phone Number" required />

            <label for="area">Area (Division)</label>
            <select name="area" id="area" required>
                <option value="">Select Area</option>
                <option>Dhaka</option>
                <option>Chattogram</option>
                <option>Khulna</option>
                <option>Rajshahi</option>
                <option>Barishal</option>
                <option>Sylhet</option>
                <option>Rangpur</option>
                <option>Mymensingh</option>
            </select>

            <label for="nid_card_photo">NID Card Photo (JPG/PNG)</label>
            <input type="file" name="nid_card_photo" id="nid_card_photo" accept="image/jpeg,image/png" required />

            <label for="nid_number">NID Number</label>
            <input type="text" name="nid_number" id="nid_number" placeholder="NID Number" required />

            <label for="admin_photo">Admin Photo (JPG/PNG)</label>
            <input type="file" name="admin_photo" id="admin_photo" accept="image/jpeg,image/png" required />

            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password" required />
                <span class="eye-btn" onclick="togglePassword('password')">👁</span>
            </div>
            <div class="password-wrapper">
                <input type="password" name="repassword" id="repassword" placeholder="Re-enter Password" required />
                <span class="eye-btn" onclick="togglePassword('repassword')">👁</span>
            </div>

            <button type="submit">Register</button>

            <p class="login-link">
                Already registered? <a href="admin_login.php">Login here</a>
            </p>
        </form>
    </div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}
function validatePasswords() {
    const pw = document.getElementById('password').value;
    const rpw = document.getElementById('repassword').value;
    if (pw !== rpw) {
        alert("Passwords do not match!");
        return false;
    }
    return true;
}
</script>
</body>
</html>
