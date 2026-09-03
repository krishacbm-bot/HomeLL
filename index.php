<?php
session_start();

if(!isset($_SESSION['student_id'])){
    header("Location: index.php");
    exit();
}
$classSection = isset($_SESSION['class_code']) ? htmlspecialchars(trim($_SESSION['class_code'])) : '';
$rawGrade = trim($_SESSION['grade']);

// Split by comma — handles single "5" or multiple "5,6,7"
$gradeParts = array_map('trim', explode(',', $rawGrade));
$studentGrades = [];

foreach($gradeParts as $part) {
    if (strtolower($part) == "junior kg" || strtolower($part) == "jr kg" || strtolower($part) == "jr_kg" || strtolower($part) == "jr-kg") {
        $studentGrades[] = "jr_kg";
    } elseif (strtolower($part) == "senior kg" || strtolower($part) == "sr kg" || strtolower($part) == "sr_kg" || strtolower($part) == "sr-kg") {
        $studentGrades[] = "sr_kg";
    } else {
        $cleaned = preg_replace('/[^0-9]/', '', $part);
        if ($cleaned !== '') $studentGrades[] = $cleaned;
    }
}

$studentGradesJson = json_encode($studentGrades);

// ── Student info from session ──
$firstName    = isset($_SESSION['first_name'])  ? htmlspecialchars(trim($_SESSION['first_name']))  : '';
$lastName     = isset($_SESSION['last_name'])   ? htmlspecialchars(trim($_SESSION['last_name']))   : '';
$studentName  = trim("$firstName $lastName") ?: 'Student';
$schoolName   = isset($_SESSION['school_name']) ? htmlspecialchars(trim($_SESSION['school_name'])) : 'Talky Friends';
$avatarLetter = mb_strtoupper(mb_substr($firstName ?: 'S', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Language Lab - Talky Friends Lab</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="manifest" href="manifest.json" />
<meta name="theme-color" content="#7c6fff" />
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg: #06071a;
  --surface: rgba(255,255,255,0.04);
  --border: rgba(255,255,255,0.10);
  --text: #eef0ff;
  --muted: rgba(238,240,255,0.55);
  --accent1: #7c6fff;
  --accent2: #ff6fd8;
  --accent3: #43e8d8;
  --accent4: #ffcf5c;
  --glow1: rgba(124,111,255,0.35);
  --glow2: rgba(255,111,216,0.30);
}

html { scroll-behavior: smooth; }
body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: var(--bg); color: var(--text);
  min-height: 100vh; overflow-x: hidden; position: relative;
}

#starCanvas { position: fixed; inset: 0; pointer-events: none; z-index: 0; }

.blob { position: fixed; border-radius: 50%; filter: blur(90px); opacity: 0.18; pointer-events: none; z-index: 0; animation: blobFloat 14s ease-in-out infinite alternate; }
.blob1 { width:520px;height:520px;background:var(--accent1);top:-120px;left:-100px;animation-delay:0s; }
.blob2 { width:420px;height:420px;background:var(--accent2);bottom:-100px;right:-80px;animation-delay:-5s; }
.blob3 { width:300px;height:300px;background:var(--accent3);bottom:30%;left:60%;animation-delay:-9s; }
@keyframes blobFloat { from{transform:translate(0,0) scale(1)} to{transform:translate(30px,40px) scale(1.08)} }

body::after {
  content:''; position:fixed; inset:0; z-index:1;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  opacity:0.03; pointer-events:none;
}

#app {
  position:relative; z-index:2; min-height:100vh;
  display:flex; flex-direction:column; align-items:center;
  padding:0 20px 60px;
}

/* ── HEADER ── */
header {
  width:100%; max-width:1100px;
  display:flex; align-items:center; justify-content:space-between;
  padding:28px 0 0; gap:16px;
  animation:fadeDown 0.8s cubic-bezier(.22,1,.36,1) both;
  flex-wrap: wrap;
}

