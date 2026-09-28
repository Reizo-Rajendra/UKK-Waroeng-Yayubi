<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../sistem_admin/view/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catering Dapoer Yayubi - Semarang</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
:root {
  --orange: #F7622A;
  --orange2: #F59332;
  --orange-dark: #D94E18;
  --cream: #FFF5EC;
  --white: #FFFFFF;
  --text-dark: #2B1A0E;
  --text-mid: #7A4E2D;
}
html { scroll-behavior:smooth; }
body { font-family:'Poppins',sans-serif; overflow-x:hidden; background:var(--cream); }

/* ═══ NAVBAR ═══ */
.navbar {
  position:fixed; top:0; width:100%; z-index:998;
background: rgba(255,255,255,.97);
-webkit-backdrop-filter: blur(14px);
backdrop-filter: blur(14px);
  border-bottom:1px solid rgba(0,0,0,.06);
  padding:0 2.5rem; height:72px;
  display:flex; align-items:center; justify-content:space-between;
  box-shadow:0 2px 20px rgba(0,0,0,.08);
  animation:navDown .6s ease;
}
@keyframes navDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:translateY(0)} }
.nav-logo { display:flex; align-items:center; gap:.7rem; text-decoration:none; }
.nav-logo-img {
  width:52px; height:52px; border-radius:50%;
  background:linear-gradient(135deg,#F7622A,#F59332);
  border:3px solid #eee; display:flex; align-items:center; justify-content:center; overflow:hidden;
}
.nav-logo-img img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.nav-logo-img span { font-size:.55rem; color:white; font-weight:900; text-align:center; line-height:1.2; }
.nav-links { display:flex; gap:1.6rem; list-style:none; }
.nav-links a {
  text-decoration:none; color:#444; font-size:.92rem; font-weight:600;
  transition:color .25s; position:relative; padding-bottom:4px;
}
.nav-links a::after {
  content:''; position:absolute; bottom:0; left:0; width:0; height:2px;
  background:var(--orange); border-radius:2px; transition:width .25s;
}
.nav-links a:hover { color:var(--orange); }
.nav-links a:hover::after { width:100%; }
.nav-right { display:flex; align-items:center; gap:1rem; }
.nav-phone { color:#555; font-size:.85rem; font-weight:600; text-decoration:none; }
.btn-nav {
  background:var(--orange); color:white; padding:.55rem 1.4rem;
  border-radius:25px; font-weight:700; font-size:.88rem; text-decoration:none;
  border:none; cursor:pointer; box-shadow:0 4px 14px rgba(247,98,42,.35); transition:all .25s;
}
.btn-nav:hover { background:var(--orange-dark); transform:translateY(-2px); }
.btn-login {
  color: var(--orange);
  background: white;
  border: 1.5px solid var(--orange);
  padding: .5rem 1.2rem;
  border-radius: 25px;
  font-weight: 700;
  font-size: .88rem;
  text-decoration: none;
  transition: all .25s;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
}
.btn-login:hover {
  background: #FFF0E5;
  color: var(--orange-dark);
  border-color: var(--orange-dark);
  transform: translateY(-2px);
}

/* ═══ HERO ═══ */
.hero {
  margin-top:72px; min-height:calc(100vh - 72px);
  position:relative; overflow:hidden;
  background:
    repeating-linear-gradient(180deg,
      transparent 0,transparent 68px,rgba(0,0,0,.13) 68px,rgba(0,0,0,.13) 70px,
      transparent 70px,transparent 138px,rgba(0,0,0,.08) 138px,rgba(0,0,0,.08) 140px),
    repeating-linear-gradient(90deg,
      transparent 0,transparent 120px,rgba(255,255,255,.03) 120px,rgba(255,255,255,.03) 121px),
    linear-gradient(160deg,#B0692E 0%,#8B4718 25%,#6E3610 50%,#9B5828 75%,#8B4E1F 100%);
  display:flex; align-items:center; justify-content:center; padding:3rem 2.5rem;
}
.hero::before {
  content:''; position:absolute; inset:0; z-index:0; pointer-events:none;
  background:radial-gradient(ellipse at 80% 50%,rgba(247,147,30,.18) 0%,transparent 60%),
             radial-gradient(ellipse at 20% 80%,rgba(232,56,13,.12) 0%,transparent 50%);
}
.hero-deco { position:absolute; opacity:.18; pointer-events:none; z-index:1; animation:floatDeco 6s ease-in-out infinite; }
.hero-deco.d1 { top:8%;  right:5%;  font-size:4rem;   animation-delay:0s; }
.hero-deco.d2 { top:55%; right:2%;  font-size:2.8rem; animation-delay:1.5s; }
.hero-deco.d3 { bottom:10%; right:18%; font-size:3.2rem; animation-delay:3s; }
.hero-deco.d4 { top:20%; left:2%;  font-size:2.5rem; animation-delay:2s; }
.hero-deco.d5 { bottom:18%; left:5%; font-size:3rem;  animation-delay:4s; }
@keyframes floatDeco {
  0%,100%{transform:translateY(0) rotate(0deg)}
  33%{transform:translateY(-18px) rotate(5deg)}
  66%{transform:translateY(10px) rotate(-4deg)}
}
.hero-inner { max-width:920px; width:100%; position:relative; z-index:2; display:flex; justify-content:center; }
.hero-glass {
width:100%;
background: rgba(255,255,255,.19);
-webkit-backdrop-filter: blur(12px);
backdrop-filter: blur(12px);
  border-radius:30px; padding:3.8rem 4rem 3.2rem;
  border:1.5px solid rgba(255,255,255,.38);
  box-shadow:0 16px 60px rgba(0,0,0,.32);
  animation:fadeUp .9s cubic-bezier(.22,1,.36,1); text-align:center;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:translateY(0)} }
.hero-badge {
  display:inline-flex; align-items:center; gap:.5rem;
  background:rgba(255,255,255,.22); border:1px solid rgba(255,255,255,.45);
  color:white; font-size:.83rem; font-weight:700;
padding:.45rem 1.3rem;
border-radius:30px;
margin-bottom:1.3rem;
-webkit-backdrop-filter: blur(6px);
backdrop-filter: blur(6px);
}
.hero-title {
  font-family:'Nunito',sans-serif; font-size:3.6rem; font-weight:900; line-height:1.1;
  color:#FFD700; text-shadow:2px 4px 16px rgba(0,0,0,.5);
  margin-bottom:.9rem; letter-spacing:-1px; animation:fadeUp .9s .1s ease backwards;
}
.hero-sub {
  color:rgba(255,255,255,.95); font-size:1.05rem; font-weight:600;
  line-height:1.65; margin-bottom:1.8rem; animation:fadeUp .9s .2s ease backwards;
}
.hero-stats {
  display:flex; justify-content:center; gap:2rem; margin-bottom:2rem;
  animation:fadeUp .9s .3s ease backwards;
}
.stat { text-align:center; }
.stat-icon { color:#FFD700; font-size:1.1rem; margin-bottom:.2rem; }
.stat-num { display:block; font-size:1.6rem; font-weight:900; color:white; line-height:1.1; }
.stat-lbl { font-size:.72rem; color:rgba(255,255,255,.8); font-weight:600; }
.hero-btns {
  display:flex; justify-content:center; gap:.9rem; flex-wrap:wrap;
  margin-bottom:1.4rem; animation:fadeUp .9s .4s ease backwards;
}
.btn-order-qris {
  background:var(--orange); color:white; padding:.8rem 1.6rem;
  border-radius:30px; font-weight:700; font-size:.92rem;
  border:none; cursor:pointer; text-decoration:none;
  box-shadow:0 5px 18px rgba(247,98,42,.55); transition:all .25s;
  display:flex; align-items:center; gap:.5rem;
}
.btn-order-qris:hover { background:#e0521e; transform:translateY(-3px); }
.btn-menu {
  background:rgba(255,255,255,.18); color:white;
  border:2px solid rgba(255,255,255,.65);
  padding:.8rem 1.6rem; border-radius:30px;
  font-weight:700; font-size:.92rem; cursor:pointer; text-decoration:none;
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
  transition:all .25s;
}
.btn-menu:hover { background:rgba(255,255,255,.32); transform:translateY(-3px); }
.btn-ig {
  background:linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045);
  color:white; padding:.8rem 1.6rem; border-radius:30px; font-weight:700; font-size:.92rem;
  border:none; cursor:pointer; text-decoration:none; transition:all .25s;
  display:flex; align-items:center; gap:.5rem;
}
.btn-ig:hover { transform:translateY(-3px); opacity:.88; }
.hero-badges {
  display:flex; justify-content:center; gap:1.5rem; animation:fadeUp .9s .5s ease backwards;
}
.hbadge { font-size:.77rem; font-weight:700; color:rgba(255,255,255,.9); display:flex; align-items:center; gap:.35rem; }
.dot { width:8px; height:8px; border-radius:50%; display:inline-block; flex-shrink:0; }
.dot-green { background:#4CAF50; } .dot-blue { background:#64B5F6; } .dot-yellow { background:#FFD700; }

/* ═══ MENU ═══ */
.section-menu {
  padding:5rem 2rem;
  background:linear-gradient(160deg,#FFF3E5 0%,#FBE0C4 50%,#F8D0A8 100%);
  position:relative; overflow:hidden;
}
.section-menu-inner { max-width:1100px; margin:0 auto; position:relative; z-index:2; }
.menu-top { display:flex; align-items:flex-start; justify-content:space-between; gap:2rem; margin-bottom:2.8rem; }
.menu-text { flex:0 0 52%; }
.menu-badge {
  display:inline-block; background:rgba(247,98,42,.13); color:var(--orange); font-size:.8rem; font-weight:700;
  padding:.32rem .9rem; border-radius:20px; margin-bottom:.8rem; border:1px solid rgba(247,98,42,.28);
}
.menu-title { font-family:'Nunito',sans-serif; font-size:2.6rem; font-weight:900; color:var(--orange); margin-bottom:.8rem; line-height:1.2; }
.menu-desc { color:#7A5035; font-size:.95rem; line-height:1.75; }

/* ─── Animasi Motor ─── */
.menu-motor-zone { flex:0 0 43%; }
.road-scene { position:relative; width:100%; height:210px; display:flex; flex-direction:column; justify-content:flex-end; }
.tree { position:absolute; top:12px; font-size:1.9rem; z-index:2; pointer-events:none; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none; }
.tree.t1{left:3%} .tree.t2{left:24%} .tree.t3{left:47%} .tree.t4{left:68%} .tree.t5{right:2%}
.road-edge { width:100%; height:11px; background:repeating-linear-gradient(90deg,#E8B84B 0,#E8B84B 18px,#C89B3C 18px,#C89B3C 36px); }
.road-edge.top { border-radius:6px 6px 0 0; }
.road-edge.bot { border-radius:0 0 6px 6px; }
.road {
  width:100%; height:74px;
  background:linear-gradient(180deg,#585858 0%,#3d3d3d 50%,#282828 100%);
  position:relative; overflow:hidden;
}
.road::before {
  content:''; position:absolute; top:50%; left:-100%; right:0; height:4px;
  transform:translateY(-50%);
  background:repeating-linear-gradient(90deg,#FFD700 0,#FFD700 32px,transparent 32px,transparent 58px);
  animation:roadScroll 1s linear infinite;
}
@keyframes roadScroll { from{left:-90px} to{left:0} }
.road::after {
  content:''; position:absolute; inset:0; opacity:.1;
  background:repeating-linear-gradient(45deg,#999 0,#999 1px,transparent 1px,transparent 6px);
}

/* Motor */
.motor-rider {
  position:absolute; bottom:86px; z-index:5;
  animation:rideBack 5.5s ease-in-out infinite;
  will-change:left,transform;
}
@keyframes rideBack {
  0%   { left:3%;  transform:scaleX(1);  }
  44%  { left:72%; transform:scaleX(1);  }
  50%  { left:72%; transform:scaleX(-1); }
  94%  { left:3%;  transform:scaleX(-1); }
  100% { left:3%;  transform:scaleX(1);  }
}
.motor-body {
  position:relative; width:115px; height:82px;
  display:flex; align-items:flex-end; justify-content:center;
}
.motor-body img { width:115px; height:82px; object-fit:contain; filter:drop-shadow(0 6px 14px rgba(0,0,0,.4)); }
.motor-ph {
  width:115px; height:82px;
  background:rgba(247,98,42,.12); border:2.5px dashed rgba(247,98,42,.5);
  border-radius:12px; display:flex; flex-direction:column;
  align-items:center; justify-content:center; gap:.25rem;
}
.motor-ph .mi { font-size:2.4rem; line-height:1; }
.motor-ph .mt { font-size:.52rem; color:var(--orange-dark); font-weight:800; text-align:center; line-height:1.3; }
.wheel {
  position:absolute; bottom:-5px; width:19px; height:19px; border-radius:50%;
  background:radial-gradient(circle,#666,#222); border:3px solid #999;
  animation:spin .5s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }
.wheel.f { right:7px; } .wheel.b { left:7px; }

/* Tombol menu */
.menu-btns { display:flex; gap:1.2rem; margin-bottom:3.5rem; flex-wrap:wrap; }
.menu-btn {
  background:linear-gradient(135deg,#F86F2D,#F7A030); color:white;
  padding:1.05rem 2rem; border-radius:50px; font-weight:800; font-size:1rem;
  border:none; cursor:pointer; text-decoration:none;
  box-shadow:0 6px 22px rgba(247,98,42,.42);
  transition:all .3s cubic-bezier(.34,1.56,.64,1);
  display:flex; align-items:center; gap:.6rem;
  flex:1; justify-content:center; min-width:200px; letter-spacing:.4px;
}
.menu-btn:hover { transform:translateY(-6px) scale(1.03); box-shadow:0 12px 30px rgba(247,98,42,.58); }

/* Why */
.why-box {
  background:linear-gradient(120deg,#E83C0F 0%,#F76F2D 42%,#F7A030 100%);
  border-radius:22px; padding:2.6rem 2rem;
  box-shadow:0 12px 44px rgba(232,56,13,.3);
}
.why-box h3 { text-align:center; color:white; font-size:1.35rem; font-weight:800; margin-bottom:2rem; font-family:'Nunito',sans-serif; }
.why-cards { display:flex; gap:1.5rem; justify-content:center; flex-wrap:wrap; }
.why-card { text-align:center; color:white; flex:1; min-width:145px; display:flex; flex-direction:column; align-items:center; gap:.65rem; }
.why-icon-circle {
  width:58px; height:58px; border-radius:50%;
  background:rgba(255,255,255,.2); border:2px solid rgba(255,255,255,.55);
  display:flex; align-items:center; justify-content:center; font-size:1.5rem;
  transition:all .35s; box-shadow:0 4px 12px rgba(0,0,0,.15);
}
.why-card:hover .why-icon-circle { background:rgba(255,255,255,.42); transform:scale(1.15) rotate(5deg); }
.why-name { font-weight:800; font-size:.97rem; }
.why-desc { font-size:.78rem; opacity:.9; line-height:1.45; }

/* ═══ ABOUT ═══ */
.section-about {
  padding:6rem 2rem;
  background:linear-gradient(160deg,#FFF3E5 0%,#FBE0C4 50%,#F8D0A8 100%);
  position:relative; overflow:hidden;
}
.section-about::before {
  content:''; position:absolute; top:-80px; right:-80px;
  width:380px; height:380px; border-radius:50%;
  background:radial-gradient(circle,rgba(247,98,42,.14),transparent 70%); pointer-events:none;
}
.section-about::after {
  content:''; position:absolute; bottom:-60px; left:-60px;
  width:300px; height:300px; border-radius:50%;
  background:radial-gradient(circle,rgba(247,160,48,.14),transparent 70%); pointer-events:none;
}
.section-about-inner { max-width:960px; margin:0 auto; position:relative; z-index:2; }

/* Judul dengan dekorasi garis */
.about-title-wrap { text-align:center; margin-bottom:3.2rem; }
.about-title-tag {
  display:inline-flex; align-items:center; gap:.5rem;
  background:rgba(247,98,42,.1); color:var(--orange);
  font-size:.8rem; font-weight:700; padding:.35rem 1rem;
  border-radius:30px; border:1px solid rgba(247,98,42,.22);
  margin-bottom:1rem; font-family:'Nunito',sans-serif;
}
.about-heading {
  font-family:'Nunito',sans-serif; font-size:2.5rem;
  font-weight:900; color:var(--orange); margin-bottom:.7rem; line-height:1.3;
}
.about-heading-deco {
  display:flex; align-items:center; justify-content:center; gap:1rem; margin-bottom:.9rem;
}
.about-line { height:3px; width:60px; border-radius:3px; background:linear-gradient(90deg,transparent,var(--orange)); }
.about-line.r { background:linear-gradient(90deg,var(--orange),transparent); }
.about-diamond { color:var(--orange); font-size:1rem; }
.about-sub { color:#8A5A3A; font-size:.97rem; line-height:1.75; }

/* Card utama */
.about-card {
  background:white; border-radius:28px; overflow:hidden;
  box-shadow:0 16px 60px rgba(0,0,0,.13); display:flex;
  transition:all .45s ease;
}
.about-card:hover { transform:translateY(-6px); box-shadow:0 24px 72px rgba(0,0,0,.18); }

/* Strip kiri warna */
.about-left-stripe { width:10px; flex-shrink:0; background:linear-gradient(180deg,#F7622A 0%,#F7A030 100%); }

/* Body card */
.about-card-body { flex:1; padding:3rem 2.8rem; display:flex; gap:3rem; align-items:center; }
.about-card-text { flex:1; }

/* Badge atas */
.about-card-top { display:flex; align-items:center; gap:.9rem; margin-bottom:1.5rem; flex-wrap:wrap; }
.about-year-badge {
  background:linear-gradient(135deg,#F7622A,#F7A030);
  color:white; font-size:.79rem; font-weight:800;
  padding:.38rem 1rem; border-radius:30px; white-space:nowrap;
  font-family:'Nunito',sans-serif; box-shadow:0 3px 10px rgba(247,98,42,.4);
}
.about-card-title {
  font-family:'Nunito',sans-serif; font-size:1.6rem; font-weight:900; color:var(--orange);
}
.about-card-text p { color:#7A5035; font-size:.93rem; line-height:1.9; margin-bottom:1.1rem; }
.about-card-text p:last-of-type { margin-bottom:0; }

/* Mini stats bawah */
.about-stats {
  display:flex; gap:0; margin-top:1.8rem; padding-top:1.6rem;
  border-top:2px solid rgba(247,98,42,.12); flex-wrap:wrap;
  background:linear-gradient(135deg,rgba(247,98,42,.04),rgba(247,160,48,.04));
  border-radius:0 0 16px 16px; margin-left:-2.8rem; margin-right:-2.8rem;
  margin-bottom:-3rem; padding:1.4rem 2.8rem 1.6rem;
}
.astat { text-align:center; flex:1; min-width:80px; }
.astat + .astat { border-left:1px solid rgba(247,98,42,.15); }
.astat-num { font-size:1.6rem; font-weight:900; color:var(--orange); display:block; line-height:1.1; font-family:'Nunito',sans-serif; }
.astat-lbl { font-size:.7rem; color:#9B6E50; font-weight:600; margin-top:.25rem; display:block; }

/* Logo kolom */
.about-logo-col { flex:0 0 200px; display:flex; align-items:center; justify-content:center; position:relative; }
.about-logo-col::before {
  content:''; position:absolute; inset:-12px; border-radius:28px;
  background:linear-gradient(135deg,rgba(247,98,42,.15),rgba(247,160,48,.15));
  z-index:0; animation:logoPulse 3s ease-in-out infinite;
}
@keyframes logoPulse { 0%,100%{transform:scale(1);opacity:.6} 50%{transform:scale(1.05);opacity:1} }
.about-logo-col img { width:190px; height:190px; object-fit:cover; border-radius:20px; box-shadow:0 12px 36px rgba(247,98,42,.35); border:4px solid rgba(247,98,42,.2); position:relative; z-index:1; }
.placeholder-logo {
  width:190px; height:190px; border-radius:20px;
  background:linear-gradient(135deg,#F7622A,#F59332);
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  color:white; text-align:center; gap:.6rem;
  box-shadow:0 12px 36px rgba(247,98,42,.45); font-family:'Nunito',sans-serif;
  position:relative; z-index:1;
  border:3px solid rgba(255,255,255,.3);
}
.placeholder-logo .ph-icon { font-size:3.2rem; }
.placeholder-logo .ph-text { font-size:.8rem; font-weight:800; line-height:1.4; }

/* ═══ TESTIMONI ═══ */
.section-testi { padding:5.5rem 2rem; background:linear-gradient(160deg,#FFF8F2 0%,#FFE8D0 100%); }
.section-testi-inner { max-width:1100px; margin:0 auto; }
.testi-title { text-align:center; font-family:'Nunito',sans-serif; font-size:2.6rem; font-weight:900; color:var(--orange); margin-bottom:2.8rem; letter-spacing:3px; }
.testi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-bottom:5rem; }
.testi-card {
  background:white; border-radius:18px; padding:1.8rem;
  box-shadow:0 4px 22px rgba(0,0,0,.08);
  transition:all .35s ease; border-top:4px solid transparent; position:relative; overflow:hidden;
}
.testi-card:hover { transform:translateY(-7px); box-shadow:0 14px 38px rgba(247,98,42,.18); border-top-color:var(--orange); }
/* Featured - Reizo */
.testi-card.featured {
  background:linear-gradient(145deg,#FFF8F2,#FFF0E5);
  border:2px solid var(--orange); border-top:4px solid var(--orange);
  box-shadow:0 8px 32px rgba(247,98,42,.22);
}
.testi-card.featured:hover { box-shadow:0 18px 48px rgba(247,98,42,.32); transform:translateY(-9px); }
.featured-ribbon {
  position:absolute; top:0; right:0;
  background:linear-gradient(135deg,#F7622A,#F7A030);
  color:white; font-size:.68rem; font-weight:800;
  padding:.38rem .95rem; border-radius:0 16px 0 16px; letter-spacing:.5px;
}
.testi-stars { color:#F7B731; font-size:1.15rem; margin-bottom:.9rem; letter-spacing:2px; }
.testi-text { color:#555; font-size:.88rem; line-height:1.8; margin-bottom:1.3rem; font-style:italic; }
.testi-card.featured .testi-text { color:#4A3020; font-weight:500; }
.testi-author { display:flex; align-items:center; gap:.8rem; }
.testi-avatar {
  width:44px; height:44px; border-radius:50%;
  background:linear-gradient(135deg,#4CAF50,#2E7D32);
  display:flex; align-items:center; justify-content:center;
  color:white; font-size:1.15rem; flex-shrink:0; box-shadow:0 4px 10px rgba(0,0,0,.15);
}
.testi-name { font-weight:700; color:#333; font-size:.9rem; }
.testi-role { color:#999; font-size:.77rem; }
.testi-card.featured .testi-name { color:var(--orange); }
.testi-card.featured .testi-role { color:var(--orange-dark); font-weight:700; }

/* ═══ PAYMENT ═══ */
.section-payment {
  padding:5rem 2rem;
  background:linear-gradient(160deg,#FFF8F2 0%,#FFF0E4 100%);
  text-align:center;
  position:relative;
  overflow:hidden;
}

.section-payment::before {
  content:'';
  position:absolute;
  inset:0;
  pointer-events:none;
  background:radial-gradient(
    ellipse at 50% 0%,
    rgba(247,98,42,.08),
    transparent 60%
  );
}

.section-payment-inner {
  max-width:960px;
  margin:0 auto;
  position:relative;
  z-index:2;
}

.payment-heading {
  font-family:'Nunito',sans-serif;
  font-size:2.4rem;
  font-weight:900;
  color:var(--text-dark);
  margin-bottom:.6rem;
  line-height:1.35;
}

.payment-sub {
  color:#9B7A60;
  font-size:1rem;
  margin-bottom:3.5rem;
  font-weight:500;
}

.payment-logos {
  display:flex;
  justify-content:center;
  align-items:stretch;
  gap:1.6rem;
  flex-wrap:wrap;
}

.payment-item {
  transition:all .3s cubic-bezier(.34,1.56,.64,1);
  cursor:pointer;
}

.payment-item:hover {
  transform:translateY(-10px) scale(1.05);
}

.pay-card {
  border-radius:30px; /* lebih lonjong */
  padding:2rem 2.5rem;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  gap:.7rem;
  box-shadow:0 10px 30px rgba(0,0,0,.18);
  min-width:170px;
  min-height:130px; /* lebih panjang ke bawah */
  position:relative;
  overflow:hidden;
}

.pay-card::before {
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(
    145deg,
    rgba(255,255,255,.18),
    rgba(255,255,255,.04)
  );
}

.pay-card::after {
  content:'';
  position:absolute;
  inset:0;
  opacity:0;
  background:rgba(255,255,255,.15);
  transition:opacity .25s;
}

.pay-card:hover::after {
  opacity:1;
}

.pay-label {
  font-family:'Nunito',sans-serif;
  font-size:1.55rem;
  font-weight:900;
  letter-spacing:.5px;
  color:white;
  line-height:1;
  position:relative;
  z-index:1;
}

.pay-sub {
  font-size:.72rem;
  font-weight:700;
  opacity:.9;
  letter-spacing:.5px;
  color:white;
  display:block;
  margin-top:.15rem;
  position:relative;
  z-index:1;
}

/* FIX LOGO GOPAY */
.pay-card img {
  height:55px;
  width:auto;
  object-fit:contain;
  display:block;
  position:relative;
  z-index:2;
}

/* WARNA */
.pay-gopay {
  background:linear-gradient(135deg,#00C8F0,#0090D0);
}

.pay-shopee {
  background:linear-gradient(135deg,#FF7A50,#EE4D2D);
}

.pay-qris, .pay-cod {
  background:linear-gradient(135deg,#E8470A,#B91C1C);
}

.pay-bca {
  background:linear-gradient(135deg,#1A7FD4,#004AA0);
}
/* ═══ FOOTER ═══ */
.footer { background:linear-gradient(135deg,#E8380D 0%,#F86F2D 42%,#F7A030 100%); padding:3.5rem 2rem 0; }
.footer-inner { max-width:1100px; margin:0 auto; display:grid; grid-template-columns:1.4fr 1fr 1fr 1.3fr; gap:2.5rem; padding-bottom:2.8rem; }
.footer-logo-circle { width:100px; height:100px; border-radius:50%; border:4px solid white; overflow:hidden; background:white; margin-bottom:1rem; display:flex; align-items:center; justify-content:center; }
.footer-logo-circle img { width:100%; height:100%; object-fit:cover; }
.footer-logo-circle span { font-size:.68rem; font-weight:900; color:var(--orange); text-align:center; line-height:1.2; }
.footer-stars { color:#FFD700; font-size:1.05rem; margin-bottom:.3rem; letter-spacing:2px; }
.footer-rating { color:white; font-size:.83rem; font-weight:600; margin-bottom:.9rem; opacity:.9; }
.footer-brand-desc { color:rgba(255,255,255,.85); font-size:.83rem; line-height:1.75; }
.footer-col h4 { color:white; font-size:1.02rem; font-weight:800; margin-bottom:1.1rem; font-family:'Nunito',sans-serif; }
.footer-col ul { list-style:none; }
.footer-col li { margin-bottom:.58rem; }
.footer-col a { color:rgba(255,255,255,.8); text-decoration:none; font-size:.84rem; transition:color .2s; }
.footer-col a:hover { color:white; }
.contact-item { display:flex; align-items:flex-start; gap:.5rem; color:rgba(255,255,255,.85); font-size:.84rem; margin-bottom:.75rem; line-height:1.55; }
.contact-icon { flex-shrink:0; font-size:.9rem; margin-top:.15rem; }
.footer-bottom { background:rgba(0,0,0,.22); padding:1.15rem 2rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem; }
.footer-copy { color:rgba(255,255,255,.8); font-size:.81rem; }
.footer-links { display:flex; gap:1.5rem; }
.footer-links a { color:rgba(255,255,255,.8); font-size:.81rem; text-decoration:none; transition:color .2s; }
.footer-links a:hover { color:white; }

/* ═══ FLOATING AI ═══ */
.ai-float {
  position:fixed; bottom:28px; left:28px; z-index:9999;
  text-decoration:none; cursor:pointer; display:block;
  animation:aiBounce 2s ease-in-out infinite;
  filter:drop-shadow(0 10px 28px rgba(99,102,241,.6));
  transition:filter .3s;
}
.ai-float:hover {
  animation:none; transform:scale(1.1) translateY(-4px);
  filter:drop-shadow(0 18px 36px rgba(99,102,241,.8));
}
@keyframes aiBounce {
  0%,100% { transform:translateY(0) rotate(-3deg);   }
  25%     { transform:translateY(-20px) rotate(4deg); }
  50%     { transform:translateY(-8px) rotate(-2deg); }
  75%     { transform:translateY(-16px) rotate(3deg); }
}
.ai-btn-inner {
  width:82px; height:82px; border-radius:50%;
  background:linear-gradient(135deg,#4F52E8,#7C3AED,#A855F7);
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
  border:3.5px solid rgba(255,255,255,.85);
  box-shadow:0 6px 28px rgba(99,102,241,.6), inset 0 2px 0 rgba(255,255,255,.35);
  position:relative;
}
.ai-icon  { font-size:2rem; line-height:1; }
.ai-label { font-size:.5rem; color:white; font-weight:900; letter-spacing:1px; font-family:'Nunito',sans-serif; }
/* Ping */
.ai-ping {
  position:absolute; top:-4px; right:-4px; width:20px; height:20px;
  border-radius:50%; background:#F7622A;
  display:flex; align-items:center; justify-content:center;
  border:2px solid white;
}
.ai-ping::before {
  content:''; position:absolute; inset:-2px; border-radius:50%;
  background:#F7622A; animation:pingAnim 1.4s ease-out infinite;
}
.ai-ping-dot { width:10px; height:10px; border-radius:50%; background:white; position:relative; z-index:1; }
@keyframes pingAnim { 0%{transform:scale(1);opacity:.9} 100%{transform:scale(2.6);opacity:0} }
/* Glow ring */
.ai-glow {
  position:absolute; inset:-8px; border-radius:50%;
  background:conic-gradient(#7C3AED,#A855F7,#4F52E8,#7C3AED);
  animation:spinGlow 3s linear infinite; z-index:-1; opacity:.5; filter:blur(4px);
}
@keyframes spinGlow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
/* Tooltip */
.ai-tooltip {
  position:absolute; left:94px; top:50%; transform:translateY(-50%);
  background:rgba(20,5,50,.92); color:white; font-size:.82rem; font-weight:700;
  white-space:nowrap; padding:.52rem 1.15rem; border-radius:12px;
opacity:0;
pointer-events:none;
transition:opacity .25s;
-webkit-backdrop-filter: blur(8px);
backdrop-filter: blur(8px);
  font-family:'Nunito',sans-serif;
}
.ai-tooltip::before {
  content:''; position:absolute; right:100%; top:50%; transform:translateY(-50%);
  border:7px solid transparent; border-right-color:rgba(20,5,50,.92);
}
.ai-float:hover .ai-tooltip { opacity:1; }

/* ═══ SCROLL ANIM ═══ */
.reveal   { opacity:0; transform:translateY(28px); transition:opacity .65s ease,transform .65s ease; }
.reveal-l { opacity:0; transform:translateX(-28px); transition:opacity .65s ease,transform .65s ease; }
.reveal-r { opacity:0; transform:translateX(28px);  transition:opacity .65s ease,transform .65s ease; }
.reveal.visible,.reveal-l.visible,.reveal-r.visible { opacity:1; transform:none; }
.delay-1{transition-delay:.12s} .delay-2{transition-delay:.24s} .delay-3{transition-delay:.36s}

/* ══════════════════════════════════════════
   RESPONSIVE — All Devices
   ══════════════════════════════════════════ */

/* ── Hamburger button (mobile) ── */
.hamburger {
  display:none; flex-direction:column; gap:5px;
  background:none; border:none; cursor:pointer; padding:6px; z-index:1001;
}
.hamburger span {
  display:block; width:26px; height:3px; background:var(--orange);
  border-radius:3px; transition:all .35s cubic-bezier(.68,-.55,.27,1.55);
  transform-origin:center;
}
.hamburger.open span:nth-child(1) { transform:translateY(8px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity:0; transform:scaleX(0); }
.hamburger.open span:nth-child(3) { transform:translateY(-8px) rotate(-45deg); }

/* Mobile nav overlay */
.mobile-nav {
  position:fixed; inset:0; background:rgba(255,248,240,.97);
  -webkit-backdrop-filter: blur(14px);
  backdrop-filter: blur(14px); z-index:1000;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  gap:1.8rem; transform:translateX(100%);
  transition:transform .4s cubic-bezier(.77,0,.18,1);
  pointer-events:none;
}
.mobile-nav.open { transform:translateX(0); pointer-events:all; }
.mobile-nav a {
  font-family:'Nunito',sans-serif; font-size:1.8rem; font-weight:900;
  color:var(--orange); text-decoration:none; transition:all .25s ease;
  opacity:0; transform:translateX(30px);
}
.mobile-nav.open a { opacity:1; transform:translateX(0); }
.mobile-nav.open a:nth-child(1){transition-delay:.05s}
.mobile-nav.open a:nth-child(2){transition-delay:.1s}
.mobile-nav.open a:nth-child(3){transition-delay:.15s}
.mobile-nav.open a:nth-child(4){transition-delay:.2s}
.mobile-nav.open a:nth-child(5){transition-delay:.25s}
.mobile-nav.open a:nth-child(6){transition-delay:.3s}
.mobile-nav a:hover { transform:scale(1.08); }
.mobile-nav-order {
  margin-top:.5rem;
  background:linear-gradient(135deg,var(--orange),var(--orange2));
  color:white!important; padding:1rem 2.5rem;
  border-radius:50px; font-size:1.2rem!important;
  box-shadow:0 6px 20px rgba(247,98,42,.4);
  opacity:0; transform:translateX(30px);
  transition:opacity .4s .35s ease, transform .4s .35s ease!important;
}
.mobile-nav.open .mobile-nav-order { opacity:1!important; transform:none!important; }

/* ── 1200px – Large desktop tweaks ── */
@media(max-width:1200px){
  .hero-title { font-size:3.2rem; }
  .footer-inner { gap:1.8rem; }
}

/* ── 1024px – Small laptop / tablet landscape ── */
@media(max-width:1024px){
  .hero-glass { padding:3rem 2.8rem 2.6rem; }
  .hero-title { font-size:2.9rem; }
  .menu-title { font-size:2.2rem; }
  .about-heading { font-size:2.1rem; }
  .testi-grid { grid-template-columns:repeat(3,1fr); gap:1.1rem; }
  .footer-inner { grid-template-columns:1.2fr 1fr 1fr; }
  .footer-inner > div:last-child { grid-column:1/-1; }
  .payment-logos { gap:1.2rem; }
  .pay-card { padding:1.3rem 2rem; min-width:135px; }
}

/* ── 900px – Tablet portrait ── */
@media(max-width:900px){
  /* Navbar */
  .nav-links { display:none; }
  .hamburger { display:flex; }
  .nav-phone { display:none; }

  /* Hero */
 .hero {
  padding: 2.5rem 1.5rem;
  min-height: auto;
}
  .hero-glass { padding:2.8rem 2.2rem 2.4rem; border-radius:22px; }
  .hero-title { font-size:2.5rem; }
  .hero-sub { font-size:.97rem; }
  .hero-stats { gap:1.2rem; }
  .stat-num { font-size:1.4rem; }

  /* Menu */
  .section-menu { padding:4rem 1.5rem; }
  .menu-top { flex-direction:column; gap:2rem; }
  .menu-text,.menu-motor-zone { flex:none; width:100%; }
  .menu-motor-zone { max-width:480px; margin:0 auto; }
  .menu-btns { gap:.8rem; }

  /* About */
  .about-card-body { flex-direction:column; padding:2.2rem 2rem; gap:2rem; }
  .about-logo-col { flex:none; }
  .about-stats { margin-left:-2rem; margin-right:-2rem; }

  /* Testi */
  .testi-grid { grid-template-columns:1fr 1fr; gap:1.2rem; }

  /* Payment */
  .pay-card { padding:1.2rem 1.6rem; min-width:120px; }
  .pay-label { font-size:1.3rem; }

  /* Footer */
  .footer-inner { grid-template-columns:1fr 1fr; gap:2rem; }

  /* AI float */
  .ai-float { bottom:20px; left:20px; }
  .ai-btn-inner { width:70px; height:70px; }
  .ai-icon { font-size:1.7rem; }
}

/* ── 768px – Large phone / small tablet ── */
@media(max-width:768px){
  .hero-title { font-size:2.1rem; letter-spacing:-.5px; }
  .hero-stats { gap:.8rem; flex-wrap:wrap; justify-content:center; }
  .stat-num { font-size:1.3rem; }
  .hero-btns { gap:.6rem; }
  .btn-order-qris,.btn-menu,.btn-ig { padding:.7rem 1.2rem; font-size:.85rem; }

  .why-cards { flex-wrap:wrap; }
  .why-card { flex:0 0 calc(50% - .75rem); }

  .about-heading { font-size:1.8rem; }
  .about-card-title { font-size:1.35rem; }

  .testi-title { font-size:2rem; letter-spacing:1px; }
  .testi-grid { grid-template-columns:1fr; }

  .payment-heading { font-size:1.9rem; }
  .payment-logos { gap:1rem; }
  .pay-card { min-width:110px; padding:1.1rem 1.4rem; }
  .pay-label { font-size:1.15rem; }

  .footer-inner { grid-template-columns:1fr; gap:1.8rem; }
  .footer-bottom { flex-direction:column; text-align:center; gap:.6rem; }
}

/* ── 480px – Phone ── */
@media(max-width:480px){
  .navbar { padding:0 1rem; }
  .btn-nav { padding:.5rem 1rem; font-size:.82rem; }
  .btn-login { padding:.45rem .8rem; font-size:.8rem; }
  .logo-text { display:none; }

  .hero { padding:1.5rem 1rem; }
  .hero-glass { padding:2rem 1.4rem 1.8rem; border-radius:18px; }
  .hero-inner { max-width:100%; }
  .hero-badge { font-size:.75rem; padding:.38rem 1rem; }
  .hero-title { font-size:1.75rem; }
  .hero-sub { font-size:.9rem; }
  .hero-stats { gap:.6rem; }
  .stat-num { font-size:1.2rem; }
  .stat-lbl { font-size:.65rem; }
  .hero-btns { flex-direction:column; gap:.5rem; align-items:center; }
  .btn-order-qris,.btn-menu,.btn-ig { width:100%; max-width:280px; justify-content:center; }
  .hero-badges { gap:.8rem; font-size:.72rem; }

  .section-menu { padding:3rem 1rem; }
  .menu-title { font-size:1.9rem; }
  .menu-btns { flex-direction:column; align-items:center; }
  .menu-btn { width:100%; max-width:300px; font-size:.92rem; }
  .why-card { flex:0 0 100%; }

  .section-about { padding:3.5rem 1rem; }
  .about-heading { font-size:1.65rem; }
  .about-card-body { padding:1.8rem 1.4rem; }
  .about-stats { margin-left:-1.4rem; margin-right:-1.4rem; padding-left:1.4rem; padding-right:1.4rem; }

  .section-testi { padding:3.5rem 1rem; }
  .testi-title { font-size:1.7rem; letter-spacing:1px; }
  .testi-card { padding:1.4rem; }

  .section-payment { padding:3.5rem 1rem; }
  .payment-heading { font-size:1.55rem; }
  .payment-logos { gap:.8rem; }
  .pay-card { min-width:calc(50% - .4rem); padding:1rem 1rem; border-radius:16px; }
  .pay-label { font-size:1rem; }
  .pay-sub { font-size:.62rem; }

  .footer { padding:2.5rem 1rem 0; }
  .footer-inner { gap:1.4rem; padding-bottom:2rem; }
  .footer-links { flex-wrap:wrap; justify-content:center; gap:.8rem; }

  .ai-float { bottom:14px; left:14px; }
  .ai-btn-inner { width:62px; height:62px; }
  .ai-icon { font-size:1.5rem; }
  .ai-label { font-size:.42rem; }
  .ai-tooltip { display:none; }

  .road-scene { height:170px; }
}

/* ── 360px – Very small phones ── */
@media(max-width:360px){
  .hero-title { font-size:1.5rem; }
  .hero-glass { padding:1.6rem 1.1rem; }
  .pay-card { min-width:100%; }
  .payment-logos { flex-direction:column; align-items:center; }
  .pay-card { width:100%; max-width:260px; }
}
</style>
</head>
<body>

<a href="../Yayubi AI/Yayubi AI.html"
   class="ai-float"
   target="_blank"
   title="Tanya AI Kami!">
  <div class="ai-btn-inner">
    <div class="ai-glow"></div>
    <img src="logo yayubi.jpeg" style="width:40px;height:40px;border-radius:50%;">
    <span class="ai-label">AI CHAT</span>
    <div class="ai-ping"><div class="ai-ping-dot"></div></div>
  </div>
  <div class="ai-tooltip">💬 Tanya AI Kami!</div>
</a>

<!-- ═══ MOBILE NAV OVERLAY ═══ -->
<div class="mobile-nav" id="mobileNav">
  <div style="padding:1rem 0; font-size:1.1rem; font-weight:800; color:var(--orange);">👤 Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pelanggan') ?></div>
  <a href="#beranda"    onclick="closeMobileNav()">Beranda</a>
  <a href="#menu"       onclick="closeMobileNav()">Layanan</a>
  <a href="#about"      onclick="closeMobileNav()">Tentang</a>
  <a href="#payment"    onclick="closeMobileNav()">Pembayaran</a>
  <a href="#footer"     onclick="closeMobileNav()">Kontak</a>
  <a href="../profil_bisnis/Code/tampilanbantuan.html" onclick="closeMobileNav()">Bantuan</a>
  <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')): ?>
    <a href="../sistem_admin/view/admin/dashboard.php" onclick="closeMobileNav()" style="color:#7C3AED;font-weight:800;">⚙️ Dashboard Admin</a>
  <?php endif; ?>
  <a href="../sistem_admin/view/logout.php" onclick="closeMobileNav()" style="color:#DC2626;font-weight:800;">🚪 Keluar / Logout</a>
  <a href="#menu" class="mobile-nav-order" onclick="closeMobileNav()">🛒 Pesan Menu Sekarang</a>
</div>

<!-- ═══ NAVBAR ═══ -->
<nav class="navbar">
  <a href="../profil_bisnis/Code/tampilanprofilbisnis.html" class="nav-logo">
    <div class="nav-logo-img">
      <img src="logo yayubi.jpeg" alt="Logo Yayubi">
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="#beranda">Beranda</a></li>
    <li><a href="#menu">Layanan</a></li>
    <li><a href="#about">Tentang</a></li>
    <li><a href="#payment">Pembayaran</a></li>
    <li><a href="#footer">Kontak</a></li>
    <li><a href="../profil_bisnis/Code/tampilanbantuan.html">Bantuan</a></li>
  </ul>
  <div class="nav-right">
    <a href="tel:0895422562858" class="nav-phone">📞 082136551189</a>
    <span style="font-size:0.85rem; font-weight:700; color:var(--text-dark); background:#FFF0E5; padding:0.4rem 0.85rem; border-radius:20px; border:1px solid #F59332;">
      👤 <?= htmlspecialchars($_SESSION['nama'] ?? 'Pelanggan') ?>
    </span>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')): ?>
      <a href="../sistem_admin/view/admin/dashboard.php" class="btn-login" style="background:#7C3AED; color:white; border-color:#7C3AED;">⚙️ Admin</a>
    <?php endif; ?>
    <a href="../sistem_admin/view/logout.php" class="btn-login" style="border-color:#DC2626; color:#DC2626;" title="Keluar / Logout">🚪 Keluar</a>
    <a href="#menu" class="btn-nav">Pesan Sekarang</a>
    <button class="hamburger" id="hamburger" onclick="toggleMobileNav()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ═══ HERO ═══ -->
<section class="hero" id="beranda">
  <div class="hero-deco d1">🍱</div>
  <div class="hero-deco d2">🍜</div>
  <div class="hero-deco d3">🍢</div>
  <div class="hero-deco d4">🍪</div>
  <div class="hero-deco d5">🥘</div>
  <div class="hero-inner">
    <div class="hero-glass">
      <div class="hero-badge">🔒 Catering Dapoer Yayubi Terpercaya #1</div>
      <h1 class="hero-title">CATERING DAPOER YAYUBI</h1>
      <p class="hero-sub">Rayakan Momen Berharga Dengan Layanan Terbaik,<br>Kami Hadir Untuk Memenuhi Kebutuhan Kalian!</p>
      <div class="hero-stats">
        <div class="stat"><div class="stat-icon">⭐</div><span class="stat-num">4.9</span><span class="stat-lbl">Rating Pelanggan</span></div>
        <div class="stat"><div class="stat-icon">👥</div><span class="stat-num">300+</span><span class="stat-lbl">Acara Tersaji</span></div>
        <div class="stat"><div class="stat-icon">🕐</div><span class="stat-num">10+</span><span class="stat-lbl">Tahun Pengalaman</span></div>
        <div class="stat"><div class="stat-icon">🍽️</div><span class="stat-num">25+</span><span class="stat-lbl">Menu Variasi</span></div>
      </div>
      <div class="hero-btns">
        <a href="#menu" class="btn-order-qris">🛒 Pesan Sekarang</a>
        <a href="../profil_bisnis/Code/tampilanseluruhmenu.php" class="btn-menu">Lihat Menu</a>
        <a href="https://instagram.com" class="btn-ig" target="_blank">📸 Lihat Instagram</a>
      </div>
      <div class="hero-badges">
        <span class="hbadge"><span class="dot dot-green"></span> Enak &amp; Halal</span>
        <span class="hbadge"><span class="dot dot-blue"></span> Gratis Konsultasi</span>
        <span class="hbadge"><span class="dot dot-yellow"></span> Garansi Kualitas</span>
      </div>
    </div>
  </div>
</section>

<!-- ═══ MENU ═══ -->
<section class="section-menu" id="menu">
  <div class="section-menu-inner">
    <div class="menu-top">
      <div class="menu-text reveal-l">
        <div class="menu-badge">Daftar Menu</div>
        <h2 class="menu-title">Pilihan Menu</h2>
        <p class="menu-desc">Kami menyediakan berbagai pilihan menu catering yang dapat<br>disesuaikan dengan kebutuhan dan selera Anda.</p>
      </div>

      <!-- Animasi motor di jalan -->
      <div class="menu-motor-zone reveal-r">
        <div class="road-scene">
          <div class="tree t1">🌳</div>
          <div class="tree t2">🌴</div>
          <div class="tree t3">🌳</div>
          <div class="tree t4">🌴</div>
          <div class="tree t5">🌳</div>
          <!-- Motor bergerak -->
          <div class="motor-rider">
            <div class="motor-body">
              <img src="motor.png" alt="Motor">
            </div>
          </div>
          <div class="road-edge top"></div>
          <div class="road"></div>
          <div class="road-edge bot"></div>
        </div>
      </div>
    </div>

    <div class="menu-btns">
      <a href="../tampilan_menu/nasibox.php" class="menu-btn reveal">⭐ MENU CATERING</a>
      <a href="../tampilan_menu/tumpeng.php" class="menu-btn reveal delay-1">⭐ MENU NASI TUMPENG</a>
      <a href="../tampilan_menu/snack.php" class="menu-btn reveal delay-2">⭐ MENU SNACK</a>
    </div>

    <div class="why-box reveal">
      <h3>Mengapa Memilih Kami?</h3>
      <div class="why-cards">
        <div class="why-card"><div class="why-icon-circle">👤</div><div class="why-name">Halal</div><div class="why-desc">Makanan yang terdaftar sudah terverifikasi halal</div></div>
        <div class="why-card"><div class="why-icon-circle">⏰</div><div class="why-name">Tepat Waktu</div><div class="why-desc">Jaminan pesanan sesuai jadwal</div></div>
        <div class="why-card"><div class="why-icon-circle">🛡️</div><div class="why-name">Higienis &amp; Aman</div><div class="why-desc">Standar kebersihan tinggi &amp; halal</div></div>
        <div class="why-card"><div class="why-icon-circle">⭐</div><div class="why-name">Kualitas Terbaik</div><div class="why-desc">Kami menjaga mutu di setiap masakan</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ ABOUT ═══ -->
<section class="section-about" id="about">
  <div class="section-about-inner">
    <div class="about-title-wrap reveal">
      <div class="about-title-tag">🏆 Tentang Kami</div>
      <h2 class="about-heading">Catering Dapoer Yayubi<br>Terpercaya #1</h2>
      <div class="about-heading-deco">
        <div class="about-line"></div>
        <span class="about-diamond">◆</span>
        <div class="about-line r"></div>
      </div>
      <p class="about-sub">Sejak 2015, kami telah menjadi pilihan utama untuk layanan catering di<br>Semarang. Dengan komitmen pada kualitas, cita rasa, dan pelayanan terbaik.</p>
    </div>
    <div class="about-card reveal">
      <div class="about-left-stripe"></div>
      <div class="about-card-body">
        <div class="about-card-text">
          <div class="about-card-top">
            <div class="about-year-badge">📅 Sejak 2015</div>
            <h3 class="about-card-title">Cerita Kami Dimulai dari 2015</h3>
          </div>
          <p>Cathering Waroeng Yayubi berawal dari kecintaan pada kuliner Nusantara serta keinginan untuk menghadirkan pengalaman catering terbaik di setiap acara. Kami percaya bahwa setiap momen spesial memerlukan hidangan yang dibuat dengan perhatian dan kualitas.</p>
          <p>Dengan dukungan chef berpengalaman dan tim yang profesional, Waroeng Yayubi terus berinovasi menghadirkan menu yang memadukan cita rasa tradisional dan sentuhan modern. Komitmen kami adalah memberikan pelayanan yang terbaik dan meninggalkan kesan yang hangat di setiap hidangan.</p>
          <div class="about-stats">
            <div class="astat"><span class="astat-num">10+</span><span class="astat-lbl">Tahun Berpengalaman</span></div>
            <div class="astat"><span class="astat-num">300+</span><span class="astat-lbl">Acara Sukses</span></div>
            <div class="astat"><span class="astat-num">25+</span><span class="astat-lbl">Menu Tersedia</span></div>
            <div class="astat"><span class="astat-num">4.9⭐</span><span class="astat-lbl">Rating Pelanggan</span></div>
          </div>
        </div>
        <div class="about-logo-col">
            <img src="logo yayubi.jpeg" alt="Logo Yayubi">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ TESTIMONI ═══ -->
<section class="section-testi" id="testimonial">
  <div class="section-testi-inner">
    <h2 class="testi-title reveal">TESTIMONI</h2>
    <div class="testi-grid">
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Saya sering pesan untuk acara arisan dan pengajian di rumah. Menu prassmannya lengkap dan tampilannya menarik. Pelayanannya juga ramah dan responsif. Pokoknya top deh!"</p>
        <div class="testi-author"><div class="testi-avatar">👤</div><div><div class="testi-name">Ibu Ibanisa Pratiwi</div><div class="testi-role">Pengusaha</div></div></div>
      </div>

      <!-- FEATURED - Reizo (Ketua/Manager style) -->
      <div class="testi-card featured reveal delay-1">
        <div class="featured-ribbon">👑 Ketua Pelanggan</div>
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Untuk acara kantor kemarin, kami pesan nasi box untuk 100 orang. Hasilnya memuaskan! Packaging rapi, makanan masih hangat, rasanya juara. Harga bersahabat untuk kantong perusahaan. Recommended!"</p>
        <div class="testi-author">
          <div class="testi-avatar" style="background:linear-gradient(135deg,#F7622A,#F7A030); font-size:1.3rem;">👔</div>
          <div>
            <div class="testi-name">Bapak Reizo Rajendra Dianta</div>
            <div class="testi-role">🏢 Manajer Perusahaan</div>
          </div>
        </div>
      </div>

      <div class="testi-card reveal delay-2">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Alhamdulillah, sudah langganan hampir 3 bulan. Makanannya enak, porsi pas, dan selalu tepat waktu. Anak-anak di rumah juga suka banget sama menu-menunya yang bervariasi setiap hari."</p>
        <div class="testi-author"><div class="testi-avatar">👤</div><div><div class="testi-name">Ibu Fanessa Anandita Putri</div><div class="testi-role">Ibu Rumah Tangga</div></div></div>
      </div>

      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Sudah beberapa kali pesan nasi kotak untuk acara keluarga. Selalu puas dengan kualitas dan pelayanannya. Harga kompetitif, rasa mantap, adminnya fast respon. Sukses terus!"</p>
        <div class="testi-author"><div class="testi-avatar">👤</div><div><div class="testi-name">Mas Ubaydilla Syahpalevi</div><div class="testi-role">Karyawan Swasta</div></div></div>
      </div>

      <div class="testi-card reveal delay-1">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Untuk acara perpisahan sekolah, kami memesan snack box dan nasi box. Semua berjalan lancar, makanan datang tepat waktu. Anak-anak dan guru-guru sangat puas!"</p>
        <div class="testi-author"><div class="testi-avatar">👤</div><div><div class="testi-name">Ibu Cahaya Fitri Darmawan</div><div class="testi-role">Anak Pemilik Catering</div></div></div>
      </div>

      <div class="testi-card reveal delay-2">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-text">"Sebagai mahasiswa yang kos-kosan, catering ini sangat membantu! Nggak perlu repot masak, harganya terjangkau, makanannya enak dan bersih. Pengiriman ke area kampus juga cepat!"</p>
        <div class="testi-author"><div class="testi-avatar">👤</div><div><div class="testi-name">Mbak Diva Risnu</div><div class="testi-role">Mahasiswi</div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ PAYMENT ═══ -->
<section class="section-payment" id="payment">
  <div class="section-payment-inner">
    <h2 class="payment-heading reveal">Pembayaran Otomatis &amp; Instan<br>Didukung Midtrans</h2>
    <p class="payment-sub reveal">Scan QRIS (Semua Bank &amp; E-Wallet) atau Transfer Bank otomatis terverifikasi realtime 💳</p>
    <div class="payment-logos reveal">

      <div class="payment-item">
        <div class="pay-card pay-qris">
          <div style="font-size:1.8rem;font-weight:900;color:white;letter-spacing:1px;font-family:'Plus Jakarta Sans',sans-serif;">QRIS</div>
          <span class="pay-sub">Semua Bank &amp; E-Wallet</span>
        </div>
      </div>

      <div class="payment-item">
        <div class="pay-card pay-gopay">
          <img src="logo gopay.png" alt="GoPay">
          <span class="pay-sub">GoPay Midtrans</span>
        </div>
      </div>

      <div class="payment-item">
        <div class="pay-card pay-shopee">
           <img src="logo spay.png" alt="spay">
          <span class="pay-sub">ShopeePay Midtrans</span>
        </div>
      </div>

      <div class="payment-item">
        <div class="pay-card pay-bca">
            <img src="logo bca.png" alt="BCA">
          <span class="pay-sub">Transfer Virtual Account</span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer class="footer" id="footer">
  <div class="footer-inner">
    <div>
      <div class="footer-logo-circle">
        <img src="logo yayubi.jpeg" alt="Yayubi">
      </div>
      <div class="footer-stars">★★★★★</div>
      <div class="footer-rating">4.9/5 (500+ reviews)</div>
      <p class="footer-brand-desc">Catering di Semarang dengan pengalaman 6+ tahun. Spesial Nasi Box &amp; Catering Harian. Menyajikan cita rasa masakan nusantara.</p>
    </div>
    <div class="footer-col">
      <h4>Menu Utama</h4>
      <ul>
        <li><a href="#beranda">Beranda</a></li>
        <li><a href="#menu">Layanan</a></li>
        <li><a href="#about">Tentang Kami</a></li>
        <li><a href="#footer">Kontak</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Layanan Kami</h4>
      <ul>
        <li><a href="#">Daily Office Meals</a></li>
        <li><a href="#">Nasi Box &amp; Snack Box</a></li>
        <li><a href="#">Wedding Catering</a></li>
        <li><a href="#">Party &amp; Event</a></li>
        <li><a href="#">Meeting Package</a></li>
        <li><a href="#">Instansi</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Hubungi Kami</h4>
      <div class="contact-item"><span class="contact-icon">📱</span> 082136551189</div>
      <div class="contact-item"><span class="contact-icon">✉️</span> Dapoeryayubi@gmail.com</div>
      <div class="contact-item"><span class="contact-icon">📍</span> JL. CINDE BARAT VII NO 5, SEMARANG, JAWA TENGAH.</div>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="footer-copy">© 2025 Catering Dapoer Yayubi.</span>
    <div class="footer-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
      <a href="#">Syarat &amp; Ketentuan</a>
    </div>
  </div>
</footer>

<script>
  // ── Hamburger mobile nav ──
  function toggleMobileNav() {
    const nav = document.getElementById('mobileNav');
    const btn = document.getElementById('hamburger');
    nav.classList.toggle('open');
    btn.classList.toggle('open');
    document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
  }
  function closeMobileNav() {
    document.getElementById('mobileNav').classList.remove('open');
    document.getElementById('hamburger').classList.remove('open');
    document.body.style.overflow = '';
  }
  // Tutup saat klik di luar
  document.getElementById('mobileNav').addEventListener('click', function(e){
    if(e.target === this) closeMobileNav();
  });

  // ── Scroll Reveal ──
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); } });
  }, { threshold:.08 });
  document.querySelectorAll('.reveal,.reveal-l,.reveal-r').forEach(el => obs.observe(el));

  // ── Smooth scroll ──
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const t = document.querySelector(a.getAttribute('href'));
      if(t){ e.preventDefault(); t.scrollIntoView({behavior:'smooth'}); }
    });
  });

  // ── Navbar shadow on scroll ──
  window.addEventListener('scroll', () => {
    document.querySelector('.navbar').style.boxShadow =
      window.scrollY > 60 ? '0 4px 32px rgba(0,0,0,.16)' : '0 2px 20px rgba(0,0,0,.08)';
  });

  // ── Counter animation ──
  let counted = false;
  const countObs = new IntersectionObserver(entries => {
    if(entries[0].isIntersecting && !counted){
      counted = true;
      document.querySelectorAll('.stat-num').forEach(el => {
        const raw = el.textContent;
        const isFloat = raw.includes('.');
        const suffix = raw.replace(/[\d.]/g,'');
        const target = parseFloat(raw);
        let cur = 0; const step = target/60;
        const t = setInterval(() => {
          cur += step;
          if(cur >= target){ cur = target; clearInterval(t); }
          el.textContent = (isFloat ? cur.toFixed(1) : Math.floor(cur)) + suffix;
        }, 18);
      });
    }
  }, { threshold:.5 });
  const heroSection = document.querySelector('.hero');
  if(heroSection) countObs.observe(heroSection);
</script>
</body>
</html>
