<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Panel</title>
<!-- Google Fonts for icons -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<style>
:root {
  --primary-gradient-light: linear-gradient(90deg, #8e44ad, #3498db);
  --primary-gradient-dark: linear-gradient(180deg,#2c2c44,#3f4c6b);
  --bg-light: #f4f4f4;
  --bg-dark: #1e1e2f;
  --text-light: #1e1e2f;
  --text-dark: #fff;
  --section-bg-light: rgba(255,255,255,0.8);
  --section-bg-dark: rgba(255,255,255,0.05);

  --sidebar-width: 280px;
  --sidebar-collapsed-width: 70px;
  --border-radius: 12px;
  --transition: 0.3s ease;
  --shadow-light: rgba(0,0,0,0.12);
  --shadow-hover: rgba(0,0,0,0.25);
  --icon-size: 28px;
  --dark-btn-size:40px;
}

/* Reset */
*, *::before, *::after { box-sizing: border-box; }
body, html { margin:0; padding:0; font-family:'Poppins',sans-serif; height:100%; transition: background-color var(--transition), color var(--transition); }

/* Background Video */
#bgVideo {
  position: fixed;
  top:0; left:0;
  width:100%; height:100%;
  object-fit:cover;
  z-index:-1;
  filter: brightness(1);
  transition: filter 0.3s ease;
}
body.dark #bgVideo { filter: brightness(0.25); }

/* Sidebar */
.sidebar {
  width: var(--sidebar-width);
  background: var(--primary-gradient-light);
  color: #fff;
  flex-direction: column;
  padding: 30px 20px;
  position: fixed;
  height: 100vh;
  overflow-y:auto;
  transition: width var(--transition), background var(--transition);
  border-right: 1px solid rgba(255,255,255,0.2);
  backdrop-filter: blur(6px);
  display:flex;
  align-items:center;
  z-index: 10;
}
.sidebar.collapsed { width: var(--sidebar-collapsed-width); }

/* Sidebar toggle button */
.sidebar-toggle {
  position: absolute;
  top: 20px;
  right: -20px;
  background: rgba(255,255,255,0.25);
  border:none;
  color:#fff;
  width:var(--dark-btn-size);
  height:var(--dark-btn-size);
  border-radius:50%;
  cursor:pointer;
  font-weight:700;
  font-size:20px;
  display:flex;
  align-items:center;
  justify-content:center;
  transition: transform var(--transition), background var(--transition);
  z-index:1000;
}
.sidebar-toggle:hover { background: rgba(255,255,255,0.45); transform: rotate(180deg); }

/* Sidebar profile */
.sidebar img { width: 140px; height:140px; border-radius:50%; object-fit:cover; margin-bottom:15px; transition: transform var(--transition), opacity var(--transition); }
.sidebar.collapsed img { opacity:0; width:0; height:0; margin:0; }
.sidebar h2 { font-size:24px; margin-bottom:25px; transition: opacity var(--transition); text-align:center; }
.sidebar.collapsed h2 { opacity:0; }

/* Menu */
.menu { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; width:100%; }
.menu li { position:relative; }
.menu li a {
  display:flex;
  align-items:center;
  gap:15px;
  padding:12px 20px;
  text-decoration:none;
  color:#fff;
  font-weight:600;
  border-radius:var(--border-radius);
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(6px);
  box-shadow:0 4px 12px rgba(0,0,0,0.15);
  transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
}
.menu li a:hover, .menu li a:focus { background: rgba(255,255,255,0.2); transform: translateX(5px); outline:none; }

/* Icons */
.menu li a .material-symbols-outlined { font-size: var(--icon-size); }
.sidebar.collapsed li a span.text { display:none; justify-content:center; text-align:center; }

/* Dropdown */
.menu li.has-dropdown > a::after { content:"▼"; margin-left:auto; transition: transform var(--transition);}
.menu li.has-dropdown.open > a::after { transform: rotate(-180deg);}
.submenu { list-style:none; padding-left:20px; max-height:0; overflow:hidden; transition:max-height 0.35s ease; }
.menu li.has-dropdown.open .submenu { max-height:300px; }
.submenu li a { font-weight:500; padding:8px 15px; background: rgba(255,255,255,0.1); margin-bottom:6px; border-radius:var(--border-radius); }
.submenu li a:hover, .submenu li a:focus { background: rgba(255,255,255,0.2); outline:none; }

/* Main content */
.main-content {
  margin-left: var(--sidebar-width);
  padding:40px 50px;
  display:flex;
  flex-wrap:wrap;
  gap:25px;
  transition: margin-left var(--transition);
}
.sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed-width); }

/* Feature cards with 3D effect */
.section {
  flex:1 1 280px;
  min-width:200px;
  padding:30px;
  border-radius:var(--border-radius);
  text-align:center;
  font-weight:600;
  font-size:18px;
  cursor:pointer;
  transition: transform var(--transition), box-shadow var(--transition), background var(--transition), color var(--transition);
  background: var(--section-bg-light);
  backdrop-filter: blur(12px) saturate(180%);
  color: var(--text-light);
  position: relative;
  overflow: hidden;
  transform-style: preserve-3d;
}
.section::before {
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(120deg, rgba(255,255,255,0.1), rgba(0,0,0,0.1));
  opacity:0.3;
  z-index:0;
  transition: all var(--transition);
}
.section:hover, .section:focus {
  transform: rotateX(5deg) rotateY(5deg) scale(1.08);
  box-shadow:0 15px 40px rgba(0,0,0,0.4);
}
.section span { position:relative; z-index:1; }