.logo-wrap { display:flex; align-items:center; gap:14px; }
.logo-icon {
  width:70px; height:70px;
  background: linear-gradient(135deg, #ffffff, #f8f5ff);
  border-radius:16px; display:grid; place-items:center;
  box-shadow:0 0 24px var(--glow1); flex-shrink:0;
}
.logo-icon img { width:50px; height:65px; }
.logo-text h1 {
  font-size:clamp(1.2rem,2.5vw,1.7rem); font-weight:800;
  background:linear-gradient(90deg,#fff 30%,var(--accent1));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.logo-text p {
  font-size:0.78rem; font-weight:600; color:var(--muted);
  letter-spacing:1.5px; text-transform:uppercase; margin-top:1px;
}

/* ── HEADER RIGHT ── */
.header-right {
  display: flex; align-items: center; gap: 12px;
}

.student-pill {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.10);
  border-radius: 50px;
  padding: 8px 16px 8px 8px;
}

.student-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent1), var(--accent2));
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; font-weight: 800; color: #fff;
  flex-shrink: 0; text-transform: uppercase;
  box-shadow: 0 0 14px var(--glow1);
}

.student-info {
  display: flex; flex-direction: column; gap: 1px;
}

.student-name {
  font-size: 0.85rem; font-weight: 700; color: var(--text);
  line-height: 1.2; white-space: nowrap;
}

.student-school {
  font-size: 0.68rem; font-weight: 600; color: var(--muted);
  letter-spacing: 0.5px; white-space: nowrap;
}

.logout-btn {
  display: flex; align-items: center; gap: 6px;
  background: rgba(255,82,119,0.12);
  border: 1px solid rgba(255,82,119,0.30);
  color: #ff5277; border-radius: 50px;
  padding: 9px 18px; font-size: 0.8rem; font-weight: 700;
  cursor: pointer; text-decoration: none;
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
  white-space: nowrap;
}
.logout-btn:hover {
  background: rgba(255,82,119,0.22);
  box-shadow: 0 4px 18px rgba(255,82,119,0.25);
  transform: translateY(-1px);
}
.logout-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

/* ── LIVE BADGE (hidden on mobile, shown on tablet+) ── */
.live-badge {
  display:inline-flex; align-items:center; gap:8px;
  background:linear-gradient(135deg,rgba(124,111,255,0.18),rgba(255,111,216,0.12));
  border:1px solid rgba(124,111,255,0.35);
  border-radius:50px; padding:7px 18px;
  font-size:0.8rem; font-weight:800; letter-spacing:1.2px; text-transform:uppercase; color:var(--accent1);
}
.live-badge .dot {
  width:7px; height:7px; border-radius:50%;
  background:var(--accent3); box-shadow:0 0 8px var(--accent3);
  animation:pulse 1.6s infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(1.5)} }

