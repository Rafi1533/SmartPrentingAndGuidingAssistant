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
    <title>Parent's Care</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            background: url('Home.png') no-repeat center center/cover;
            transition: background 1s ease-in-out;
        }

        /* Navbar */
        .navbar {
            background: #1565C0;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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
            transition: color 0.3s, transform 0.3s;
        }
        .navbar ul li a:hover {
            color: #FFD700;
            transform: scale(1.1);
        }

        /* Navigation Section */
        .nav-container {
            position: fixed;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            z-index: 10;
        }
        .nav-button {
            width: 60px;
            height: 60px;
            background-color: #1976D2;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, background 0.3s;
        }
        .nav-button:hover {
            transform: scale(1.1);
            background-color: #1565C0;
        }

        /* Section Style */
        .section {
            display: none;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 60%;
            margin: 40px auto;
            transition: transform 0.5s ease-in-out, opacity 0.5s;
            opacity: 0;
        }
        .section.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .section h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .section p {
            font-size: 16px;
            line-height: 1.6;
        }
        /* Section Hover Effect */
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <span>Smart Parenting Assistant</span>
        <ul>
            <li><a href="parent_dashboard.php" onclick="showSection('home')">Home</a></li>
            <li><a href="childcare.php" onclick="showSection('child-care-guidelines')">Child's Care</a></li>
            <li><a href="#">Login</a></li>
        </ul>
    </nav>

    <!-- Navigation System -->
    <div class="nav-container">
        <div class="nav-button" onclick="showSection('child-care-guidelines')">C</div>
        <div class="nav-button" onclick="showSection('maternity-care-guidance')">M</div>
        <div class="nav-button" onclick="showSection('counseling')">C</div>
    </div>

    <!-- Parent Care Content Sections -->
    <section id="home" class="section active" onclick="window.location.href='parent_care_doctors.php'">
        <h2>Welcome to the Parent's Care Section</h2>
        <p>Explore our resources for Child Care Guidelines, Maternity Care Guidance, and Counseling. Choose from the options in the navigation.</p>
    </section>
    <section id="child-care-guidelines" class="section" onclick="window.location.href='parent_child_care_guideline.php'">
        <h2>Child Care Guidelines</h2>
        <p>Our child care guidelines offer step-by-step advice on how to ensure your child’s development is on track. From feeding schedules to developmental milestones, we’ve got you covered.</p>
    </section>
    <section id="maternity-care-guidance" class="section" onclick="window.location.href='maternity_user_dashboard.php'">
        <h2>Maternity Care Guidance</h2>
        <p>Our maternity care guidance provides essential information and tips for expectant mothers. Get advice on prenatal care, nutrition, and preparing for childbirth.</p>
    </section>
    <section id="counseling" class="section" onclick="window.location.href='parent_counseling.php'">
        <h2>Counseling</h2>
        <p>Our counseling services offer emotional support for parents dealing with the challenges of raising children. We provide expert advice and strategies for managing stress and improving family dynamics.</p>
    </section>

    <script>
        let currentSection = 'home';
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
            
            // Change the background image based on the active section
            const body = document.body;
            if (sectionId === 'home') {
                body.style.background = "url('Home.png') no-repeat center center/cover";
            } else if (sectionId === 'child-care-guidelines') {
                body.style.background = "url('ChildCare.png') no-repeat center center/cover";
            } else if (sectionId === 'maternity-care-guidance') {
                body.style.background = "url('Maternity.png') no-repeat center center/cover";
            } else if (sectionId === 'counseling') {
                body.style.background = "url('Counseling.png') no-repeat center center/cover";
            }
        }
    </script>
</body>
</html>
