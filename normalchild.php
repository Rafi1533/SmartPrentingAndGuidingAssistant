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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Normal Child - Smart Parenting Assistant</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

/* COLORS */
:root {
  --blue-light: rgba(21,101,192,0.85);
  --blue-dark: rgba(10,30,80,0.85);
  --white: #ffffff;
  --black: #000000;
  --text-light: #222222;
  --text-dark: #f0f0f0;
  --shadow-light: rgba(0,0,0,0.2);
  --shadow-dark: rgba(0,0,0,0.8);
  --hover-glow: rgba(58,110,255,0.7);
}

/* RESET */
* { box-sizing: border-box; margin:0; padding:0; font-family:'Poppins',sans-serif; }
body { min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; transition: background 0.5s, color 0.5s; overflow-x:hidden; }

/* BACKGROUND VIDEO */
video.bg-video {
  position: fixed;
  top:0; left:0; width:100%; height:100%;
  object-fit:cover; z-index:-1; opacity:1;
}

/* NAVBAR */
.navbar {
  position:fixed; top:0; left:0; right:0; height:60px;
  display:flex; justify-content:space-between; align-items:center;
  padding:0 30px; font-weight:600; font-size:16px; border-radius:0 0 15px 15px;
  background: rgba(21,101,192,0.85); color:#fff;
  box-shadow:0 4px 15px var(--shadow-light); backdrop-filter:blur(12px);
  z-index:1000; transition:0.5s;
}
body.dark .navbar { background: var(--blue-dark); box-shadow:0 4px 15px var(--shadow-dark);}
.navbar ul { display:flex; gap:20px; list-style:none; align-items:center; }
.navbar ul li a { text-decoration:none; color:#fff; transition:0.3s; position:relative; }
.navbar ul li a:hover::after { width:100%; }
.navbar ul li a:hover { color:#FFD700; transform:scale(1.1); }

#darkModeToggle {
  background:transparent; border:2px solid #fff; border-radius:30px; padding:6px 16px;
  cursor:pointer; font-weight:600; transition:0.3s;
}
#darkModeToggle:hover { background:#fff; color: var(--blue-dark);}
body.dark #darkModeToggle:hover { color: var(--blue-light); }

/* SLIDER CONTAINER */
.slider-container {
  width:80%; max-width:900px; margin-top:100px;
  position:relative; overflow:hidden;
  display:flex; align-items:center; justify-content:center;
  flex-direction:column;
}
.slider {
  display:flex; transition: transform 0.5s ease-in-out;
}
.section {
  width:100%; margin:0 10px; padding:30px;
  border-radius:20px;
  background: rgba(255,255,255,0.25); /* glass effect light mode */
  backdrop-filter: blur(12px);
  box-shadow:0 8px 25px var(--shadow-light);
  color: var(--text-light);
  text-align:left; font-size:18px; display:flex; flex-direction:column; justify-content:center;
  cursor:pointer; transition:0.4s all; flex-shrink:0;
  border: 1px solid rgba(255,255,255,0.3);
}
.section:hover {
  transform: scale(1.05) translateY(-5px);
  box-shadow: 0 0 35px var(--hover-glow);
  background: rgba(255,255,255,0.4);
}
body.dark .section {
  background: rgba(15,32,84,0.7);
  box-shadow: 0 8px 25px var(--shadow-dark);
  color: var(--text-dark);
  border:1px solid rgba(255,255,255,0.1);
}
body.dark .section:hover {
  background: rgba(15,32,84,0.9);
  box-shadow: 0 0 35px var(--hover-glow);
  color: #fff;
}
.section h2 { font-size:28px; margin-bottom:15px; }
.section p { font-size:18px; line-height:1.5; }

/* SLIDER NAVIGATION */
.slider-nav {
  display:flex; justify-content:space-between; width:100%; margin-top:20px;
  z-index:10;
}
.slider-nav button {
  background: rgba(21,101,192,0.8); color:#fff; border:none; padding:10px 22px; border-radius:20px; cursor:pointer; transition:0.3s;
}
.slider-nav button:hover { background: rgba(21,101,192,1); }
body.dark .slider-nav button { background: var(--blue-dark); }
body.dark .slider-nav button:hover { background: var(--blue-light); color: var(--black); }

/* RESPONSIVE */
@media (max-width:768px){
  .slider-container { width:90%; }
  .section { margin:0 0 20px 0; padding:20px; font-size:16px; }
  .slider-nav { flex-direction:row; justify-content:space-between; width:100%; margin-top:10px; }
}
</style>
</head>
<body id="body">

<!-- Background Video -->
<video autoplay muted loop playsinline class="bg-video">
  <source src="normalchild.mp4" type="video/mp4">
</video>

<!-- Navbar -->
<nav class="navbar">
  <span>Smart Parenting Assistant</span>
  <ul>
    <li><a href="parent_dashboard.php">Home</a></li>
    <li><a href="childcare.php">Child's Care</a></li>
    <li><a href="parentcare.html">Parent's Care</a></li>
    <li><a href="parent_logout.php">Logout</a></li>
    <li><button id="darkModeToggle">Dark Mode</button></li>
  </ul>
</nav>

<!-- Slider -->
<div class="slider-container">
  <div class="slider" id="slider">
    <div class="section" onclick="window.location.href='child_emergency.php'">
      <h2>Emergency</h2>
      <p>Learn how to handle emergency situations effectively and ensure the safety of your child.</p>
    </div>
    <div class="section" onclick="window.location.href='counseling.php'">
      <h2>Counseling</h2>
      <p>Access counseling services to help children deal with emotional challenges and psychological growth.</p>
    </div>
  </div>

  <!-- Nav Buttons moved below slider to prevent overlap -->
  <div class="slider-nav">
    <button onclick="moveSlider('prev')">Prev</button>
    <button onclick="moveSlider('next')">Next</button>
  </div>
</div>

<script>
const body = document.getElementById('body');
const darkModeToggle = document.getElementById('darkModeToggle');
const slider = document.getElementById('slider');
const sections = document.querySelectorAll('.section');
let currentSlide = 0;

// Load dark mode preference
if(sessionStorage.getItem('darkMode')==='true'){ 
  body.classList.add('dark'); 
  darkModeToggle.textContent='Light Mode'; 
}

darkModeToggle.addEventListener('click', ()=>{
  body.classList.toggle('dark');
  if(body.classList.contains('dark')){
    darkModeToggle.textContent='Light Mode';
    sessionStorage.setItem('darkMode','true');
  }else{
    darkModeToggle.textContent='Dark Mode';
    sessionStorage.setItem('darkMode','false');
  }
});

function moveSlider(direction){
  const totalSlides = sections.length;
  if(direction==='next') currentSlide = (currentSlide+1)%totalSlides;
  if(direction==='prev') currentSlide = (currentSlide-1+totalSlides)%totalSlides;
  slider.style.transform = `translateX(-${currentSlide*100}%)`;
}
</script>

</body>
</html>
