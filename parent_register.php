<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $gender = $_POST['gender'];
    $kids = $_POST['number_of_kids'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $area = $_POST['area'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    if ($password !== $repassword) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, gender, number_of_kids, email, phone, area, password)
                VALUES ('$first', '$last', '$gender', '$kids', '$email', '$phone', '$area', '$hash')";

        if ($conn->query($sql) === TRUE) {
            header("Location: parent_login.php?msg=registered");
            exit;
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Parent Registration - Smart Parenting Assistant</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

  :root {
    --blue: #1565c0;
    --blue-light: #1976d2;
    --blue-dark: #0d47a1;
    --white: #fff;
    --gray-light: #f5f7fa;
    --gray-dark: #374151;
    --shadow-light: rgba(21, 101, 192, 0.35);
  }

  /* Dark mode variables */
  body.dark {
    --blue: #90caf9;
    --blue-light: #bbdefb;
    --blue-dark: #64b5f6;
    --white: #e3f2fd;
    --gray-light: #121212;
    --gray-dark: #b0bec5;
    --shadow-light: rgba(144, 202, 249, 0.7);
    background-color: var(--gray-light);
    color: var(--blue-dark);
  }

  /* Reset */
  * {
    box-sizing: border-box;
  }
  body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: var(--gray-light);
    color: var(--blue-dark);
    transition: background-color 0.4s ease, color 0.4s ease;
    min-height: 100vh;
  }

  /* Navbar */
  .navbar {
    background: var(--blue);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    box-shadow: 0 6px 12px var(--shadow-light);
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: background-color 0.4s ease;
  }
  .navbar a {
    color: var(--white);
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 6px;
    transition: background-color 0.3s ease, transform 0.3s ease;
  }
  .navbar a:first-child {
    margin-left: 0;
  }
  .navbar a:hover,
  .navbar a:focus {
    background-color: var(--blue-dark);
    transform: scale(1.1);
    outline: none;
  }

  /* Dark mode toggle */
  .dark-toggle {
    cursor: pointer;
    background: transparent;
    border: 2px solid var(--white);
    color: var(--white);
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 6px;
    transition: background-color 0.3s ease, color 0.3s ease;
    user-select: none;
  }
  .dark-toggle:hover,
  .dark-toggle:focus {
    background-color: var(--white);
    color: var(--blue);
    outline: none;
  }

  /* Container */
  .container {
    max-width: 420px;
    margin: 80px auto 40px auto;
    background: var(--white);
    padding: 40px 30px;
    border-radius: 14px;
    box-shadow: 0 16px 35px var(--shadow-light);
    color: var(--blue-dark);
    transition: background-color 0.4s ease, color 0.4s ease;
  }

  body.dark .container {
    background: var(--gray-dark);
    color: var(--white);
    box-shadow: 0 16px 35px var(--shadow-light);
  }

  h2 {
    margin: 0 0 30px 0;
    font-weight: 700;
    font-size: 2.2rem;
    text-align: center;
  }

  /* Inputs & select */
  input[type="text"],
  input[type="email"],
  input[type="number"],
  select {
    width: 100%;
    padding: 14px 16px;
    margin-bottom: 20px;
    border: 2px solid var(--blue-light);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background: var(--white);
    color: var(--blue-dark);
  }
  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="number"]:focus,
  select:focus {
    border-color: var(--blue-dark);
    outline: none;
    box-shadow: 0 0 8px var(--blue-light);
    background: var(--white);
  }
  body.dark input[type="text"],
  body.dark input[type="email"],
  body.dark input[type="number"],
  body.dark select {
    background: var(--gray-light);
    color: var(--blue-dark);
  }
  body.dark input[type="text"]:focus,
  body.dark input[type="email"]:focus,
  body.dark input[type="number"]:focus,
  body.dark select:focus {
    background: var(--gray-light);
    color: var(--blue-dark);
  }

  /* Password container */
  .password-wrapper {
    position: relative;
    margin-bottom: 24px;
  }
  .password-wrapper input[type="password"],
  .password-wrapper input[type="text"] {
    width: 100%;
    padding: 14px 48px 14px 16px;
    border: 2px solid var(--blue-light);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    background: var(--white);
    color: var(--blue-dark);
    box-sizing: border-box;
  }
  .password-wrapper input[type="password"]:focus,
  .password-wrapper input[type="text"]:focus {
    border-color: var(--blue-dark);
    outline: none;
    box-shadow: 0 0 8px var(--blue-light);
  }
  body.dark .password-wrapper input[type="password"],
  body.dark .password-wrapper input[type="text"] {
    background: var(--gray-light);
    color: var(--blue-dark);
  }
  body.dark .password-wrapper input[type="password"]:focus,
  body.dark .password-wrapper input[type="text"]:focus {
    background: var(--gray-light);
    color: var(--blue-dark);
  }

  .eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    user-select: none;
    font-size: 1.25rem;
    color: var(--blue-dark);
    transition: color 0.3s ease;
  }
  .eye-btn:hover,
  .eye-btn:focus {
    color: var(--blue-light);
    outline: none;
  }
  body.dark .eye-btn {
    color: var(--white);
  }
  body.dark .eye-btn:hover,
  body.dark .eye-btn:focus {
    color: var(--blue-light);
  }

  /* Button */
  button[type="submit"] {
    width: 100%;
    padding: 14px;
    font-size: 1.1rem;
    background: var(--blue);
    color: var(--white);
    font-weight: 700;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 6px 14px var(--shadow-light);
    transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
  }
  button[type="submit"]:hover,
  button[type="submit"]:focus {
    background: var(--blue-dark);
    box-shadow: 0 10px 25px var(--shadow-light);
    transform: scale(1.05);
    outline: none;
  }

  /* Responsive */
  @media (max-width: 480px) {
    .container {
      margin: 60px 20px;
      padding: 35px 25px;
    }
    h2 {
      font-size: 1.8rem;
    }
    button[type="submit"] {
      font-size: 1rem;
      padding: 12px;
    }
  }
