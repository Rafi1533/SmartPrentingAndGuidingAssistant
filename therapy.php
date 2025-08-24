<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header('Location: parent_login.php'); exit; }
$user_id = (int)$_SESSION['user_id'];
function esc($s){ return htmlspecialchars($s); }
$notice = '';

// Submit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_session'])) {
    $autism_type_id = intval($_POST['autism_type_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    if ($autism_type_id && $title) {
        $stmt = $conn->prepare("INSERT INTO therapy_requests (user_id, autism_type_id, title, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $autism_type_id, $title, $description);
        if ($stmt->execute()) $notice = "Request submitted.";
        else $notice = "Error: " . $conn->error;
        $stmt->close();
    } else $notice = "Please select type and enter title.";
}

// Cancel request (only pending)
if (isset($_GET['cancel_request'])) {
    $rid = intval($_GET['cancel_request']);
    $q = $conn->prepare("SELECT status FROM therapy_requests WHERE id=? AND user_id=?");
    $q->bind_param("ii",$rid,$user_id); $q->execute(); $res = $q->get_result(); $r = $res->fetch_assoc(); $q->close();
    if ($r && $r['status']==='Pending') {
        $u = $conn->prepare("UPDATE therapy_requests SET status='Cancelled' WHERE id=?"); $u->bind_param("i",$rid); $u->execute(); $u->close();
        $notice = "Request cancelled.";
    } else $notice = "Cannot cancel.";
}

// Fetch data
$types = $conn->query("SELECT * FROM therapy_autism_types ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$tutorials_res = $conn->query("SELECT * FROM therapy_tutorials ORDER BY created_at DESC");
$tutorials_by_type = [];
while ($r = $tutorials_res->fetch_assoc()) $tutorials_by_type[$r['autism_type_id']][] = $r;

$user_requests = $conn->query("SELECT r.*, at.name AS autism_name FROM therapy_requests r JOIN therapy_autism_types at ON r.autism_type_id = at.id WHERE r.user_id = $user_id ORDER BY r.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$user_sessions = $conn->query("SELECT s.*, at.name AS autism_name, r.status AS request_status FROM therapy_sessions s JOIN therapy_requests r ON s.request_id = r.id JOIN therapy_autism_types at ON s.autism_type_id = at.id WHERE s.user_id = $user_id ORDER BY s.start_datetime DESC")->fetch_all(MYSQLI_ASSOC);
$payments = $conn->query("SELECT p.*, s.start_datetime FROM therapy_payments p JOIN therapy_sessions s ON p.session_id = s.id WHERE p.user_id = $user_id ORDER BY p.uploaded_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Therapy — User</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
    --blue:#1565C0;
    --muted:#e0e0e0;
    --accent:#ff416c;
    --card-bg:rgba(0,0,0,0.35);
}
body{
    font-family: 'Inter',Segoe UI,Roboto,Arial;
    margin:0;
    color:#fff;
    overflow-x:hidden;
}
.bg-video{
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    object-fit:cover;
    z-index:-1;
}
.nav{
    background: rgba(0,0,0,0.5);
    color:#fff;
    padding:14px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 6px 20px rgba(0,0,0,0.3);
    border-radius:0 0 20px 20px;
}
.nav span.logo{ font-size:20px; font-weight:700; }
.nav .links a{
    margin-left:16px;
    text-decoration:none;
    color:#fff;
    font-weight:500;
    transition:0.3s;
}
.nav .links a:hover{ color:var(--accent); }
.container{
    max-width:1100px;
    margin:20px auto;
    padding:16px;
}
.grid{
    display:grid;
    grid-template-columns:1fr 360px;
    gap:16px;
}
.card{
    background: var(--card-bg);
    border-radius:16px;
    padding:18px;
    backdrop-filter:blur(8px);
    transition:transform 0.3s ease,box-shadow 0.3s ease;
}
.card:hover{
    transform: translateY(-6px);
    box-shadow:0 15px 35px rgba(0,0,0,0.4);
}
h2{color:var(--white);margin:0 0 10px 0}
select,input,textarea{
    width:100%;
    padding:10px;
    border:1px solid rgba(255,255,255,0.3);
    border-radius:10px;
    margin-top:8px;
    font-size:14px;
    background: rgba(255,255,255,0.1);
    color:#fff;
}
select option{ color:#000; }
button{
    background:var(--blue);
    color:#fff;
    padding:10px 14px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    transition:all 0.3s ease;
}
button:hover{ background:#0b3d91; }
.tutorial{
    display:flex;
    gap:12px;
    align-items:flex-start;
    padding:12px;
    border-bottom:1px solid rgba(255,255,255,0.2);
    transition:background 0.2s;
}
.tutorial:hover{ background:rgba(255,255,255,0.1); }
.tutorial h4{margin:0;font-size:15px}
.play-btn{
    background:#fff;
    color:var(--blue);
    border:1px solid rgba(255,255,255,0.5);
    padding:8px 10px;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
}
.list{
    max-height:400px;
    overflow:auto;
}
.badge{
    background:rgba(255,255,255,0.2);
    color:#fff;
    padding:6px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:500;
}
a.button-link{
    display:inline-block;
    margin-top:6px;
    padding:6px 12px;
    border-radius:8px;
    background: var(--accent);
    color:#fff;
    text-decoration:none;
    font-size:13px;
    transition:all 0.3s;
}
a.button-link:hover{
    transform: translateY(-2px);
    box-shadow:0 6px 15px rgba(0,0,0,0.4);
}
.modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.8);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
}
.modal-content{
    width:95%;
    max-width:1000px;
    background:#000;
    border-radius:12px;
    overflow:hidden;
    position:relative;
}
.close-btn{
    position:absolute;
    right:12px;
    top:12px;
    background:rgba(255,255,255,0.95);
    border-radius:50%;
    padding:6px 8px;
    border:none;
    cursor:pointer;
}
.small{font-size:13px;color:#ddd}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<video autoplay muted loop class="bg-video">
    <source src="parenthome.mp4" type="video/mp4">
</video>

<div class="nav">
    <span class="logo">Smart Parenting — Therapy</span>
    <div class="links">
        <a href="parent_dashboard.php">Home</a>
        <a href="specialchild.php">SpecialChildCare</a>
        <a href="parent_logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <?php if($notice): ?><div class="card"><strong><?= esc($notice) ?></strong></div><?php endif; ?>

    <div class="grid">
        <div>
            <!-- Browse Tutorials -->
            <div class="card">
                <h2>Browse Tutorials</h2>
                <label>Select Autism Type</label>
                <select id="typeSelect" onchange="onTypeChange()">
                    <option value="">-- choose type --</option>
                    <?php foreach($types as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="tutorialList" class="list" style="margin-top:12px"><p class="small">Select a type to see tutorials.</p></div>
            </div>

            <!-- Request Session -->
            <div class="card" style="margin-top:16px">
                <h2>Request Live Session</h2>
                <form method="post">
                    <label>Autism Type</label>
                    <select name="autism_type_id" required>
                        <option value="">Select type</option>
                        <?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?>
                    </select>
                    <label>Request Title</label><input name="title" required>
                    <label>Details</label><textarea name="description" rows="4"></textarea>
                    <div style="margin-top:10px">
                        <button type="submit" name="request_session">Request Session</button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <!-- User Requests -->
            <div class="card">
                <h2>Your Requests</h2>
                <?php if(count($user_requests)==0): ?><p class="small">No requests yet.</p><?php endif; ?>
                <div style="max-height:260px;overflow:auto">
                <?php foreach($user_requests as $r): ?>
                    <div style="border-bottom:1px solid rgba(255,255,255,0.2);padding:10px 0">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <strong><?= esc($r['title']) ?></strong><br>
                                <span class="small"><?= esc($r['autism_name']) ?> • <?= esc($r['created_at']) ?></span>
                            </div>
                            <div><span class="badge"><?= esc($r['status']) ?></span></div>
                        </div>
                        <div style="margin-top:6px" class="small"><?= nl2br(esc($r['description'])) ?></div>
                        <div style="margin-top:8px">
                            <?php if($r['status']==='Pending'): ?>
                            <a href="?cancel_request=<?= $r['id'] ?>" class="button-link">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Sessions & Payments -->
            <div class="card" style="margin-top:16px">
                <h2>Your Sessions & Payments</h2>
                <?php if(count($user_sessions)==0): ?><p class="small">No sessions scheduled.</p><?php endif; ?>
                <?php foreach($user_sessions as $s): ?>
                    <div style="border-bottom:1px solid rgba(255,255,255,0.2);padding:10px 0">
                        <div style="display:flex;justify-content:space-between">
                            <div>
                                <strong><?= esc($s['autism_name']) ?></strong><br>
                                <span class="small"><?= esc($s['doctor_name']) ?> • <?= esc($s['start_datetime']) ?></span>
                            </div>
                            <div style="text-align:right">
                                <?php
                                $ps = $conn->prepare("SELECT * FROM therapy_payments WHERE session_id=? AND user_id=? ORDER BY uploaded_at DESC LIMIT 1");
                                $ps->bind_param("ii",$s['id'],$user_id); $ps->execute(); $res = $ps->get_result(); $pay = $res->fetch_assoc(); $ps->close();
                                ?>
                                <?php if(!$pay): ?>
                                    <div class="small">Payment: 200 ৳</div>
                                    <a href="pay.php?session=<?= $s['id'] ?>" class="button-link">Pay First</a>
                                <?php else: ?>
                                    <div class="small">Payment: <?= esc($pay['status']) ?></div>
                                    <?php if($pay['status']==='Received'): ?>
                                        <a href="<?= esc($s['video_call_link']) ?>" class="button-link" target="_blank">Join</a>
                                    <?php elseif($pay['status']==='Pending'): ?>
                                        <div class="small">Awaiting admin verification</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Video modal -->
<div id="videoModal" class="modal" style="display:none">
    <div class="modal-content" id="modalContent">
        <button class="close-btn" onclick="closeModal()">✕</button>
        <div id="videoContainer" style="width:100%;height:70vh;background:#000;display:flex;align-items:center;justify-content:center"></div>
        <div style="background:#111;padding:16px;color:#fff">
            <h3 id="modalTitle" style="margin:0"></h3>
            <p id="modalDetails" class="small"></p>
        </div>
    </div>
</div>

<script>
const tutorials = <?= json_encode($tutorials_by_type) ?>;

function onTypeChange(){
  const sel = document.getElementById('typeSelect');
  const id = sel.value;
  const list = document.getElementById('tutorialList');
  list.innerHTML = '';
  if(!id){ list.innerHTML = '<p class="small">Select a type to see tutorials.</p>'; return; }
  const items = tutorials[id] || [];
  if(items.length===0){ list.innerHTML = '<p class="small">No tutorials yet for this type.</p>'; return; }
  items.forEach(t=>{
    const div = document.createElement('div');
    div.className = 'tutorial';
    div.innerHTML = `<div style="flex:1"><h4>${t.title}</h4><div class="small">${t.created_at}</div><div class="small">${t.details? t.details.substring(0,150)+'...' : ''}</div></div>
      <div><button class="play-btn" onclick='openVideo(${JSON.stringify(JSON.stringify(t))})'>Play</button></div>`;
    list.appendChild(div);
  });
}

function openVideo(jsonStr){
  const t = JSON.parse(jsonStr);
  const modal = document.getElementById('videoModal');
  const container = document.getElementById('videoContainer');
  document.getElementById('modalTitle').innerText = t.title;
  document.getElementById('modalDetails').innerText = t.details || '';
  let url = t.video_url;
  let videoId = null;
  if(url.includes('youtube.com/watch?v=')) videoId = url.split('v=')[1].split('&')[0];
  else if(url.includes('youtu.be/')) videoId = url.split('youtu.be/')[1].split('?')[0];
  container.innerHTML = '';
  if(videoId){
    const iframe = document.createElement('iframe');
    iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
    iframe.style.width='100%'; iframe.style.height='100%';
    iframe.allow='autoplay; fullscreen';
    container.appendChild(iframe);
  }else{
    const a = document.createElement('a'); a.href=url; a.innerText='Open video'; a.target='_blank'; a.style.color='#fff';
    container.appendChild(a);
  }
  modal.style.display='flex';
}

function closeModal(){
  const modal = document.getElementById('videoModal');
  const container = document.getElementById('videoContainer');
  container.innerHTML='';
  modal.style.display='none';
}
</script>
</body>
</html>
