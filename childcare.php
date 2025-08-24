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
<title>Child's Care - Smart Parenting Assistant</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

/* COLORS */
:root {
  --blue-1: rgba(240, 248, 255, 0.85); /* Light bluish-white for special child */
  --blue-2: rgba(230, 240, 255, 0.85); /* Light bluish-white for normal child */
  --nav-light: rgba(30,58,138,0.85);
  --nav-dark: rgba(10,30,80,0.85);
  --btn-light: rgba(58,110,255,0.8);
  --btn-hover-light: rgba(42,85,210,0.9);
  --btn-dark: rgba(97,140,255,0.8);
  --btn-hover-dark: rgba(138,168,255,0.9);
  --text-light: #fff;
  --text-dark: #00081dff;
  --shadow-light: rgba(0,0,0,0.2);
  --shadow-dark: rgba(0,0,0,0.7);
}

/* RESET */
* { box-sizing: border-box; margin:0; padding:0; font-family:'Poppins',sans-serif; }
body { min-height:100vh; text-align:center; transition: background 0.5s, color 0.5s; position:relative; padding-bottom:80px; overflow-x:hidden; background:#f0f4f8; color:var(--text-dark);}
body.dark { background:#0d1b2a; color:var(--text-light); }

/* BACKGROUND VIDEO */
video.background-video {
  position:fixed; top:0; left:0; width:100%; height:100%;
  object-fit:cover; z-index:-1; opacity:1;
}

/* NAVBAR */
.navbar {
  background: var(--nav-light);
  color: var(--text-light);
  padding:1rem 2rem;
  display:flex;
  justify-content:space-between;
  align-items:center;
  font-size:18px;
  font-weight:600;
  border-radius:0 0 15px 15px;
  box-shadow:0 4px 15px var(--shadow-light);
  backdrop-filter: blur(12px);
  transition: background 0.5s, box-shadow 0.5s;
}
body.dark .navbar { background:var(--nav-dark); box-shadow:0 4px 15px var(--shadow-dark);}
.navbar ul { list-style:none; display:flex; gap:20px; align-items:center; margin:0; padding:0; }
.navbar ul li a { color:var(--text-light); text-decoration:none; transition: color 0.3s, transform 0.3s; position:relative; padding-bottom:3px;}
.navbar ul li a::after { content:''; position:absolute; width:0%; height:2px; bottom:0; left:0; background:#00f0ff; transition:0.3s ease;}
.navbar ul li a:hover::after { width:100%; }

/* DARK MODE TOGGLE */
#darkModeToggle {
  background:transparent; border:2px solid var(--text-light); color:var(--text-light); padding:7px 16px; border-radius:30px;
  cursor:pointer; transition:0.3s; font-weight:600;
}
#darkModeToggle:hover { background:var(--text-light); color:var(--nav-light);}
body.dark #darkModeToggle:hover { color:var(--nav-dark); }

/* BUTTONS */
.button-container { margin-top:100px; }
.btn {
  padding:16px 32px; font-size:18px; font-weight:700; border:none; border-radius:30px; cursor:pointer; margin:20px;
  transition: all 0.3s ease-in-out; box-shadow:0 5px 20px var(--shadow-light); user-select:none;
  background:var(--btn-light); color:white;
}
.btn:hover { transform:scale(1.08); box-shadow:0 8px 25px var(--shadow-light); background:var(--btn-hover-light);}
body.dark .btn { background:var(--btn-dark); box-shadow:0 5px 20px var(--shadow-dark);}
body.dark .btn:hover { background:var(--btn-hover-dark); box-shadow:0 8px 25px var(--shadow-dark); }

/* SECTIONS */
.section {
  display:none; padding:35px 40px; border-radius:20px; width:60%; max-width:700px; margin:40px auto;
  transition: opacity 0.6s ease-in-out, transform 0.4s ease-in-out; opacity:0;
  cursor:pointer; user-select:none;
  background: rgba(255,255,255,0.85); /* Light mode glass solid */
  backdrop-filter: blur(12px);
  border: 2px solid rgba(255,255,255,0.6);
  box-shadow: 0 0 25px rgba(58,110,255,0.5); /* Glow border in light mode */
  color: var(--text-dark);
}
.section:hover { transform: translateY(-8px) scale(1.02); box-shadow:0 0 35px rgba(58,110,255,0.7);}
.section.active { display:block; opacity:1; }

/* LIGHT MODE SPECIFIC COLORS */
#special-child { background: var(--blue-1); color:#1a385f;}
#normal-child { background: var(--blue-2); color:#153973;}

/* DARK MODE */
body.dark #special-child { background: rgba(26,50,90,0.7); color:var(--text-light); box-shadow:0 8px 25px rgba(0,0,0,0.8);}
body.dark #normal-child { background: rgba(20,40,80,0.7); color:var(--text-light); box-shadow:0 8px 25px rgba(0,0,0,0.85);}

.section h2 { font-size:2.5rem; margin-top:0; font-weight:700; letter-spacing:1.2px;}
.section p { font-size:1.2rem; margin-top:15px; line-height:1.5;}

/* RESPONSIVE */
@media (max-width:768px){
  .section { width:90%; padding:30px 20px; }
  .btn { font-size:16px; padding:14px 28px; margin:12px; }
  .navbar { font-size:16px; padding:12px 20px; }
  .navbar ul { gap:12px; }
}
</style>
</head>
<body id="body">

<!-- BACKGROUND VIDEO -->
<video autoplay muted loop playsinline class="background-video">
  <source src="childcare.mp4" type="video/mp4">
</video>

<!-- NAVBAR -->
<nav class="navbar">
  <span>Smart Parenting Assistant</span>
  <ul>
    <li><a href="parent_dashboard.php">Home</a></li>
    <li><a href="parentcare.html">Parent's Care</a></li>
    <li><a href="parent_logout.php">Logout</a></li>
    <li><button id="darkModeToggle">Dark Mode</button></li>
  </ul>
</nav>

<!-- BUTTONS -->
<div class="button-container">
  <button class="btn" onclick="showSection('special-child')">Special Child</button>
  <button class="btn" onclick="showSection('normal-child')">Normal Child</button>
</div>

<!-- SECTIONS -->
<div id="special-child" class="section active" tabindex="0" onclick="window.location.href='specialchild.php'">
  <h2>Special Child</h2>
  <p>Guidance and care specifically designed for special children to promote their development.</p>
</div>

<div id="normal-child" class="section" tabindex="0" onclick="window.location.href='normalchild.php'">
  <h2>Normal Child</h2>
  <p>General care and development tips for normal children to enhance their growth and well-being.</p>
</div>

<script>
const body = document.getElementById('body');
const darkModeToggle = document.getElementById('darkModeToggle');

// Load dark mode preference
if(sessionStorage.getItem('darkMode')==='true'){ 
    body.classList.add('dark'); 
    darkModeToggle.textContent='Light Mode'; 
}

darkModeToggle.addEventListener('click',()=>{
  body.classList.toggle('dark');
  if(body.classList.contains('dark')){ 
      darkModeToggle.textContent='Light Mode'; 
      sessionStorage.setItem('darkMode','true'); 
  } else { 
      darkModeToggle.textContent='Dark Mode'; 
      sessionStorage.setItem('darkMode','false'); 
  }
});

function showSection(sectionId){
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.getElementById(sectionId).classList.add('active');
}
</script>
</body>
</html>
