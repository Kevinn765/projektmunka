<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$user_id = $_SESSION['user_id'];
$current_plan = getUserPlan($user_id);
$is_premium = isPremium($user_id);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prémium Upgrade - OnlineEdző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #0d1117, #161b22, #1b263b);
      color: #e6edf3;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
    }
    
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
      width: 3px;
      height: 3px;
      background: rgba(251, 191, 36, 0.3);
      border-radius: 50%;
      animation: float 25s infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100vh) translateX(30px); opacity: 0; }
    }
    
    nav {
      background: rgba(255,255,255,0.04);
      backdrop-filter: blur(12px);
      padding: 15px 40px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    nav .logo {
      font-size: 1.5rem;
      font-weight: 700;
    }
    
    nav a {
      color: #9bbcff;
      text-decoration: none;
      margin-right: 20px;
      transition: 0.3s;
      font-weight: 500;
    }
    
    nav a:hover {
      color: #58a6ff;
    }
    
    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 60px 20px;
      position: relative;
      z-index: 1;
    }
    
    .hero-upgrade {
      text-align: center;
      margin-bottom: 60px;
      animation: fadeInDown 0.8s ease;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .hero-upgrade h1 {
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }
    
    .hero-upgrade p {
      font-size: 1.3rem;
      color: #9ca3af;
    }
    
    .pricing-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
      margin-bottom: 60px;
    }
    
    .pricing-card {
      background: rgba(255,255,255,0.05);
      border: 2px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 40px 30px;
      position: relative;
      transition: all 0.4s;
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .pricing-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }
    
    .pricing-card.popular {
      border-color: #fbbf24;
      background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.05));
      box-shadow: 0 10px 40px rgba(251, 191, 36, 0.3);
    }
    
    .popular-badge {
      position: absolute;
      top: -15px;
      right: 30px;
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: #000;
      padding: 6px 20px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.85rem;
    }
    
    .plan-name {
      font-size: 1.8rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 10px;
    }
    
    .plan-price {
      font-size: 3rem;
      font-weight: 900;
      color: #fbbf24;
      margin: 20px 0;
    }
    
    .plan-price span {
      font-size: 1rem;
      color: #9ca3af;
      font-weight: 400;
    }
    
    .plan-trial {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
      border: 1px solid #22c55e;
      padding: 8px 16px;
      border-radius: 10px;
      font-size: 0.9rem;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 20px;
    }
    
    .plan-features {
      list-style: none;
      margin: 30px 0;
      padding: 0;
    }
    
    .plan-features li {
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      display: flex;
      align-items: center;
      gap: 12px;
      color: #d1d5db;
    }
    
    .plan-features li:last-child {
      border-bottom: none;
    }
    
    .plan-features li::before {
      content: '✓';
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      flex-shrink: 0;
    }
    
    .btn-select {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 10px;
    }
    
    .btn-select.free {
      background: rgba(255,255,255,0.1);
      color: #9ca3af;
    }
    
    .btn-select.premium {
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: #000;
      box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
    }
    
    .btn-select.premium:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, 0.6);
    }
    
    .btn-select:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .features-comparison {
      background: rgba(255,255,255,0.03);
      border-radius: 20px;
      padding: 40px;
      margin-top: 40px;
    }
    
    .features-comparison h3 {
      color: #58a6ff;
      text-align: center;
      margin-bottom: 30px;
      font-size: 2rem;
    }
    
    .comparison-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    
    .feature-item {
      background: rgba(255,255,255,0.05);
      padding: 20px;
      border-radius: 12px;
      border-left: 3px solid #fbbf24;
    }
    
    .feature-item h5 {
      color: #fbbf24;
      margin-bottom: 10px;
      font-weight: 600;
    }
    
    .feature-item p {
      color: #d1d5db;
      font-size: 0.9rem;
      margin: 0;
    }
    
    .current-plan-badge {
      background: rgba(88, 166, 255, 0.2);
      color: #58a6ff;
      border: 1px solid #58a6ff;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 15px;
    }
    
    @media (max-width: 768px) {
      .hero-upgrade h1 {
        font-size: 2rem;
      }
      
      .pricing-cards {
        grid-template-columns: 1fr;
      }
      
      nav {
        padding: 12px 20px;
      }
      
      nav a {
        margin-right: 12px;
        font-size: 0.85rem;
      }
    }
  </style>
