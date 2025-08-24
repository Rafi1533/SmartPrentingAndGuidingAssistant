<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Dashboard - Smart Parenting Assistant</title>
<style>
    /* Reset & basics */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }
    body {
        background: #f0f4f8;
        color: #1E3A8A;
        min-height: 100vh;
        overflow-x: hidden;
        position: relative;
        padding-bottom: 80px; /* Footer height space */
    }
    /* Background video */
    video.background-video {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
        opacity: 1;
    }
    /* Minimalist navbar */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background: #1E3A8A;
        color: #fff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        position: sticky;
        top: 0;
        z-index: 1000;
        border-radius: 0 0 15px 15px;
    }
    .navbar .welcome {
        font-weight: 600;
        font-size: 1.2rem;
    }
    .navbar ul {
        list-style: none;
        display: flex;
        gap: 2rem;
    }
    .navbar ul li a {
        text-decoration: none;
        color: #fff;
        font-weight: 500;
        position: relative;
        padding-bottom: 3px;
        transition: all 0.3s ease;
    }
    .navbar ul li a::after {
        content: '';
        position: absolute;
        width: 0%;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: #00f0ff;
        transition: 0.3s ease;
    }
    .navbar ul li a:hover::after {
        width: 100%;
    }
    /* Container & cards */
    .container {
        max-width: 1200px;
        margin: 3rem auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        padding: 0 1rem;
    }
    .card {
        background: rgba(255, 255, 255, 0.1); /* more transparent */
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        backdrop-filter: blur(12px); /* glass effect */
        border: 1px solid rgba(255, 255, 255, 0.2);
        position: relative;
        overflow: hidden;
        color: #00081dff;
    }
    .card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    .card .icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease, text-shadow 0.3s ease;
        color: #1E3A8A;
        text-shadow: 0 0 6px rgba(59,130,246,0.3);
    }
    .card:hover .icon {
        transform: scale(1.2);
        text-shadow: 0 0 12px rgba(59,130,246,0.5);
    }
    .card h2 {
        font-size: 1.7rem;
        margin-bottom: 1rem;
        color: #000923ff;
    }
    .card p {
        color: #00122eff;
        font-size: 1rem;
        line-height: 1.5;
    }
    .card a {
        text-decoration: none;
        color: inherit;
    }
    /* Fixed footer */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #1E3A8A;
        color: #fff;
        text-align: center;
        padding: 1rem;
        font-size: 0.9rem;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.2);
        z-index: 1000;
    }
    /* Responsive */
    @media (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
        }
        .navbar {
            flex-direction: column;
            gap: 1rem;
            border-radius: 0 0 15px 15px;
        }
        .navbar ul {
            flex-direction: column;
            align-items: center;
        }
    }
</style>
</head>
<body>

<!-- Background video -->
<video autoplay muted loop playsinline class="background-video">
    <source src="parenthome.mp4" type="video/mp4">
</video>

<!-- Navbar -->
<nav class="navbar">
    <span class="welcome">Welcome, <?php echo $_SESSION['first_name']; ?> 👋</span>
    <ul>
        <li><a href="childcare.php">Child's Care</a></li>
        <li><a href="parentcare.php">Parent's Care</a></li>
        <li><a href="parent_logout.php">Logout</a></li>
    </ul>
</nav>

<!-- Dashboard cards -->
<div class="container">
    <div class="card">
        <a href="childcare.php">
            <div class="icon">🧸</div>
            <h2>Child's Care</h2>
            <p>Discover tools and resources to nurture your child's growth and well-being.</p>
        </a>
    </div>
    <div class="card">
        <a href="parentcare.php">
            <div class="icon">🤝</div>
            <h2>Parent's Care</h2>
            <p>Empowering you with support for a confident parenting experience.</p>
        </a>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    &copy; 2025 Smart Parenting Assistant. All rights reserved.
</div>

</body>
</html>
