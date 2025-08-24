<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parenting Assistant</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            transition: background 1s ease-in-out;
            background: url('Speakers.png') no-repeat center center/cover;
        }
        .navbar {
            background: rgba(21, 101, 192, 0.7); /* Semi-transparent navbar */
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px; /* Smaller font */
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px); /* Optional blur effect */
        }
        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .navbar ul li {
            margin: 0 10px;
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
        .container {
            display: flex;
            height: 90vh;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .circle-slider {
            width: 600px;
            height: 600px;
            background: #1976D2;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            position: absolute;
            left: -300px;
            top: 50%;
            transform: translateY(-50%) rotate(0deg);
            transition: transform 1s ease-in-out;
            cursor: pointer;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
        }
        .circle-slider:hover {
            background: #0D47A1;
            transform: translateY(-50%) scale(1.05) rotate(0deg);
        }
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 100px;
        }
        .section {
            display: none;
            background: white;
            padding: 25px; /* Reduced padding */
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 60%;
            transition: transform 0.5s ease-in-out, opacity 0.5s;
            opacity: 0;
            font-size: 16px; /* Smaller font size */
        }
        .section.active {
            display: block;
            opacity: 1;
            transform: scale(1.1);
        }
        .section:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            transform: scale(1.05); /* Slight scale on hover */
        }
        .section-link {
        text-decoration: none; /* Removes underline */
        color: inherit; /* Ensures text color doesn't change */
        display: block;
        width:auto;
        
    }

    </style>
</head>
<body id="body">
    <nav class="navbar">
        <span>Smart Parenting Assistant</span>
        <ul>
            <li><a href="childcare.php">Child's Care</a></li>
            <li><a href="admin_login.php">Admin Login</a></li>
            <li><a href="parent_login.php">Login</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="circle-slider" id="slider" onclick="rotateSlider()">
            Switch
        </div>
        <div class="content">
            <a href="childcare.php" class="section-link">
            <section id="childs-care" class="section active">
                <h2>Child's Care</h2>
                <p>Providing specialized care and guidance for children's development.</p>
            </section>
            </a>
            <a href="parentcare.html" class="section-link">
            <section id="parents-care" class="section">
                <h2>Parent's Care</h2>
                <p>Support and resources for parents to ensure the best care for their children.</p>
            </section>
        </a>
        </div>
    </div>
    
    <script>
        let currentSection = 'childs-care';
        function rotateSlider() {
            const slider = document.getElementById('slider');
            const body = document.getElementById('body');
            slider.style.transform = `translateY(-50%) rotate(${currentSection === 'childs-care' ? 180 : 0}deg)`; // Rotate slider
            
            currentSection = currentSection === 'childs-care' ? 'parents-care' : 'childs-care'; // Toggle section
            
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(currentSection).classList.add('active'); // Activate new section
            
            // Change background image based on section
            if (currentSection === 'childs-care') {
                body.style.background = "url('Speakers.png') no-repeat center center/cover";
            } else {
                body.style.background = "url('Home.png') no-repeat center center/cover";
            }
        }
    </script>
</body>
</html>