</head>
<body>

  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
    <div class="particle" style="left: 50%; animation-delay: 8s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 90%; animation-delay: 16s;"></div>
  </div>

  <nav class="d-flex justify-content-between align-items-center flex-wrap">
    <div class="logo text-light">💪 OnlineEdző</div>
    <div class="nav-links">
      <a href="index.php">Főoldal</a>
      <a href="plans.php">Edzéstervek</a>
      <a href="workout_log.php">Edzésnapló</a>
      <a href="logout.php">Kijelentkezés</a>
    </div>
  </nav>

  <div class="container">
    <div class="hero-upgrade">
      <h1>⭐ Upgrade Prémiumra</h1>
      <p>Szabadítsd fel a teljes potenciált és érj el többet!</p>
    </div>

    <?php if ($is_premium): ?>
      <div style="text-align: center; padding: 40px; background: rgba(34, 197, 94, 0.1); border-radius: 20px; border: 2px solid #22c55e; margin-bottom: 40px;">
        <h2 style="color: #22c55e; margin-bottom: 15px;">🎉 Már Prémium tag vagy!</h2>
        <p style="color: #d1d5db; font-size: 1.1rem;">Élvezd a korlátlan hozzáférést minden funkcióhoz!</p>
        <p style="color: #9ca3af; margin-top: 10px;">
          Előfizetésed vége: <?= $current_plan['end_date'] ? htmlspecialchars($current_plan['end_date']) : 'Korlátlan' ?>
        </p>
      </div>
    <?php endif; ?>

    <div class="pricing-cards">
      
      <!-- Ingyenes csomag -->
      <div class="pricing-card">
        <?php if (!$is_premium): ?>
          <span class="current-plan-badge">Jelenlegi csomagod</span>
        <?php endif; ?>
        
        <div class="plan-name">🆓 Ingyenes</div>
        <div class="plan-price">
          0 Ft <span>/ hó</span>
        </div>
        
        <ul class="plan-features">
          <li>Összes edzésterv sablon</li>
          <li>30 napos edzésnapló</li>
          <li>Teljes edzéskereső</li>
          <li>Timer funkciók</li>
          <li>Utolsó 10 testmérés</li>
          <li>Max 5 progress fotó</li>
          <li>1 terv generálás/hó</li>
        </ul>
        
        <button class="btn-select free" disabled>Jelenlegi csomag</button>
      </div>

      <!-- Prémium Havi -->
      <div class="pricing-card popular">
        <span class="popular-badge">⭐ Legnépszerűbb</span>
        
        <div class="plan-name">💎 Prémium Havi</div>
        
        <div class="plan-trial">🎁 Első 7 nap INGYEN!</div>
        
        <div class="plan-price">
          2.990 Ft <span>/ hó</span>
        </div>
        
        <ul class="plan-features">
          <li>🤖 AI Személyi Edző</li>
          <li>📊 Fejlett statisztikák</li>
          <li>🍎 Táplálkozási napló</li>
          <li>🏆 Kihívások & célok</li>
          <li>📸 Korlátlan progress fotók</li>
          <li>📈 Teljes történet</li>
          <li>🎨 Prémium témák</li>
          <li>📧 Exkluzív tartalmak</li>
          <li>⚡ Prioritás támogatás</li>
        </ul>
        
        <button class="btn-select premium" onclick="selectPlan('monthly')" <?= $is_premium ? 'disabled' : '' ?>>
          <?= $is_premium ? '✓ Aktív előfizetés' : '🚀 Kipróbálom ingyen!' ?>
        </button>
      </div>

      <!-- Prémium Éves -->
      <div class="pricing-card">
        <div class="plan-name">🔥 Prémium Éves</div>
        
        <div class="plan-trial" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border-color: #ef4444;">
          💰 2 hónap ingyen!
        </div>
        
        <div class="plan-price">
          29.990 Ft <span>/ év</span>
        </div>
        
        <p style="color: #22c55e; text-align: center; margin: 10px 0; font-weight: 600;">
          Spórolj 5.890 Ft-ot!
        </p>
        
        <ul class="plan-features">
          <li>🤖 AI Személyi Edző</li>
          <li>📊 Fejlett statisztikák</li>
          <li>🍎 Táplálkozási napló</li>
          <li>🏆 Kihívások & célok</li>
          <li>📸 Korlátlan progress fotók</li>
          <li>📈 Teljes történet</li>
          <li>🎨 Prémium témák</li>
          <li>📧 Exkluzív tartalmak</li>
          <li>⚡ Prioritás támogatás</li>
          <li>🎁 <strong>+ Bónusz tartalmak</strong></li>
        </ul>
        
        <button class="btn-select premium" onclick="selectPlan('yearly')" <?= $is_premium ? 'disabled' : '' ?>>
          <?= $is_premium ? '✓ Aktív előfizetés' : '💎 Legjobb ajánlat!' ?>
        </button>
      </div>

    </div>

    <!-- Funkciók összehasonlítás -->
    <div class="features-comparison">
      <h3>🎯 Mit kapsz Prémiummal?</h3>
      
      <div class="comparison-grid">
        <div class="feature-item">
          <h5>🤖 AI Személyi Edző</h5>
          <p>Kérdezz bármit az AI-tól edzésről, táplálkozásról, technikáról. 24/7 elérhető személyi asszisztens.</p>
        </div>
        
        <div class="feature-item">
          <h5>📊 Fejlett Statisztikák</h5>
          <p>Részletes grafikonok, progressziós görbék, teljesítmény analízis minden gyakorlatra.</p>
        </div>
        
        <div class="feature-item">
          <h5>🍎 Táplálkozási Modul</h5>
          <p>Kalória számláló, makrók követése, étkezések naplózása, receptek adatbázis.</p>
        </div>
        
        <div class="feature-item">
          <h5>🏆 Gamifikáció</h5>
          <p>Kihívások, achievement-ek, toplista, szintek. Motiválj magad játékosan!</p>
        </div>
        
        <div class="feature-item">
          <h5>📈 Korlátlan Történet</h5>
          <p>Teljes edzésnapló és testmérés történet. Nézd vissza az évek óta tartó fejlődésed!</p>
        </div>
        
        <div class="feature-item">
          <h5>⚡ Prioritás Támogatás</h5>
          <p>Gyorsabb válaszidő, email támogatás, személyre szabott segítség.</p>
        </div>
      </div>
    </div>

    <div style="text-align: center; margin-top: 60px; color: #9ca3af;">
      <p>💳 Biztonságos fizetés • 🔒 Bármikor lemondható • ✅ 7 napos pénzvisszafizetési garancia</p>
    </div>
  </div>

  <script>
    function selectPlan(type) {
      // Később itt lesz a Stripe fizetés
      window.location.href = 'payment.php?plan=' + type;
      
      // TODO: Stripe checkout session létrehozása
      // window.location.href = 'payment.php?plan=' + type;
    }
  </script>

</body>
</html>