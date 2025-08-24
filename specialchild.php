<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: parent_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Special Child Care</title>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* Reset & base */
    * { box-sizing: border-box; }
    html,body { height:100%; }
    body {
      margin: 0;
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: var(--bg);
      color: var(--text);
      transition: background-color 0.45s ease, color 0.45s ease;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      min-height: 100vh;
      overflow-x: hidden;
    }

    :root{
      --bg: #eef4ff;
      --overlay: rgba(11,24,55,0.45);
      --card-bg: rgba(255,255,255,0.6);
      --glass-blue-bg: rgba(13,56,160,0.14);
      --text: #0b2038;
      --text-muted: #556577;
      --link: #1e40af;
      --btn-bg: linear-gradient(90deg,#2563eb,#1e40af);
      --glass-border: rgba(255,255,255,0.5);
      --glass-blur: 8px;
      --card-shadow: 0 8px 30px rgba(16,24,40,0.08);
      --accent: #2563eb;
    }
    body.dark{
      --bg: #071028;
      --overlay: rgba(2,6,23,0.55);
      --card-bg: rgba(30,41,59,0.55);
      --glass-blue-bg: rgba(37,60,130,0.18);
      --text: #e6eefc;
      --text-muted: #9fb0d6;
      --link: #60a5fa;
      --btn-bg: linear-gradient(90deg,#1e40af,#2563eb);
      --glass-border: rgba(255,255,255,0.06);
      --glass-blur: 10px;
      --card-shadow: 0 10px 40px rgba(2,6,23,0.6);
      --accent: #60a5fa;
    }

    /* background video container */
    .bg-video-wrap{
      position: fixed; inset:0; z-index: -2; overflow:hidden;
      display:flex; align-items:center; justify-content:center;
    }
    .bg-video-wrap video{
      min-width: auto; min-height: 100%; width: 100%; height: 100%;
      position: absolute; left:50%; top:50%; transform: translate(-50%,-50%);
      object-fit: cover; filter: brightness(0.75) saturate(1.05);
      transition: filter 0.4s ease;
    }
    body.dark .bg-video-wrap video{ filter: brightness(0.45) saturate(1) contrast(0.95); }

    /* subtle overlay on top of video for tint */
    .bg-overlay{
      position: fixed; inset:0; z-index:-1; pointer-events:none;
      mix-blend-mode: overlay;
      transition: background 0.45s ease;
    }
    body.dark .bg-overlay{ background: linear-gradient(180deg, rgba(1,8,20,0.45), rgba(4,10,30,0.55)); }

    /* Navbar */
    .navbar {
      backdrop-filter: blur(var(--glass-blur));
      background: linear-gradient(90deg, rgba(0, 157, 255, 0.45), rgba(255, 255, 255, 1));
      border: 1px solid var(--glass-border);
      color: var(--text);
      padding: 0.85rem 1.25rem;
      display: flex; justify-content: space-between; align-items: center;
      box-shadow: var(--card-shadow);
      border-radius: 10px;
      max-width: 1200px; margin: 1.25rem auto 0; position: relative; z-index:5;
    }
    body.dark .navbar{ background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); }
    .navbar .brand{ font-weight:800; letter-spacing:0.6px; font-size:1.25rem; display:flex; gap:0.6rem; align-items:center; }
    .navbar .brand .logo-blob{ width:38px; height:38px; border-radius:10px; display:inline-block; background: linear-gradient(135deg,var(--accent),#6fb0ff); box-shadow: 0 6px 18px rgba(37,99,235,0.18); }
    .navbar nav{ display:flex; gap:1rem; align-items:center; }
    .navbar a{ color:var(--text); font-weight:600; text-decoration:none; padding:6px 10px; border-radius:8px; }
    .navbar a:hover{ background: rgba(255,255,255,0.06); }

    #darkModeToggle{
      background: transparent; border: 1px solid var(--glass-border); color:var(--text); padding:6px 12px; border-radius:20px; cursor:pointer; font-weight:700;
    }
    #darkModeToggle:focus{ outline:2px solid rgba(99,102,241,0.18); }

    /* Dashboard hero */
    .hero{
      max-width:1200px; margin: 1.25rem auto; padding: 1rem; display:flex; gap:1.25rem; align-items:center; z-index:4; position:relative;
    }
    .hero-left{
      flex:1 1 420px; background:var(--card-bg); border-radius:14px; padding:20px; box-shadow: var(--card-shadow); border:1px solid var(--glass-border); backdrop-filter: blur(var(--glass-blur));
      transform-origin:center; transition: transform 0.35s cubic-bezier(.2,.9,.2,1), box-shadow 0.35s;
    }
    .hero-left:hover{ transform: translateY(-6px) scale(1.01); }
    .hero-left h2{ margin:0 0 8px; font-size:1.35rem; }
    .hero-left p{ margin:0 0 12px; color:var(--text-muted); }

    .hero-right{ width:320px; display:flex; flex-direction:column; gap:12px; }
    .stat{ padding:14px; border-radius:12px; background: linear-gradient(180deg, rgba(255,255,255,0.45), rgba(255,255,255,0.18)); border:1px solid var(--glass-border); backdrop-filter: blur(var(--glass-blur)); box-shadow: var(--card-shadow); }
    .stat h4{ margin:0; font-size:1rem; }
    .stat p{ margin:6px 0 0; color:var(--text-muted); }

    /* Main grid container */
    .main-container {
      max-width: 1100px;
      margin: 1rem auto 4rem;
      padding: 0 1rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 22px;
      justify-items: center;
      z-index:4; position:relative;
    }

    /* Section card - glass styles */
    .section-card {
      border-radius: 16px;
      padding: 22px;
      width: 100%; max-width: 380px; min-height: 220px;
      text-align: left;
      box-shadow: var(--card-shadow);
      cursor: pointer;
      transition: transform 0.36s cubic-bezier(.2,.9,.2,1), box-shadow 0.36s, border-color 0.36s;
      display: flex; flex-direction: column; gap:12px; border:1px solid var(--glass-border);
      user-select: none; position:relative; overflow:hidden;
      backdrop-filter: blur(var(--glass-blur));
    }

    .section-card:before{
      content:''; position:absolute; inset:0; background: linear-gradient(120deg, rgba(255,255,255,0.02), rgba(255,255,255,0.035)); pointer-events:none; mix-blend-mode:overlay;
    }

    .section-card.glass-white{ background: linear-gradient(180deg, rgba(255,255,255,0.66), rgba(255,255,255,0.44)); }
    .section-card.glass-blue{ background: linear-gradient(180deg, rgba(17,48,122,0.12), rgba(37,99,235,0.08)); color:var(--text); }

    .section-card h3{ margin:0; font-size:1.25rem; }
    .section-card p{ margin:0; color:black; line-height:1.4; }

    /* animated hover accent */
    .section-card .btn{ margin-top:auto; align-self:flex-start; padding:10px 18px; border-radius:999px; border:none; font-weight:800; cursor:pointer; background:var(--btn-bg); color:white; box-shadow: 0 10px 30px rgba(37,99,235,0.18); transition:transform 0.28s ease, box-shadow 0.28s ease; }
    .section-card:hover .btn{ transform: translateY(-3px); box-shadow: 0 18px 40px rgba(37,99,235,0.26); }

    .section-card:hover{ transform: translateY(-8px); box-shadow: 0 30px 70px rgba(16,30,80,0.18); }

    /* sheen animation on hover */
    .section-card .sheen{ position:absolute; top:-40%; left:-30%; width:60%; height:180%; transform: rotate(25deg); background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.18), rgba(255,255,255,0.03)); transition: transform 0.9s ease; pointer-events:none; }
    .section-card:hover .sheen{ transform: translateX(220%) rotate(25deg); }

    /* small icon */
    .kicker{ display:inline-flex; gap:10px; align-items:center; }
    .kicker .ico{ width:44px; height:44px; border-radius:10px; display:inline-grid; place-items:center; font-weight:800; }
    .glass-white .ico{ background: rgba(255,255,255,0.45); border:1px solid rgba(255,255,255,0.4); }
    .glass-blue .ico{ background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.04); }
    .btn{ margin-top:auto; align-self:flex-start; padding:10px 18px; border-radius:999px; border:none; font-weight:800; cursor:pointer; background:var(--btn-bg); color:white; box-shadow: 0 10px 30px rgba(37,99,235,0.18); transition:transform 0.28s ease, box-shadow 0.28s ease; }
    .btn{ transform: translateY(-3px); box-shadow: 0 18px 40px rgba(37,99,235,0.26); }

    /* responsive tweaks */
    @media (max-width: 880px){ .hero{ flex-direction:column; } .hero-right{ width:100%; } }
    @media (max-width:480px){ .navbar{ margin: 0.6rem 0.6rem 0; padding:0.7rem; border-radius:8px; } .hero{ margin:0.9rem 0.9rem; } .main-container{ margin-top:0.8rem; padding:0.6rem; }
    }

    /* focus-visible accessibility */
    .section-card:focus{ outline: 3px solid rgba(37,99,235,0.14); transform: translateY(-6px); }

    /* dashboard footer */
    .footer{ max-width:1200px; margin: 2rem auto; text-align:center; color:var(--text-muted); z-index:4; position:relative; }

  </style>
