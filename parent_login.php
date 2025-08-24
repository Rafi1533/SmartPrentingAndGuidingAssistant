<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['first_name'] = $row['first_name'];
            header("Location: parent_dashboard.php");
            exit;
        } else {
            echo "<script>alert('Wrong password');</script>";
        }
    } else {
        echo "<script>alert('User not found');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Parent Login - Smart Parenting Assistant</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        :root {
            --primary: #003087;
            --primary-light: #00b7eb;
            --primary-dark: #002171;
            --white: #ffffff;
            --gray-light: #f5f6fa;
            --gray-dark: #2c2c2c;
            --shadow: rgba(0, 48, 135, 0.3);
        }

        /* Dark mode variables */
        body.dark {
            --primary: #00b7eb;
            --primary-light: #4fc3f7;
            --primary-dark: #003087;
            --white: #e0e0e0;
            --gray-light: #1e1e1e;
            --gray-dark: #ffffff;
            --shadow: rgba(79, 195, 247, 0.5);
            background-color: var(--gray-light);
            color: var(--white);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--white);
            color: var(--gray-dark);
            transition: background-color 0.4s ease, color 0.4s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Background Video */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            opacity: 1;
            filter: brightness(0.8);
        }

        /* Navbar */
        .navbar {
            background: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            box-shadow: 0 2px 8px var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            transition: transform 0.3s ease;
        }
        body.dark .navbar {
            background: var(--gray-light);
        }
        .navbar:hover {
            transform: translateY(-2px);
        }
        .navbar a {
            color: var(--primary);
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 12px;
            position: relative;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .navbar a:first-child {
            margin-left: 0;
        }
        .navbar a:hover,
        .navbar a:focus {
            color: var(--primary-dark);
            transform: scale(1.1);
            outline: none;
        }
        .navbar a::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-dark);
            transition: width 0.3s ease;
        }
        .navbar a:hover::before {
            width: 100%;
        }

        /* Dark mode toggle */
        .dark-toggle {
            cursor: pointer;
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            user-select: none;
        }
        .dark-toggle:hover,
        .dark-toggle:focus {
            background-color: var(--primary);
            color: var(--white);
            transform: scale(1.05);
            outline: none;
        }
        .dark-toggle::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }
        .dark-toggle:hover::before {
            width: 200px;
            height: 200px;
        }

        /* Container */
        .container {
            max-width: 600px;
            width: 90%;
            margin: 100px auto 40px;
            background: rgba(255, 255, 255, 0.62);
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 8px 20px var(--shadow);
            color: var(--gray-dark);
            transition: transform 0.3s ease;
            z-index: 1;
        }
        .container:hover {
            transform: translateY(-5px);
        }
        body.dark .container {
            background: var(--gray-light);
            color: var(--white);
            box-shadow: 0 8px 20px var(--shadow);
        }

        h2 {
            margin: 0 0 30px 0;
            font-weight: 600;
            font-size: 2rem;
            text-align: center;
            color: var(--primary);
        }
        body.dark h2 {
            color: var(--primary-light);
        }

        /* Inputs */
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 20px;
            border: 2px solid var(--primary-light);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--white);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease, background-color 0.3s ease, color 0.3s ease;
        }
        input[type="email"]:hover,
        input[type="password"]:hover {
            transform: scale(1.01);
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary-dark);
            outline: none;
            box-shadow: 0 0 10px var(--primary-light);
            transform: scale(1.01);
        }
        body.dark input[type="email"],
        body.dark input[type="password"] {
            background: var(--gray-light);
            color: var(--white);
            border-color: var(--primary-light);
        }
        /* Password field when visible */
        input[type="text"].password-visible {
            background: rgba(0, 183, 235, 0.15); /* Light cyan background when visible */
            border: 2px solid var(--primary); /* Stronger deep blue border */
            color: var(--primary-dark); /* Darker text for contrast */
            box-shadow: 0 0 12px rgba(0, 183, 235, 0.5); /* Enhanced glow effect */
        }
        body.dark input[type="text"].password-visible {
            background: rgba(79, 195, 247, 0.25); /* Lighter cyan in dark mode */
            border: 2px solid var(--primary-light); /* Cyan border in dark mode */
            color: var(--white); /* White text for readability */
            box-shadow: 0 0 12px rgba(79, 195, 247, 0.6); /* Stronger glow in dark mode */
        }

        /* Password container */
        .password-wrapper {
            position: relative;
        }
        .eye-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 1.2rem;
            color: var(--primary-dark);
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .eye-btn:hover,
        .eye-btn:focus {
            color: var(--primary-light);
            transform: translateY(-50%) scale(1.2);
            outline: none;
        }
        body.dark .eye-btn {
            color: var(--white);
        }
        body.dark .eye-btn:hover,
        body.dark .eye-btn:focus {
            color: var(--primary-light);
        }
        .eye-btn.visible {
            color: var(--primary-light); /* Highlight eye icon when password is visible */
        }
        body.dark .eye-btn.visible {
            color: var(--primary);
        }

        /* Button */
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            background: var(--primary);
            color: var(--white);
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px var(--shadow);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        button[type="submit"]:hover,
        button[type="submit"]:focus {
            transform: scale(1.03);
            box-shadow: 0 6px 18px var(--shadow);
            outline: none;
        }
        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }
        button[type="submit"]:hover::before {
            width: 400px;
            height: 400px;
        }

        /* Footer text */
        p {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--gray-dark);
        }
        p a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
        }
        p a:hover,
        p a:focus {
            color: var(--primary-dark);
            outline: none;
        }
        p a::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-dark);
            transition: width 0.3s ease;
        }
        p a:hover::before {
            width: 100%;
        }
        body.dark p {
            color: var(--white);
        }
        body.dark p a {
            color: var(--primary-light);
        }
        body.dark p a:hover,
        body.dark p a:focus {
            color: var(--white);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                margin: 80px 20px;
                padding: 30px 20px;
            }
            h2 {
                font-size: 1.8rem;
            }
            button[type="submit"] {
                font-size: 1rem;
                padding: 12px;
            }
            .navbar {
                padding: 10px 20px;
            }
            .navbar a {
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>

<video id="bg-video" autoplay loop muted>
    <source src="parents.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<div class="navbar" role="navigation" aria-label="Main navigation">
    <div>
        <a href="parent_register.php">Register</a>
        <a href="parent_login.php">Login</a>
        <a href="index.html">Home</a>
    </div>
    <button class="dark-toggle" id="darkToggle" aria-pressed="false" aria-label="Toggle dark mode">Dark Mode</button>
</div>

<div class="container" role="main">
    <h2>Parent Login</h2>
    <form method="POST" novalidate>
        <input type="email" name="email" placeholder="Email" required autocomplete="email" />
        <div class="password-wrapper">
            <input type="password" name="password" id="loginpass" placeholder="Password" required autocomplete="current-password" />
            <span class="eye-btn" tabindex="0" role="button" aria-label="Toggle password visibility" onclick="togglePassword('loginpass')" onkeydown="if(event.key==='Enter' || event.key===' ') { event.preventDefault(); togglePassword('loginpass'); }">👁️</span>
        </div>
        <button type="submit">Login</button>
        <p>Not registered? <a href="parent_register.php">Register here</a></p>
    </form>
</div>

<script>
    // Password toggle
    function togglePassword(id) {
        const field = document.getElementById(id);
        const eyeBtn = field.nextElementSibling;
        if (field.type === "password") {
            field.type = "text";
            field.classList.add("password-visible");
            eyeBtn.classList.add("visible");
            eyeBtn.textContent = "🙈";
        } else {
            field.type = "password";
            field.classList.remove("password-visible");
            eyeBtn.classList.remove("visible");
            eyeBtn.textContent = "👁️";
        }
    }

    // Dark mode toggle with localStorage persistence
    const darkToggle = document.getElementById('darkToggle');
    const body = document.body;

    function setDarkMode(enabled) {
        if (enabled) {
            body.classList.add('dark');
            darkToggle.textContent = 'Light Mode';
            darkToggle.setAttribute('aria-pressed', 'true');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            body.classList.remove('dark');
            darkToggle.textContent = 'Dark Mode';
            darkToggle.setAttribute('aria-pressed', 'false');
            localStorage.setItem('darkMode', 'disabled');
        }
    }

    darkToggle.addEventListener('click', () => {
        setDarkMode(!body.classList.contains('dark'));
    });

    // Initialize on page load
    window.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('darkMode');
        if (saved === 'enabled') {
            setDarkMode(true);
        }
    });
</script>

</body>
</html>