/* ── HERO ── */
.hero {
  margin-top:54px;
  display:flex; flex-direction:column; align-items:center; gap:18px;
  text-align:center;
  animation:fadeUp 0.9s 0.3s cubic-bezier(.22,1,.36,1) both;
}
.hero h2 { font-size:clamp(2.2rem,6vw,4rem); font-weight:800; line-height:1.15; max-width:700px; }
.hero h2 .hl1 { background:linear-gradient(90deg,var(--accent1),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.hero h2 .hl2 { background:linear-gradient(90deg,var(--accent3),var(--accent4)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.hero p { font-size:clamp(0.95rem,2vw,1.1rem); color:var(--muted); max-width:500px; line-height:1.7; font-weight:500; }

/* ── SECTION LABEL ── */
.section-label {
  align-self:center; max-width:1100px; width:100%;
  display:flex; align-items:center; justify-content:center;
  gap:12px; margin-top:52px;
  font-size:0.78rem; font-weight:800; letter-spacing:2px; text-transform:uppercase; color:var(--muted);
  animation:fadeUp 0.9s 0.5s cubic-bezier(.22,1,.36,1) both;
}
.section-label::before, .section-label::after {
  content:''; flex:1; height:1px;
  background:linear-gradient(90deg,transparent,var(--border),transparent);
}

/* ── GRADE GRID ── */
#gradeGrid {
  width:100%; max-width:1100px;
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(180px,1fr));
  gap:14px; margin-top:20px;
  animation:fadeUp 0.9s 0.6s cubic-bezier(.22,1,.36,1) both;
}

.grade-card {
  position:relative;
  display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  gap:12px; text-align:center;
  padding:32px 16px 26px;
  min-height:210px;
  border-radius:24px;
  background:rgba(255,255,255,0.03);
  border:1px solid rgba(255,255,255,0.07);
  cursor:pointer; overflow:hidden;
  transition:transform 0.32s cubic-bezier(.22,1,.36,1), box-shadow 0.32s, border-color 0.28s;
  will-change:transform;
}
.grade-card::before {
  content:''; position:absolute; inset:0; border-radius:inherit;
  background:var(--cg); opacity:0; transition:opacity 0.35s;
}
.grade-card::after {
  content:''; position:absolute; top:0; left:0; right:0; height:3px;
  background:var(--cg); border-radius:24px 24px 0 0;
  opacity:0; transition:opacity 0.28s;
}
.grade-card:hover { transform:translateY(-10px) scale(1.03); box-shadow:0 28px 64px -14px var(--cs); border-color:var(--ca); }
.grade-card:hover::before { opacity:0.11; }
.grade-card:hover::after  { opacity:1; }
.grade-card:active { transform:scale(0.97); transition-duration:0.1s; }

.ci {
  width:76px; height:76px; border-radius:50%;
  background:var(--cg);
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 10px 32px -8px var(--cs);
  position:relative; z-index:1; flex-shrink:0;
  transition:transform 0.35s cubic-bezier(.22,1,.36,1);
}
.grade-card:hover .ci { transform:scale(1.14) rotate(-7deg); }
.ci svg { width:32px; height:32px; }

.cl { font-size:1.15rem; font-weight:800; color:#fff; position:relative; z-index:1; line-height:1.2; }

.cs {
  display:inline-flex; align-items:center; gap:5px;
  padding:3px 11px; border-radius:99px;
  background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.10);
  font-size:0.62rem; font-weight:700; color:var(--muted); letter-spacing:1.1px; text-transform:uppercase;
  position:relative; z-index:1;
}
.cs-dot { width:5px; height:5px; border-radius:50%; background:var(--ca); }

.ca {
  position:absolute; bottom:13px; right:13px;
  width:26px; height:26px; border-radius:50%;
  background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);
  display:flex; align-items:center; justify-content:center;
  transition:background 0.25s, transform 0.25s; z-index:1;
}
.grade-card:hover .ca { background:var(--ca); border-color:transparent; transform:translate(2px,-2px); }
.ca svg { width:11px; height:11px; stroke:#fff; fill:none; stroke-width:2.5; stroke-linecap:round; stroke-linejoin:round; }

/* ── NO GRADE ── */
.no-grade {
  grid-column: 1/-1; text-align: center; padding: 48px 24px;
  color: var(--muted); font-size: 1rem; font-weight: 600;
}
.no-grade span { display: block; font-size: 2.5rem; margin-bottom: 12px; }

/* ── TOAST ── */
#toast {
  position:fixed; bottom:36px; left:50%;
  transform:translateX(-50%) translateY(80px);
  background:rgba(20,18,60,0.95); border:1px solid var(--accent1);
  backdrop-filter:blur(20px); border-radius:50px;
  padding:12px 28px; font-size:0.9rem; font-weight:700; color:#fff;
  box-shadow:0 8px 36px -4px var(--glow1);
  z-index:999; transition:transform 0.45s cubic-bezier(.22,1,.36,1),opacity 0.45s;
  opacity:0; pointer-events:none; white-space:nowrap;
}
#toast.show { transform:translateX(-50%) translateY(0); opacity:1; }

/* ── FOOTER ── */
footer {
  margin-top:52px; font-size:0.78rem; color:var(--muted);
  font-weight:600; letter-spacing:0.5px; text-align:center;
  animation:fadeUp 0.9s 1s cubic-bezier(.22,1,.36,1) both;
}

@keyframes fadeUp   { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeDown { from{opacity:0;transform:translateY(-20px)} to{opacity:1;transform:translateY(0)} }

::-webkit-scrollbar { width:6px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--border); border-radius:99px; }

/* ── RESPONSIVE ── */
@media (max-width:700px) {
  #gradeGrid { grid-template-columns:repeat(2,1fr); }
  .live-badge { display: none; }
}
@media (max-width:500px) {
  .student-school { display: none; }
  .logout-btn span { display: none; }
  .logout-btn { padding: 9px 12px; }
  .student-pill { padding: 6px 12px 6px 6px; }
}
@media (max-width:400px) {
  #gradeGrid { grid-template-columns:1fr 1fr; gap:10px; }
  .grade-card { min-height:170px; padding:22px 12px 18px; }
  .ci { width:58px; height:58px; }
}
</style>
</head>

<body>
<canvas id="starCanvas"></canvas>
<div class="blob blob1"></div>
<div class="blob blob2"></div>
<div class="blob blob3"></div>

<div id="app">

  <header>
    <!-- LEFT: Logo + School Name -->
    <div class="logo-wrap">
      <div class="logo-icon">
        <img src="images/logo.png" alt="Logo">
      </div>
      <div class="logo-text">
        <h1>Talky Friends Lab</h1>
        <p><?php echo $schoolName; ?></p>
      </div>
    </div>

    <!-- RIGHT: Live Badge + Student Pill + Logout -->
    <div class="header-right">
      <!-- <div class="live-badge"><span class="dot"></span>Now Live — Language Lab</div> -->

        <div class="student-pill">
          <div class="student-avatar"><?php echo $avatarLetter; ?></div>
          <div class="student-info">
            <span class="student-name"><?php echo $studentName; ?></span>
            <span class="student-school"><?php echo $schoolName; ?></span>
          </div>
        </div>

      <a href="logout.php" class="logout-btn" title="Logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Logout</span>
      </a>
    </div>
  </header>

  <div class="hero">
    <h2>Learn, Speak &amp; <span class="hl1">Grow</span><br/>with <span class="hl2">Talky Friends</span></h2>
    <p>An interactive language experience for every grade. Select your level below and start your personalized journey today.</p>
  </div>

  <div class="section-label">Your Grade</div>

  <div id="gradeGrid"></div>

  <footer>Talky Friends Lab &nbsp;|&nbsp; Language Learning for Every Level</footer>
</div>

<div id="toast"></div>

<script>
      const studentId = <?php echo (int)($_SESSION['student_id'] ?? 0); ?>;
const classSection = "<?php echo isset($_SESSION['section']) ? addslashes(trim($_SESSION['section'])) : ''; ?>";
/* ── STARS ── */
(function(){
  const c=document.getElementById('starCanvas'),ctx=c.getContext('2d');let s=[];
  function resize(){c.width=window.innerWidth;c.height=window.innerHeight;}
  function make(){s=[];for(let i=0;i<140;i++)s.push({x:Math.random()*c.width,y:Math.random()*c.height,r:Math.random()*1.4+0.3,a:Math.random(),sp:Math.random()*0.008+0.003,d:Math.random()>.5?1:-1});}
  function draw(){ctx.clearRect(0,0,c.width,c.height);s.forEach(p=>{p.a+=p.sp*p.d;if(p.a>=1||p.a<=0)p.d*=-1;ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`rgba(200,210,255,${p.a})`;ctx.fill();});requestAnimationFrame(draw);}
  window.addEventListener('resize',()=>{resize();make();});resize();make();draw();
})();

/* ── GRADE DATA FROM PHP SESSION ── */
const STUDENT_GRADES = <?php echo $studentGradesJson; ?>;

const GRADES=[
  {grade:'jr_kg',label:'Junior KG',   sub:'Pre-School',accent:'#ff6fd8',shadow:'rgba(255,111,216,0.5)',grad:'linear-gradient(135deg,#ff6fd8,#a06fff)'},
  {grade:'sr_kg',label:'Senior KG',   sub:'Pre-School',accent:'#a06fff',shadow:'rgba(160,111,255,0.5)',grad:'linear-gradient(135deg,#a06fff,#7c6fff)'},
  {grade:'1',    label:'Grade 1',     sub:'Primary',   accent:'#43e8d8',shadow:'rgba(67,232,216,0.5)', grad:'linear-gradient(135deg,#43e8d8,#4da8ff)'},
  {grade:'2',    label:'Grade 2',     sub:'Primary',   accent:'#4da8ff',shadow:'rgba(77,168,255,0.5)', grad:'linear-gradient(135deg,#4da8ff,#7c6fff)'},
  {grade:'3',    label:'Grade 3',     sub:'Primary',   accent:'#ffcf5c',shadow:'rgba(255,207,92,0.5)', grad:'linear-gradient(135deg,#ffcf5c,#ff9a5c)'},
  {grade:'4',    label:'Grade 4',     sub:'Primary',   accent:'#ff9a5c',shadow:'rgba(255,154,92,0.5)', grad:'linear-gradient(135deg,#ff9a5c,#ff6fd8)'},
  {grade:'5',    label:'Grade 5',     sub:'Middle',    accent:'#ff5277',shadow:'rgba(255,82,119,0.5)', grad:'linear-gradient(135deg,#ff6fd8,#ff5277)'},
  {grade:'6',    label:'Grade 6',     sub:'Middle',    accent:'#43d8a0',shadow:'rgba(67,216,160,0.5)', grad:'linear-gradient(135deg,#43e8d8,#43d8a0)'},
  {grade:'7',    label:'Grade 7',     sub:'Middle',    accent:'#5562ff',shadow:'rgba(85,98,255,0.5)',  grad:'linear-gradient(135deg,#7c6fff,#5562ff)'},
  {grade:'8',    label:'Grade 8',     sub:'Secondary', accent:'#43e8d8',shadow:'rgba(67,232,216,0.5)', grad:'linear-gradient(135deg,#5562ff,#43e8d8)'},
  {grade:'9',    label:'Grade 9',     sub:'Secondary', accent:'#ff6fd8',shadow:'rgba(255,111,216,0.5)',grad:'linear-gradient(135deg,#ff6fd8,#ffcf5c)'},
  {grade:'10',   label:'Grade 10',    sub:'Secondary', accent:'#a06fff',shadow:'rgba(160,111,255,0.5)',grad:'linear-gradient(135deg,#a06fff,#ff6fd8)'},
  {grade:'11',   label:'Grade 11',    sub:'Senior',    accent:'#ffcf5c',shadow:'rgba(255,207,92,0.5)', grad:'linear-gradient(135deg,#ffcf5c,#ff9a5c)'},
  {grade:'12',   label:'Grade 12',    sub:'Senior',    accent:'#ff5277',shadow:'rgba(255,82,119,0.5)', grad:'linear-gradient(135deg,#ff5277,#a06fff)'},
];

const ICONS=[
  `<svg viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="6" fill="white" opacity="0.9"/><line x1="16" y1="3" x2="16" y2="8" stroke="white" stroke-width="2.5" stroke-linecap="round"/><line x1="16" y1="24" x2="16" y2="29" stroke="white" stroke-width="2.5" stroke-linecap="round"/><line x1="3" y1="16" x2="8" y2="16" stroke="white" stroke-width="2.5" stroke-linecap="round"/><line x1="24" y1="16" x2="29" y2="16" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M28 18A12 12 0 1 1 14 4a9 9 0 0 0 14 14z"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><polygon points="16 3 19.5 12 30 13 22 20 24.5 30 16 25 7.5 30 10 20 2 13 12.5 12 16 3"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round"><path d="M8 26c0 0 2-14 16-20C24 16 15 28 8 26z"/><line x1="8" y1="26" x2="17" y2="16"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><polygon points="16 2 20 11 30 12 23 19 25 29 16 24 7 29 9 19 2 12 12 11 16 2"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M29 10a7 7 0 0 0-12-3L16 9l-1-2A7 7 0 0 0 3 16l13 13 13-13a7 7 0 0 0 0-6z"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white" stroke="white" stroke-width="0.5"><polyline points="8 4 24 4 29 13 16 30 3 13 8 4" fill="rgba(255,255,255,0.75)"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M6 24a8 8 0 0 1 4-9 10 10 0 0 1 18-6 8 8 0 0 1 2 15H6z" opacity="0.9"/><line x1="10" y1="30" x2="10" y2="24" stroke="white" stroke-width="2.5" stroke-linecap="round"/><line x1="16" y1="30" x2="16" y2="24" stroke="white" stroke-width="2.5" stroke-linecap="round"/><line x1="22" y1="30" x2="22" y2="24" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M14 4A3 3 0 0 1 17 7v18a3 3 0 0 1-5.96-.55A3 3 0 0 1 9 19V11a3 3 0 0 1 2-5.8A3 3 0 0 1 14 4z"/><path d="M18 4a3 3 0 0 0-3 3v18a3 3 0 0 0 5.96-.55A3 3 0 0 0 23 19V11a3 3 0 0 0-2-5.8A3 3 0 0 0 18 4z"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M28 22V12a2 2 0 0 0-1-1.73L17 4a2 2 0 0 0-2 0L5 10.27A2 2 0 0 0 4 12v10a2 2 0 0 0 1 1.73L15 30a2 2 0 0 0 2 0l10-6A2 2 0 0 0 28 22z"/><line x1="4.27" y1="10" x2="16" y2="17"/><line x1="27.73" y1="10" x2="16" y2="17"/><line x1="16" y1="30" x2="16" y2="17"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M13 18a7 7 0 1 0 0-10 7 7 0 0 0 0 10z" opacity="0.8"/><path d="M16 3c0 0-7 8-7 15a7 7 0 0 0 14 0C23 11 16 3 16 3z"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M16 3l2.5 7h8l-6.5 5.5 2.5 8L16 19l-6.5 4.5 2.5-8L5.5 10h8z" opacity="0.85"/><rect x="5" y="26" width="22" height="4" rx="2"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M3 26l5-13 7 7 4-16 5 9 3-5 4 8H3z" opacity="0.85"/></svg>`,
  `<svg viewBox="0 0 32 32" fill="white"><path d="M6 12h5V5H6v7zm3 16a7 7 0 1 0 0-14 7 7 0 0 0 0 14zm13-17l7 12H15l7-12z" opacity="0.85"/></svg>`,
];

/* ── BUILD GRADE GRID ── */
const grid = document.getElementById('gradeGrid');
const matched = GRADES.filter(g => STUDENT_GRADES.includes(String(g.grade)));

if (matched.length === 0) {
  grid.innerHTML = `<div class="no-grade"><span>🎓</span>No grade assigned yet.<br>Please contact your instructor.</div>`;
} else {
  matched.forEach((g) => {
    const idx = GRADES.indexOf(g);
    const card = document.createElement('div');
    card.className = 'grade-card';
    card.style.cssText = `--cg:${g.grad};--cs:${g.shadow};--ca:${g.accent}`;
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');
    card.setAttribute('aria-label', `Open ${g.label}`);

    card.innerHTML = `
      <div class="ci">${ICONS[idx]}</div>
      <div class="cl">${g.label}</div>
      <div class="cs"><span class="cs-dot"></span>${g.sub}</div>
      <div class="ca"><svg viewBox="0 0 12 12"><polyline points="4 2 10 6 4 10"/></svg></div>`;

function go() {
    localStorage.setItem('selectedGrade', g.grade);
    showToast(`Opening ${g.label}…`);
    speakText(`${g.label}. Let's go!`);
    setTimeout(() => {
        window.location.href = `module.html?grade=${encodeURIComponent(g.grade)}&student_id=${studentId}&section=${encodeURIComponent(classSection)}`;
    }, 480);
}

    card.addEventListener('click', go);
    card.addEventListener('keydown', e => { if (e.key==='Enter'||e.key===' ') { e.preventDefault(); go(); } });
    grid.appendChild(card);
  });
}

/* ── TOAST ── */
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(t._t); t._t = setTimeout(() => t.classList.remove('show'), 2400);
}

/* ── TEXT TO SPEECH ── */
/* ── TEXT TO SPEECH (ElevenLabs — natural, human voice) ── */
let currentAudio = null;

function speakText(text) {
  if (currentAudio) {
    currentAudio.pause();
    currentAudio = null;
  }

  fetch('tts.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'text=' + encodeURIComponent(text)
  })
  .then(res => {
    if (!res.ok) throw new Error('TTS request failed');
    return res.blob();
  })
  .then(blob => {
    const url = URL.createObjectURL(blob);
    currentAudio = new Audio(url);
    currentAudio.play().catch(err => console.log('Playback blocked:', err));
  })
  .catch(err => console.log('TTS error:', err));
}

window.addEventListener('load', () => {
  speakText('Welcome to Talky Friends Lab. Choose your grade to begin!');
});

window.addEventListener('beforeunload', () => { if (currentAudio) currentAudio.pause(); });
document.addEventListener('visibilitychange', () => { if (document.hidden && currentAudio) currentAudio.pause(); });

/* ── SERVICE WORKER ── */
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('./sw.js')
      .then(() => console.log('SW registered'))
      .catch(err => console.log('SW failed:', err));
  });
}
</script>
</body>
</html>