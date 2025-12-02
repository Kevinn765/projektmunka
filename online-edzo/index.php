<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Személyi Edző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0e27, #0d1117, #1a1f3a);
      color: #e6edf3;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    /* Animated background particles */
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }
    
    .particle {
      position: absolute;
      width: 4px;
      height: 4px;
      background: rgba(88, 166, 255, 0.4);
      border-radius: 50%;
      animation: float 20s infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
    }
    
    /* Navigation */
    nav {
      background: rgba(10, 14, 39, 0.8);
      backdrop-filter: blur(20px);
      padding: 20px 50px;
      border-bottom: 1px solid rgba(88, 166, 255, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
    }
    
    .logo {
      font-size: 1.8rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -1px;
    }
    
    nav a {
      color: #9bbcff;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      padding: 8px 20px;
      border-radius: 8px;
      position: relative;
    }
    
    nav a:hover {
      color: #58a6ff;
      background: rgba(88, 166, 255, 0.1);
    }
    
    /* Hero Section */
    .hero {
      text-align: center;
      padding: 100px 20px 80px;
      position: relative;
      z-index: 1;
    }
    
    .hero h1 {
      font-size: 4rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636, #fbbf24);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 25px;
      line-height: 1.2;
      animation: fadeInUp 0.8s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .hero p {
      color: #9ca3af;
      font-size: 1.4rem;
      margin-bottom: 40px;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
      animation: fadeInUp 1s ease;
    }
    
    .btn-main {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      padding: 18px 50px;
      font-weight: 700;
      font-size: 1.2rem;
      border: none;
      border-radius: 15px;
      transition: all 0.4s;
      display: inline-block;
      text-decoration: none;
      box-shadow: 0 8px 30px rgba(35, 134, 54, 0.4);
      animation: fadeInUp 1.2s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-main::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: 0.5s;
    }
    
    .btn-main:hover::before {
      left: 100%;
    }
    
    .btn-main:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(35, 134, 54, 0.6);
      color: white;
    }
    
    /* Stats Section */
    .stats-section {
      background: rgba(10, 14, 39, 0.5);
      padding: 80px 40px;
      border-top: 1px solid rgba(88, 166, 255, 0.1);
      border-bottom: 1px solid rgba(88, 166, 255, 0.1);
      position: relative;
      z-index: 1;
    }
    
    .stats-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 50px;
      text-align: center;
    }
    
    .stat-item {
      padding: 30px;
      background: rgba(88, 166, 255, 0.03);
      border-radius: 20px;
      border: 1px solid rgba(88, 166, 255, 0.1);
      transition: all 0.3s;
    }
    
    .stat-item:hover {
      transform: translateY(-10px);
      background: rgba(88, 166, 255, 0.08);
      box-shadow: 0 10px 40px rgba(88, 166, 255, 0.2);
    }
    
    .stat-number {
      font-size: 4rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      display: block;
      margin-bottom: 15px;
    }
    
    .stat-label {
      font-size: 1.2rem;
      color: #9ca3af;
      font-weight: 500;
    }
    
    /* Features Section */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 35px;
      padding: 80px 50px;
      max-width: 1400px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }
    
    .feature-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      backdrop-filter: blur(10px);
      color: #fff;
      border-radius: 25px;
      padding: 40px 35px;
      transition: all 0.4s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-decoration: none;
      position: relative;
      overflow: hidden;
      min-height: 320px;
    }
    
    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(88, 166, 255, 0.1), rgba(35, 134, 54, 0.1));
      opacity: 0;
      transition: opacity 0.4s;
    }
    
    .feature-card:hover::before {
      opacity: 1;
    }
    
    .feature-card:hover {
      transform: translateY(-12px) scale(1.02);
      border-color: #238636;
      box-shadow: 0 20px 60px rgba(35, 134, 54, 0.4);
    }
    
    .feature-icon {
      font-size: 4rem;
      margin-bottom: 25px;
      display: block;
      transition: transform 0.4s;
      position: relative;
      z-index: 1;
    }
    
    .feature-card:hover .feature-icon {
      transform: scale(1.2) rotate(5deg);
    }
    
    .feature-card h5 {
      font-weight: 700;
      margin-bottom: 18px;
      color: #58a6ff;
      font-size: 1.6rem;
      position: relative;
      z-index: 1;
    }
    
    .feature-card p {
      line-height: 1.7;
      color: #d1d5db;
      font-size: 1.05rem;
      position: relative;
      z-index: 1;
    }
    
    .coming-soon {
      background: rgba(251, 191, 36, 0.1);
      border: 1px solid rgba(251, 191, 36, 0.3);
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 700;
      color: #fbbf24;
      display: inline-block;
      margin-top: 15px;
      position: relative;
      z-index: 1;
    }
    
    /* Footer */
    footer {
      text-align: center;
      padding: 50px 20px;
      border-top: 1px solid rgba(88, 166, 255, 0.1);
      color: #6b7280;
      background: rgba(10, 14, 39, 0.8);
      position: relative;
      z-index: 1;
      font-size: 1rem;
    }
    
    footer a {
      color: #58a6ff;
      text-decoration: none;
      transition: 0.3s;
    }
    
    footer a:hover {
      color: #238636;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      .hero h1 {
        font-size: 2.5rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .features {
        grid-template-columns: 1fr;
        padding: 40px 20px;
      }
      
      .stats-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
      }
      
      .stat-number {
        font-size: 3rem;
      }
    }
    
    @media (max-width: 480px) {
      .hero h1 {
        font-size: 2rem;
      }
      
      .btn-main {
        padding: 14px 35px;
        font-size: 1rem;
      }
      
      .stats-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Animated particles -->
  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
    <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 50%; animation-delay: 8s;"></div>
    <div class="particle" style="left: 60%; animation-delay: 10s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 80%; animation-delay: 14s;"></div>
    <div class="particle" style="left: 90%; animation-delay: 16s;"></div>
  </div>

  <nav class="d-flex justify-content-between align-items-center">
    <div class="logo">💪 OnlineEdző</div>
    <div>
      <?php if (isset($_SESSION['user_id'])): ?>
<a href="settings.php">⚙️ Beállítások</a>
        <a href="logout.php">Kijelentkezés</a>
      <?php else: ?>
        <a href="register.php">Regisztráció</a>
        <a href="login.php">Bejelentkezés</a>
      <?php endif; ?>
    </div>
  </nav>

  <?php if (isset($_SESSION['user_id'])): ?>
    <?php include 'premium_banner.php'; ?>
  <?php endif; ?>

  <section class="hero">
    <h1>Személyre szabott edzésterv<br>bárhol, bármikor</h1>
    <p>Regisztrálj, töltsd ki a profilod, és kezd el a fejlődést még ma!</p>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="plans.php" class="btn-main">Saját terv megtekintése →</a>
    <?php else: ?>
      <a href="register.php" class="btn-main">Kezdjük el! →</a>
    <?php endif; ?>
  </section>

  <section class="stats-section">
    <div class="stats-container">
      <div class="stat-item">
        <span class="stat-number">1000+</span>
        <span class="stat-label">Aktív felhasználó</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">500+</span>
        <span class="stat-label">Gyakorlat adatbázis</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">50+</span>
        <span class="stat-label">Edzésterv sablon</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">24/7</span>
        <span class="stat-label">Elérhető bárhol</span>
      </div>
    </div>
  </section>

  <section class="features">

    <a href="plans.php" class="feature-card">
      <div>
        <span class="feature-icon">🗓️</span>
        <h5>Edzéstervek</h5>
        <p>Automatikusan generált heti edzésterv a profilod és céljaid alapján. Izomnövelés, zsírégetés vagy erősödés!</p>
      </div>
    </a>

    <a href="exercise_browser.php" class="feature-card">
      <div>
        <span class="feature-icon">🏋️</span>
        <h5>Edzéskereső</h5>
        <p>Keresés izomcsoport szerint részletes leírásokkal és helyes végrehajtási tippekkel.</p>
      </div>
    </a>

    <a href="workout_log.php" class="feature-card">
      <div>
        <span class="feature-icon">📖</span>
        <h5>Edzésnapló</h5>
        <p>Rögzítsd az edzéseidet és kövesd a fejlődésed! Sorozatok, ismétlések és súlyok nyilvántartása.</p>
      </div>
    </a>

    <a href="body_tracker.php" class="feature-card">
      <div>
        <span class="feature-icon">📏</span>
        <h5>Testméret Követés</h5>
        <p>Kövesd a testsúlyod és körméreted változását. Töltsd fel progress fotóidat!</p>
      </div>
    </a>

    <a href="workout_timer.php" class="feature-card">
      <div>
        <span class="feature-icon">⏱️</span>
        <h5>Edzés Timer</h5>
        <p>Pihenőidő számláló, stopperóra és HIIT interval timer. Minden egy helyen!</p>
      </div>
    </a>

    <a href="nutrition_log.php" class="feature-card">
      <div>
        <span class="feature-icon">🍎</span>
        <h5>Táplálkozási Napló</h5>
        <p>Kalória és makró számláló, étkezések naplózása. A sikeres átalakuláshoz!</p>
        <span class="coming-soon">⭐ Prémium</span>
      </div>
    </a>

    <a href="statistics.php" class="feature-card">
  <div>
    <span class="feature-icon">📊</span>
    <h5>Statisztikák</h5>
    <p>Részletes grafikonok a teljesítményedről. Lásd az erősödésed és személyes rekordokat!</p>
  </div>
</a>

    <a href="challenges.php" class="feature-card">
      <div>
        <span class="feature-icon">🏆</span>
        <h5>Kihívások & Célok</h5>
        <p>Állíts be célokat, teljesíts kihívásokat! Motiváld magad gamifikációval!</p>
      </div>
    </a>

    <a href="support.php" class="feature-card">
      <div>
        <span class="feature-icon">💬</span>
        <h5>Edző Támogatás</h5>
        <p>Kérdezz bármit, és személyesen válaszolok! Valódi edző segítség.</p>
        <span class="coming-soon">⭐ Prémium</span>
      </div>
    </a>

  </section>

  <footer>
  <p>© 2025 Online Személyi Edző – Minden jog fenntartva</p>
  <p style="margin-top: 10px;">
    <a href="privacy.php">Adatvédelem</a> · 
    <a href="terms.php">Felhasználási feltételek</a> ·
<a href="contact.php">Kapcsolat</a> 
  </p>
</footer>

</body>
</html>