</head>
<body>
  <!-- Background video - replace assets/bg-video.mp4 with your path. Provide multiple formats if possible -->
  <div class="bg-video-wrap" aria-hidden="true">
    <video id="bgVideo" autoplay muted loop playsinline poster="assets/bg-poster.jpg">
      <source src="specialhome.mp4" type="video/mp4">
      <!-- fallback image if video unsupported -->
    </video>
  </div>
  <div class="bg-overlay" aria-hidden="true"></div>

  <!-- Navbar -->
  <nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="brand"><span class="logo-blob" aria-hidden="true"></span><span>Special Child Care</span></div>
    <div>
      <nav>
        <a href="parent_dashboard.php">Home</a>
        <a href="childcare.php">Child Care</a>
        <a href="parent_logout.php">Logout</a>
        <button id="darkModeToggle" aria-pressed="false" aria-label="Toggle dark mode">Dark Mode</button>
      </nav>
    </div>
  </nav>

  <!-- Hero / Dashboard top -->
  <header class="hero" role="banner">
    <div class="hero-left" tabindex="0">
      <h2>Welcome back!</h2>
      <p>Find tools, therapies, achievement stories, and games tailored for special children. Use the sections below to navigate quickly.</p>
      <div style="display:flex; gap:10px; margin-top:12px;">
        <button class="btn" onclick="location.href='achievement_upload.php'">Share a Story</button>
        <button class="btn" style="background:transparent; color:var(--text); box-shadow:none; border:1px solid var(--glass-border);" onclick="location.href='adv_counseling.php'">Book Counseling</button>
      </div>
    </div>
    <aside class="hero-right" aria-label="Quick stats">
      <div class="stat">
        <h4>Active Programs</h4>
        <p>12 ongoing therapies & workshops</p>
      </div>
      <div class="stat">
        <h4>New Resources</h4>
        <p>5 teaching aids added this month</p>
      </div>
    </aside>
  </header>

  <!-- Main Content -->
  <main class="main-container" role="main">
    <section id="disability" class="section-card glass-white" tabindex="0" role="button" aria-label="Autism Screening Questionnaire" onclick="location.href='autism_quiz.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">AS</div><h3>Autism Screening Questionnaire</h3></div>
      <p>Identify disabilities and care. Quick, friendly screening for early signs.</p>
      <button class="btn" type="button" onclick="location.href='autism_quiz.php'; event.stopPropagation();">Learn More</button>
    </section>

    <section id="autism" class="section-card glass-blue" tabindex="0" role="button" aria-label="Autism guidance and therapy" onclick="location.href='autism.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">AU</div><h3>Autism</h3></div>
      <p>Guidance, therapy plans and activity suggestions tailored for children with autism.</p>
      <button class="btn" type="button" onclick="location.href='autism.php'; event.stopPropagation();">Check for Therapy</button>
    </section>

    <section id="counseling" class="section-card glass-white" tabindex="0" role="button" aria-label="Counseling services" onclick="location.href='adv_counseling.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">CS</div><h3>Counseling</h3></div>
      <p>Providing counseling services for children and parents. Book private or group sessions.</p>
      <button class="btn" type="button" onclick="location.href='adv_counseling.php'; event.stopPropagation();">Get your Session Now</button>
    </section>

    <section id="teaching-aid" class="section-card glass-blue" tabindex="0" role="button" aria-label="Teaching aids" onclick="location.href='user_teaching_aid_store.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">TA</div><h3>Teaching Aid</h3></div>
      <p>Resources and multisensory aids to help teaching special children at home or school.</p>
      <button class="btn" type="button" onclick="location.href='user_teaching_aid_store.php'; event.stopPropagation();">Buy Our TA</button>
    </section>

    <section id="shop" class="section-card glass-white" tabindex="0" role="button" aria-label="Shop products" onclick="location.href='user_shop.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">TS</div><h3>Toy Shop</h3></div>
      <p>Shop products that support special child care and daily living aids for children.</p>
      <button class="btn" type="button" onclick="location.href='user_shop.php'; event.stopPropagation();">Shop Now</button>
    </section>

    <section id="achievement" class="section-card glass-blue" tabindex="0" role="button" aria-label="Achievement stories" onclick="location.href='achievement_stories.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">AS</div><h3>Achievement Story</h3></div>
      <p>Stories of success from special children. Read and be inspired by real journeys of progress.</p>
      <button class="btn" type="button" onclick="location.href='achievement_stories.php'; event.stopPropagation();">Read Stories</button>
    </section>

    <section id="upload-achievement" class="section-card glass-white" tabindex="0" role="button" aria-label="Upload achievement story" onclick="location.href='achievement_upload.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">UP</div><h3>Upload Achievement Story</h3></div>
      <p>Upload your stories of success — short text, images or video. Share hope with other parents.</p>
      <button class="btn" type="button" onclick="location.href='achievement_upload.php'; event.stopPropagation();">Upload</button>
    </section>

    <section id="gamification" class="section-card glass-blue" tabindex="0" role="button" aria-label="Gamification game for ASD" onclick="location.href='color_match_game.php'">
      <div class="sheen"></div>
      <div class="kicker"><div class="ico">GM</div><h3>Gamification</h3></div>
      <p>Interactive games to improve color recognition and attention skills for children with ASD.</p>
      <button class="btn" type="button" onclick="location.href='color_match_game.php'; event.stopPropagation();">Start Playing (Demo)</button>
    </section>
  </main>

  <footer class="footer"> - </footer>

  <script>
    // keyboard support for section cards
    document.querySelectorAll('.section-card').forEach(card => {
      card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
      });
    });

    // Dark mode toggle with persistence
    const toggleBtn = document.getElementById('darkModeToggle');
    const bodyEl = document.body;
    const bgVideo = document.getElementById('bgVideo');

    // Initialize
    const saved = localStorage.getItem('special_dark_mode');
    if (saved === 'true') { bodyEl.classList.add('dark'); toggleBtn.textContent = 'Light Mode'; toggleBtn.setAttribute('aria-pressed','true'); }

    toggleBtn.addEventListener('click', () => {
      bodyEl.classList.toggle('dark');
      const dark = bodyEl.classList.contains('dark');
      toggleBtn.textContent = dark ? 'Light Mode' : 'Dark Mode';
      toggleBtn.setAttribute('aria-pressed', dark ? 'true' : 'false');
      localStorage.setItem('special_dark_mode', dark ? 'true' : 'false');
    });

    // Improve perceived performance: pause video on low-power devices or when reduced motion is enabled
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const saveData = navigator.connection && navigator.connection.saveData;
    if (prefersReducedMotion || saveData) {
      bgVideo.pause(); bgVideo.style.display = 'none';
      // optionally show a static background color/image
    }

    // Tiny accessibility: allow pressing 'V' to toggle video sound (muted by default)
    document.addEventListener('keydown', (e) => {
      if (e.key.toLowerCase() === 'v') {
        if (bgVideo.muted) { bgVideo.muted = false; bgVideo.volume = 0.4; }
        else { bgVideo.muted = true; }
      }
    });

    // Ensure buttons inside cards don't bubble
    document.querySelectorAll('.section-card .btn').forEach(b=>b.addEventListener('click', e=>e.stopPropagation()));

  </script>
</body>
</html>