/* Section gradients */
.section-view-emergencies{ background: linear-gradient(90deg,#e74c3c,#ff6f61);}
.section-add-counseling{ background: linear-gradient(90deg,#8e44ad,#9b59b6);}
.section-add-therapy{ background: linear-gradient(90deg,#2ecc71,#27ae60);}
.section-shop-management{ background: linear-gradient(90deg,#f39c12,#f1c40f);}
.section-teaching-aid{ background: linear-gradient(90deg,#3498db,#2980b9);}
.section-achievements{ background: linear-gradient(90deg,#1abc9c,#16a085);}
.section-child-care{ background: linear-gradient(90deg,#9b59b6,#8e44ad);}
.section-maternity-care{ background: linear-gradient(90deg,#34495e,#2c3e50);}

/* Dark mode */
body.dark {
  background: var(--bg-dark);
  color: var(--text-dark);
}
body.dark .sidebar { background: var(--primary-gradient-dark);}
body.dark .section {
  background: var(--section-bg-dark);
  color: var(--text-dark);
}

/* Dark mode button as sun/moon icon */
.dark-mode-toggle {
  position:absolute;
  bottom:30px;
  width:var(--dark-btn-size);
  height:var(--dark-btn-size);
  border:none;
  border-radius:50%;
  background: rgba(255,255,255,0.25);
  color:#fff;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:20px;
  transition: transform var(--transition), background var(--transition);
}
.dark-mode-toggle:hover { background: rgba(255,255,255,0.45); }

/* Responsive */
@media (max-width:768px){
  .sidebar{width:220px;}
  .main-content{margin-left:220px; gap:20px;}
  .section{flex:1 1 100%;}
}
@media (max-width:480px){
  .sidebar{position:relative;width:100%;height:auto;flex-direction:row;padding:15px;box-shadow:none;}
  .sidebar img{width:50px;height:50px;margin:0;}
  .sidebar h2{display:none;}
  .main-content{margin-left:0;padding:20px;flex-direction:column;gap:20px;}
}
</style>
</head>
<body>

<video autoplay muted loop id="bgVideo" playsinline>
  <source src="adminbg.mp4" type="video/mp4">
</video>

<aside class="sidebar" aria-label="Sidebar navigation">
  <button class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
  <img src="<?php echo htmlspecialchars($_SESSION['admin_photo']); ?>" alt="Admin Photo" />
  <h2><?php echo htmlspecialchars($_SESSION['admin_first_name']); ?></h2>

  <ul class="menu" role="menu">
    <li><a href="admin_dashboard.php"><span class="material-symbols-outlined">dashboard</span><span class="text">Dashboard</span></a></li>
    <li><a href="admin_logout.php"><span class="material-symbols-outlined">logout</span><span class="text">Logout</span></a></li>
  </ul>

  <button class="dark-mode-toggle" aria-label="Toggle dark mode">🌙</button>
</aside>

<main class="main-content" role="main">
  <div class="section section-view-emergencies" tabindex="0" onclick="location.href='admin_emergency.php'"><span>View Emergencies</span></div>
  <div class="section section-add-counseling" tabindex="0" onclick="location.href='admin_counseling.php'"><span>Add Counseling Sessions For Normal Kids</span></div>
  <div class="section section-add-therapy" tabindex="0" onclick="location.href='admin_therapy.php'"><span>Add Therapy for Autism</span></div>
  <div class="section section-achievements" tabindex="0" onclick="location.href='adv_admin_counseling.php'"><span>Add Special Child Counseling</span></div>
  <div class="section section-shop-management" tabindex="0" onclick="location.href='admin_shop.php'"><span>Shop Management</span></div>
  <div class="section section-teaching-aid" tabindex="0" onclick="location.href='admin_teaching_aid.php'"><span>Teaching Aid Management</span></div>
  <div class="section section-achievements" tabindex="0" onclick="location.href='admin_achievement.php'"><span>Add Achievement Stories</span></div>
  <div class="section section-child-care" tabindex="0" onclick="location.href='maternity_admin_dashboard.php'"><span>Manage Maternity Tasks</span></div>
  <div class="section section-maternity-care" tabindex="0" onclick="location.href='parent_care_doctors_admin.php'"><span>Add Doctors for Parent Care</span></div>
  <div class="section section-teaching-aid" tabindex="0" onclick="location.href='admin_parent_child_care_guideline.php'"><span>Manage Parent Care Guideline</span></div>
  <div class="section section-teaching-aid" tabindex="0" onclick="location.href='admin_parent_counseling.php'"><span>Manage Parent Counseling</span></div>
</main>

<script>
// Sidebar toggle
const sidebar = document.querySelector('.sidebar');
const sidebarToggleBtn = document.querySelector('.sidebar-toggle');
sidebarToggleBtn.addEventListener('click', ()=> sidebar.classList.toggle('collapsed'));

// Dark mode toggle
const darkModeBtn = document.querySelector('.dark-mode-toggle');
const body = document.body;
const savedTheme = localStorage.getItem('theme');
if(savedTheme==='dark'){ body.classList.add('dark'); darkModeBtn.textContent='☀️'; }
darkModeBtn.addEventListener('click', ()=>{
  const isDark = body.classList.toggle('dark');
  darkModeBtn.textContent = isDark?'☀️':'🌙';
  localStorage.setItem('theme', isDark?'dark':'light');
});

// Activate sections via keyboard
document.querySelectorAll('.section').forEach(section=>{
  section.addEventListener('keydown', e=>{
    if(e.key==='Enter'||e.key===' '){ e.preventDefault(); section.click(); }
  });
});
</script>

</body>
</html>
