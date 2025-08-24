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
<title>Autism Types</title>
<style>
/* Base Styles */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    text-align: center;
    color: #333;
    background: linear-gradient(135deg, #f0f2f5, #c3cfe2);
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
    z-index: -2;
    opacity: 0.9;
}
.overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.25);
    z-index: -1;
}

/* Navbar */
.navbar {
    background: linear-gradient(90deg, #ff416c, #6a11cb, #2575fc);
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    border-radius: 0 0 20px 20px;
    position: relative;
    z-index: 1;
}

.navbar span {
    font-size: 20px;
    font-weight: 700;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
}

.navbar ul {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
}

.navbar ul li {
    margin: 0 15px;
}

.navbar ul li a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 8px;
    transition: all 0.3s ease-in-out;
}

.navbar ul li a:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

/* Main Container */
.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 60px;
    z-index: 1;
    position: relative;
}

/* Autism Card Styles */
.autism-card {
    background: rgba(255, 255, 255, 0.85);
    width: 300px;
    margin: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    backdrop-filter: blur(6px);
}

.autism-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
}

.autism-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.autism-card .content {
    padding: 20px;
    text-align: left;
}

.autism-card .content h3 {
    color: #2E3B55;
    font-size: 22px;
    margin-bottom: 10px;
    transition: color 0.3s ease;
}

.autism-card .content p {
    color: #333;
    font-size: 15px;
    line-height: 1.5;
}

.autism-card:hover .content h3 {
    color: #ff416c;
}

/* View Therapy Button */
.view-therapy-btn {
    display: inline-block;
    background: linear-gradient(135deg, #ff416c, #6a11cb);
    color: white;
    padding: 14px 30px;
    font-size: 16px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    text-decoration: none;
    margin: 40px 0;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.view-therapy-btn:hover {
    background: linear-gradient(135deg, #ff6b81, #8a2be2);
    transform: scale(1.05) translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

/* Responsive */
@media screen and (max-width: 768px) {
    .navbar {
        flex-direction: column;
    }
    .navbar ul {
        flex-direction: column;
        margin-top: 10px;
    }
    .navbar ul li {
        margin: 8px 0;
    }
    .container {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</head>
<body>

<!-- Background Video -->
<video autoplay muted loop id="bg-video">
    <source src="parenthome.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>
<div class="overlay"></div>

<!-- Navbar -->
<div class="navbar">
    <span>Autism Types</span>
    <ul>
        <li><a href="parent_dashboard.php">Home</a></li>
        <li><a href="specialchild.php">Special Child Home</a></li>
        <li><a href="parent_logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="container" id="autism-container">
    <!-- Cards dynamically inserted by JS -->
</div>

<!-- View Therapy Button -->
<a href="therapy.php" class="view-therapy-btn">View Therapy</a>

<script>
// Sample data
const autismData = [
    {
        name: 'Childhood Disintegrative Disorder (CDD)',
        description: 'Childhood Disintegrative Disorder (CDD) is a rare developmental disorder that affects children aged 2 to 10 years. It is also known as Heller’s syndrome, and it is characterized by a regression in developmental milestones, which were previously acquired by the child. This disorder is considered a severe form of autism spectrum disorder (ASD), and it affects both boys and girls equally.',
        image_url: 'cdd.jpg'
    },
    {
        name: 'Pervasive Developmental Disorder',
        description: 'It is a mild type of autism that presents a range of symptoms. The most common symptoms are challenges in social and language development.child may experience delays in language development, walking, and other motor skills.',
        image_url: 'type2.jpg'
    },
    {
        name: 'Rett Syndrome',
        description: 'Rett syndrome is a rare neurodevelopmental disorder that is noticed in infancy. The disorder mostly affects girls, although it can still be diagnosed in boys. Rett syndrome presents challenges that affect almost every aspect of a childs life. The good thing is your child can still enjoy and live a fulfilling life with the proper care. You can have family time together and provide support to allow the child to do what they enjoy. ',
        image_url: 'type3.jpg'
    }
];

function displayAutismTypes() {
    const container = document.getElementById('autism-container');
    autismData.forEach(autism => {
        const card = document.createElement('div');
        card.classList.add('autism-card');
        card.innerHTML = `
            <img src="${autism.image_url}" alt="${autism.name}">
            <div class="content">
                <h3>${autism.name}</h3>
                <p>${autism.description}</p>
            </div>
        `;
        container.appendChild(card);
    });
}

displayAutismTypes();
</script>
</body>
</html>
