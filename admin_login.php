<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, first_name, password, admin_photo FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_first_name'] = $admin['first_name'];
            $_SESSION['admin_photo'] = $admin['admin_photo'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No admin found with this email.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <title>Admin Login - Smart Parenting System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --cool-blue: #4a90e2;
      --cool-blue-light: #8bb9ff;
      --cool-blue-dark: #3161b9;
      --cool-gray-light: #f0f4f8;
      --cool-gray-mid: #a6b8d9;
      --cool-gray-dark: #2f3e5e;
      --cool-gray-darker: #1a2640;
      --white: #fff;
      --error-red: #e94b4b;
      --success-green: #3cb371;
      --transition: 0.3s ease;
      --border-radius: 8px;
      --input-border: 1.8px solid var(--cool-gray-mid);
      --input-border-focus: 1.8px solid var(--cool-blue);
      --box-shadow-light: 0 4px 20px rgba(74, 144, 226, 0.15);
      --box-shadow-dark: 0 4px 20px rgba(20, 35, 70, 0.7);
    }

    /* Base */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--cool-gray-light);
      color: var(--cool-gray-dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      transition: background var(--transition), color var(--transition);
    }
    body.dark {
      background: var(--cool-gray-darker);
      color: var(--cool-blue-light);
    }

    /* Background video */
    #bgVideo {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -1;
      filter: brightness(0.5);
      transition: filter 0.3s ease;
    }
    body.dark #bgVideo {
      filter: brightness(0.3);
    }

    /* Navbar */
    nav.navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: var(--white);
      box-shadow: var(--box-shadow-light);
      position: sticky;
      top: 0;
      z-index: 100;
      transition: background var(--transition), color var(--transition);
    }
    body.dark nav.navbar {
      background: var(--cool-gray-dark);
      box-shadow: var(--box-shadow-dark);
    }
    nav.navbar .logo {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--cool-blue);
      user-select: none;
    }
    body.dark nav.navbar .logo {
      color: var(--cool-blue-light);
    }
    nav.navbar .nav-links {
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }
    nav.navbar a {
      text-decoration: none;
      color: var(--cool-gray-dark);
      font-weight: 600;
      position: relative;
      padding-bottom: 4px;
      transition: color var(--transition);
      border-bottom: 2px solid transparent;
    }
    nav.navbar a:hover,
    nav.navbar a:focus {
      color: var(--cool-blue);
      border-bottom-color: var(--cool-blue);
      outline: none;
    }
    body.dark nav.navbar a {
      color: var(--cool-blue-light);
    }
    body.dark nav.navbar a:hover,
    body.dark nav.navbar a:focus {
      color: var(--white);
      border-bottom-color: var(--white);
    }

    /* Dark mode toggle button */
    #darkModeToggle {
      background: none;
      border: 2px solid var(--cool-blue);
      color: var(--cool-blue);
      padding: 6px 16px;
      border-radius: 20px;
      font-weight: 600;
      cursor: pointer;
      user-select: none;
      transition: background var(--transition), color var(--transition), border-color var(--transition);
    }
    #darkModeToggle:hover,
    #darkModeToggle:focus {
      background: var(--cool-blue);
      color: var(--white);
      outline: none;
      border-color: var(--cool-blue-dark);
    }
    body.dark #darkModeToggle {
      border-color: var(--cool-blue-light);
      color: var(--cool-blue-light);
    }
    body.dark #darkModeToggle:hover,
    body.dark #darkModeToggle:focus {
      background: var(--cool-blue-light);
      color: var(--cool-gray-darker);
      border-color: var(--cool-blue-light);
    }

    /* Container & Form */
    .container {
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 3rem 1rem;
    }
    form {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(12px) saturate(180%);
      padding: 3rem 2.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow-light);
      width: 100%;
      max-width: 420px;
      transition: background var(--transition), color var(--transition);
      color: var(--cool-gray-dark);
      user-select: none;
    }
    body.dark form {
      background: rgba(20, 30, 60, 0.4);
      color: var(--cool-blue-light);
      box-shadow: var(--box-shadow-dark);
    }
    h2 {
      margin: 0 0 1.5rem 0;
      font-weight: 700;
      font-size: 2rem;
      letter-spacing: 1px;
      text-align: center;
    }

    /* Inputs */
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px 14px;
      margin-top: 1rem;
      border-radius: var(--border-radius);
      border: var(--input-border);
      font-size: 1rem;
      color: var(--cool-gray-dark);
      background: var(--cool-gray-light);
      transition: border-color var(--transition), background var(--transition), color var(--transition);
      outline-offset: 2px;
      box-sizing: border-box;
    }
    body.dark input[type="email"],
    body.dark input[type="password"] {
      background: #1e2a4f;
      color: var(--cool-blue-light);
      border-color: #3b4c7d;
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: var(--cool-blue);
      background: var(--white);
      color: var(--cool-gray-dark);
      outline: none;
      box-shadow: 0 0 8px var(--cool-blue-light);
    }
    body.dark input[type="email"]:focus,
    body.dark input[type="password"]:focus {
      background: #2a3a6a;
      color: var(--white);
      border-color: var(--cool-blue-light);
      box-shadow: 0 0 8px var(--cool-blue-light);
    }

    /* Password wrapper */
    .password-wrapper {
      position: relative;
      margin-top: 1rem;
    }
    .eye-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 1.3rem;
      color: var(--cool-gray-mid);
      transition: color var(--transition);
      user-select: none;
      padding: 4px;
      border-radius: 50%;
      border: 1.5px solid transparent;
    }
    .eye-btn:hover,
    .eye-btn:focus {
      color: var(--cool-blue);
      border-color: var(--cool-blue);
      outline: none;
      background: var(--cool-blue-light);
      box-shadow: 0 0 8px var(--cool-blue-light);
    }
    body.dark .eye-btn {
      color: var(--cool-gray-mid);
    }
    body.dark .eye-btn:hover,
    body.dark .eye-btn:focus {
      color: var(--white);
      border-color: var(--cool-blue-light);
      background: var(--cool-blue-light);
      box-shadow: 0 0 8px var(--cool-blue-light);
    }

    input[type="password"], input[type="text"] {
      padding-right: 40px;
    }

    /* Button */
    button[type="submit"] {
      margin-top: 2.5rem;
      width: 100%;
      padding: 14px;
      border: 1.8px solid var(--cool-blue);
      border-radius: 30px;
      background: var(--white);
      color: var(--cool-blue);
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background var(--transition), color var(--transition), border-color var(--transition), box-shadow var(--transition);
      user-select: none;
      box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
    }
    button[type="submit"]:hover,
    button[type="submit"]:focus {
      background: var(--cool-blue);
      color: var(--white);
      border-color: var(--cool-blue-dark);
      outline: none;
      box-shadow: 0 8px 24px rgba(49, 97, 185, 0.6);
    }
    body.dark button[type="submit"] {
      background: var(--cool-blue-light);
      color: var(--cool-gray-darker);
      border-color: var(--cool-blue-light);
      box-shadow: 0 6px 14px rgba(139, 185, 255, 0.6);
    }
    body.dark button[type="submit"]:hover,
    body.dark button[type="submit"]:focus {
      background: var(--cool-blue-dark);
      color: var(--white);
      border-color: var(--cool-blue-dark);
      box-shadow: 0 8px 24px rgba(49, 97, 185, 0.9);
    }

    /* Messages */
    .errors, .success-msg {
      margin-top: 1rem;
      border-radius: var(--border-radius);
      padding: 12px 15px;
      font-weight: 600;
      font-size: 0.95rem;
      user-select: none;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .errors {
      background: var(--error-red);
      color: var(--white);
      box-shadow: 0 2px 10px rgba(233, 75, 75, 0.7);
    }
    .success-msg {
      background: var(--success-green);
      color: var(--white);
      box-shadow: 0 2px 10px rgba(60, 179, 113, 0.7);
    }

    /* Register link */
    p.register-link {
      margin-top: 1.75rem;
      text-align: center;
      font-weight: 600;
      font-size: 0.9rem;
      color: var(--cool-gray-dark);
      user-select: none;
    }
    p.register-link a {
      color: var(--cool-blue);
      text-decoration: none;
      font-weight: 700;
      transition: color var(--transition);
    }
    p.register-link a:hover,
    p.register-link a:focus {
      color: var(--cool-blue-dark);
      text-decoration: underline;
      outline: none;
    }
    body.dark p.register-link {
      color: var(--cool-blue-light);
    }
    body.dark p.register-link a {
      color: var(--cool-blue-light);
    }
    body.dark p.register-link a:hover,
    body.dark p.register-link a:focus {
      color: var(--white);
    }

    /* Responsive */
    @media (max-width: 480px) {
      nav.navbar {
        padding: 1rem;
        font-size: 14px;
      }
      form {
        padding: 2rem 1.5rem;
      }
      button[type="submit"] {
        font-size: 1rem;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
  <!-- Background Video -->
  <video autoplay muted loop id="bgVideo" playsinline>
    <source src="admin.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <nav class="navbar" role="navigation" aria-label="Primary navigation">
    <div class="logo">Admin Panel</div>
    <div class="nav-links">
      <button id="darkModeToggle" aria-pressed="false" aria-label="Toggle dark mode">🌙 Dark Mode</button>
      <a href="admin_register.php">Register</a>
      <a href="index.html">Home</a>
    </div>
  </nav>

  <div class="container">
    <form method="POST" aria-labelledby="loginTitle" novalidate>
      <h2 id="loginTitle">Admin Login</h2>

      <?php if (!empty($error)) {
        echo '<div class="errors" role="alert">'.$error.'</div>';
      }
      if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
        echo '<div class="success-msg" role="alert">Registration successful! Please login.</div>';
      }
      ?>

      <input type="email" name="email" placeholder="Email" required autocomplete="email" aria-required="true" />
      <div class="password-wrapper">
        <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password" aria-required="true" />
        <span tabindex="0" role="button" aria-label="Toggle password visibility" class="eye-btn"
          onclick="togglePassword('password')"
          onkeydown="if(event.key==='Enter' || event.key===' ') togglePassword('password')">👁</span>
      </div>

      <button type="submit">Login</button>
      <p class="register-link">Don't have an account? <a href="admin_register.php">Register here</a></p>
    </form>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }

    const toggleBtn = document.getElementById('darkModeToggle');
    const body = document.body;

    function applyTheme(theme) {
      if (theme === 'dark') {
        body.classList.add('dark');
        toggleBtn.textContent = 'Light Mode';
        toggleBtn.setAttribute('aria-pressed', 'true');
      } else {
        body.classList.remove('dark');
        toggleBtn.textContent = 'Dark Mode';
        toggleBtn.setAttribute('aria-pressed', 'false');
      }
    }

    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    toggleBtn.addEventListener('click', () => {
      const isDark = body.classList.toggle('dark');
      applyTheme(isDark ? 'dark' : 'light');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
  </script>
</body>
</html>
