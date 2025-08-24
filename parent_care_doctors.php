<?php
// parent_care_doctors.php (user side)
session_start();
include 'db.php';

$search = trim($_GET['q'] ?? '');

// fetch
if ($search !== '') {
  $like = "%{$search}%";
  $stmt = $conn->prepare("SELECT * FROM Parent_portal_doctors 
                          WHERE name LIKE ? OR designation LIKE ? 
                          ORDER BY created_at DESC");
  $stmt->bind_param("ss", $like, $like);
  $stmt->execute();
  $doctors = $stmt->get_result();
  $stmt->close();
} else {
  $doctors = $conn->query("SELECT * FROM Parent_portal_doctors ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Parent Care — Our Doctors</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root {
    --blue:#0a74da; --blue-dark:#0a5bb0; --bg:#f7fbff; --card:#fff; --text:#0a3d62; --muted:#6c7a89;
    --border:#e3eef9; --shadow:rgba(10,116,218,.12);
  }
  *{box-sizing:border-box}
  body{margin:0; font-family:'Poppins', system-ui; background:var(--bg); color:var(--text)}
  .navbar{
    background:var(--blue); color:#fff; padding:14px 24px; display:flex; justify-content:space-between; align-items:center;
    box-shadow:0 8px 18px var(--shadow);
  }
  .navbar .brand{font-weight:700; letter-spacing:.3px}
  .navbar a{
    color:#fff; text-decoration:none; margin-left:16px; font-weight:600; padding:8px 12px; border-radius:8px; transition:.25s;
  }
  .navbar a:hover{ background:rgba(255,255,255,.12); transform:translateY(-1px);}
  .container{max-width:1100px;margin:28px auto; padding:0 16px 60px;}
  h1{margin:0 0 14px; font-size:1.6rem}
  .toolbar{
    display:flex; gap:10px; align-items:center; margin: 10px 0 18px;
  }
  .search{
    flex:1; position:relative;
  }
  .search input{
    width:100%; padding:12px 42px 12px 14px; border:1px solid var(--border); border-radius:12px; background:#fff;
    outline:none; transition: box-shadow .2s ease, border-color .2s ease;
  }
  .search input:focus{border-color:var(--blue); box-shadow:0 0 0 4px rgba(10,116,218,.12);}
  .grid{
    display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:18px;
  }
  .card{
    background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; cursor:pointer;
    box-shadow:0 10px 24px var(--shadow); transition:.25s; display:flex; flex-direction:column; align-items:center; text-align:center;
  }
  .card:hover{ transform: translateY(-4px); box-shadow:0 14px 30px var(--shadow); }
  .photo{ width:86px; height:86px; border-radius:50%; object-fit:cover; border:1px solid var(--border); margin-bottom:10px; }
  .name{ font-weight:700; }
  .designation{ color:var(--muted); font-size:.95rem; margin-top:4px; }
  .muted{ color:var(--muted); font-size:.9rem; }

  /* Modal */
  .modal {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    display:none; align-items:center; justify-content:center; padding:20px;
  }
  .modal.open { display:flex; }
  .modal-card {
    background:#fff; width:min(720px, 95vw); max-height: 90vh; overflow:auto;
    border-radius:16px; box-shadow:0 18px 40px rgba(0,0,0,.28); border:1px solid var(--border);
  }
  .modal-header {
    display:flex; align-items:center; justify-content:space-between; padding:16px 18px; border-bottom:1px solid var(--border);
    position: sticky; top:0; background:#fff; z-index: 1;
  }
  .close-btn {
    appearance:none; border:none; background:transparent; font-size:22px; cursor:pointer; padding:6px 10px; border-radius:8px;
  }
  .close-btn:hover { background:#f3f7fc; }
  .modal-body { padding: 18px; }
  .modal-doc { display:flex; gap:18px; align-items:flex-start; flex-wrap:wrap; }
  .modal-doc img { width:120px; height:120px; border-radius:12px; object-fit:cover; border:1px solid var(--border); }
  .field { margin: 8px 0; }
  .btn {
    appearance:none; border:1px solid transparent; padding:12px 16px; border-radius:10px; font-weight:700; cursor:pointer; transition:.25s;
    display:inline-block; text-decoration:none; font-size:.98rem;
  }
  .btn-primary { background: var(--blue); color:#fff; }
  .btn-primary:hover { background: var(--blue-dark); transform: translateY(-1px); }
  .btn-outline { border-color: var(--blue); color: var(--blue); background:#fff; }
  .btn-outline:hover { background: rgba(10,116,218,.08); }

  .empty{
    background:#fff; border:1px dashed var(--border); border-radius:14px; padding:24px; text-align:center; color:var(--muted);
  }
</style>
</head>
<body>

<nav class="navbar">
  <div class="brand">Parent Care — Our Doctors</div>
  <div>
    <a href="parent_dashboard.php">Home</a>
  </div>
</nav>

<div class="container">
  <h1>Our Doctors</h1>

  <form class="toolbar" method="GET">
    <div class="search">
      <input type="text" name="q" placeholder="Search by name or designation..." value="<?php echo htmlspecialchars($search); ?>" />
    </div>
    <button class="btn btn-outline" type="submit">Search</button>
    <a class="btn btn-primary" href="parent_care_doctors.php">Reset</a>
  </form>

  <?php if ($doctors->num_rows === 0): ?>
    <div class="empty">No doctors found.</div>
  <?php else: ?>
    <div class="grid" id="doctorGrid">
      <?php while ($d = $doctors->fetch_assoc()): ?>
        <div class="card"
             data-name="<?php echo htmlspecialchars($d['name']); ?>"
             data-desig="<?php echo htmlspecialchars($d['designation']); ?>"
             data-email="<?php echo htmlspecialchars($d['email']); ?>"
             data-contact="<?php echo htmlspecialchars($d['contact']); ?>"
             data-details="<?php echo htmlspecialchars($d['details']); ?>"
             data-photo="<?php echo ($d['photo'] && file_exists($d['photo'])) ? htmlspecialchars($d['photo']) : ''; ?>">
          <?php if ($d['photo'] && file_exists($d['photo'])): ?>
            <img class="photo" src="<?php echo htmlspecialchars($d['photo']); ?>" alt="">
          <?php else: ?>
            <div class="photo" style="display:flex;align-items:center;justify-content:center;background:#eef5ff;color:#7aa9e8;">N/A</div>
          <?php endif; ?>
          <div class="name"><?php echo htmlspecialchars($d['name']); ?></div>
          <div class="designation"><?php echo htmlspecialchars($d['designation']); ?></div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal" id="doctorModal" aria-hidden="true">
  <div class="modal-card">
    <div class="modal-header">
      <strong id="mName">Doctor</strong>
      <button class="close-btn" id="mClose" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-doc">
        <img id="mPhoto" src="" alt="" />
        <div>
          <div class="field"><strong>Designation:</strong> <span id="mDesig"></span></div>
          <div class="field"><strong>Email:</strong> <a id="mEmail" href="#" target="_blank" rel="noopener"></a></div>
          <div class="field"><strong>Contact:</strong> <span id="mContact"></span></div>
        </div>
      </div>
      <div class="field" style="margin-top:14px;">
        <strong>Details:</strong>
        <div id="mDetails" style="margin-top:6px; line-height:1.55;"></div>
      </div>
      <div style="margin-top:16px;">
        <a class="btn btn-primary" id="mSendEmail" href="#" target="_blank" rel="noopener">Send Email</a>
        <button class="btn btn-outline" id="mClose2" type="button">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  const grid = document.getElementById('doctorGrid');
  const modal = document.getElementById('doctorModal');
  const mClose = document.getElementById('mClose');
  const mClose2 = document.getElementById('mClose2');

  const mName = document.getElementById('mName');
  const mDesig = document.getElementById('mDesig');
  const mEmail = document.getElementById('mEmail');
  const mContact = document.getElementById('mContact');
  const mDetails = document.getElementById('mDetails');
  const mPhoto = document.getElementById('mPhoto');
  const mSendEmail = document.getElementById('mSendEmail');

  function openModal(d) {
    const name = d.dataset.name || '';
    const desig = d.dataset.desig || '';
    const email = d.dataset.email || '';
    const contact = d.dataset.contact || '';
    const details = d.dataset.details || '';
    const photo = d.dataset.photo || '';

    mName.textContent = name;
    mDesig.textContent = desig;
    mEmail.textContent = email || '-';
    mEmail.href = email ? 'mailto:' + email : '#';
    mContact.textContent = contact || '-';
    mDetails.textContent = details || '—';

    if (photo) {
      mPhoto.src = photo;
      mPhoto.style.display = 'block';
    } else {
      mPhoto.style.display = 'none';
    }

    mSendEmail.href = email ? ('mailto:' + email + '?subject=Parent%20Care%20Consultation&body=Hello%20' + encodeURIComponent(name) + ',%0D%0A') : '#';

    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
  }
  function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden','true');
  }

  if (grid) {
    grid.addEventListener('click', (e) => {
      const card = e.target.closest('.card');
      if (card) openModal(card);
    });
  }
  mClose.addEventListener('click', closeModal);
  mClose2.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal(); // click backdrop to close
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
</script>

</body>
</html>