</style>
</head>
<body>

<div class="navbar" role="navigation" aria-label="Main navigation">
  <div>
    <a href="parent_register.php">Register</a>
    <a href="parent_login.php">Login</a>
  </div>
  <button class="dark-toggle" id="darkToggle" aria-pressed="false" aria-label="Toggle dark mode">Dark Mode</button>
</div>

<div class="container" role="main">
  <h2>Parent Registration</h2>
  <form method="POST" novalidate>
    <input type="text" name="first_name" placeholder="First Name" required autocomplete="given-name" />
    <input type="text" name="last_name" placeholder="Last Name" required autocomplete="family-name" />
    
    <select name="gender" required aria-label="Select Gender">
      <option value="">Select Gender</option>
      <option>Male</option>
      <option>Female</option>
    </select>
    
    <input type="number" name="number_of_kids" placeholder="Number of Kids" min="0" required />
    
    <input type="email" name="email" placeholder="Email" required autocomplete="email" />
    <input type="text" name="phone" placeholder="Phone Number" required autocomplete="tel" />
    
    <select name="area" required aria-label="Select Area">
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

    <div class="password-wrapper">
      <input type="password" name="password" id="password" placeholder="Password" required autocomplete="new-password" />
      <span class="eye-btn" tabindex="0" role="button" aria-label="Toggle password visibility" onclick="togglePassword('password')" onkeydown="if(event.key==='Enter' || event.key===' ') { event.preventDefault(); togglePassword('password'); }">👁</span>
    </div>

    <div class="password-wrapper">
      <input type="password" name="repassword" id="repassword" placeholder="Re-enter Password" required autocomplete="new-password" />
      <span class="eye-btn" tabindex="0" role="button" aria-label="Toggle password visibility" onclick="togglePassword('repassword')" onkeydown="if(event.key==='Enter' || event.key===' ') { event.preventDefault(); togglePassword('repassword'); }">👁</span>
    </div>

    <button type="submit">Register</button>
  </form>
</div>

<script>
  function togglePassword(id) {
    const field = document.getElementById(id);
    field.type = (field.type === "password") ? "text" : "password";
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

  window.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('darkMode');
    if (saved === 'enabled') {
      setDarkMode(true);
    }
  });
</script>

</body>
</html>
