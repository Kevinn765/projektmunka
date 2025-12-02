<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edzéstervek</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #0a0e27, #0d1117, #1a1f3a);
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
      background: rgba(88, 166, 255, 0.3);
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
    }
    
    nav a:hover {
      color: #58a6ff;
      background: rgba(88, 166, 255, 0.1);
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 60px 20px;
      position: relative;
      z-index: 1;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 50px;
      font-size: 3.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInDown 0.8s ease;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .text-center {
      text-align: center;
    }
    
    .btn-main {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border: none;
      border-radius: 15px;
      padding: 15px 40px;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.3s;
      display: inline-block;
      text-decoration: none;
      box-shadow: 0 8px 25px rgba(35, 134, 54, 0.4);
      margin-bottom: 50px;
    }
    
    .btn-main:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(35, 134, 54, 0.6);
      color: white;
    }
    
    .plan-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 40px;
      color: #fff;
      margin-bottom: 40px;
      transition: all 0.4s ease;
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .plan-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(88, 166, 255, 0.05), rgba(35, 134, 54, 0.05));
      opacity: 0;
      transition: opacity 0.4s;
    }
    
    .plan-card:hover::before {
      opacity: 1;
    }
    
    .plan-card:hover {
      transform: translateY(-10px);
      border-color: #238636;
      box-shadow: 0 15px 50px rgba(35, 134, 54, 0.4);
    }
    
    .plan-card h4 {
      font-size: 2rem;
      font-weight: 900;
      color: #58a6ff;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }
    
    .plan-card > p {
      color: #9ca3af;
      margin-bottom: 30px;
      font-size: 1.1rem;
      position: relative;
      z-index: 1;
    }
    
    .plan-card > p strong {
      color: #58a6ff;
    }
    
    .day {
      background: rgba(0,0,0,0.4);
      border-radius: 18px;
      padding: 30px;
      margin-bottom: 25px;
      border-left: 5px solid #238636;
      transition: all 0.3s;
      position: relative;
      z-index: 1;
    }
    
    .day:hover {
      background: rgba(0,0,0,0.5);
      border-left-color: #58a6ff;
      transform: translateX(8px);
    }
    
    .day h6 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #58a6ff;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .day h6::before {
      content: '📅';
      font-size: 1.6rem;
    }
    
    .day ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .day li {
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      color: #d1d5db;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: all 0.2s;
      font-size: 1.05rem;
    }
    
    .day li:last-child {
      border-bottom: none;
    }
    
    .day li:hover {
      color: #58a6ff;
      padding-left: 12px;
    }
    
    .day li::before {
      content: '💪';
      font-size: 1.3rem;
    }
    
    .day em {
      color: #fbbf24;
      font-style: normal;
      font-weight: 600;
    }
    
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      nav a {
        padding: 6px 12px;
        font-size: 0.9rem;
      }
      
      h2 {
        font-size: 2.5rem;
      }
      
      .plan-card {
        padding: 30px 25px;
      }
      
      .day {
        padding: 25px 20px;
      }
    }
    
    @media (max-width: 480px) {
      h2 {
        font-size: 2rem;
      }
      
      .plan-card h4 {
        font-size: 1.6rem;
      }
      
      .day h6 {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

  <div class="particles">
    <div class="particle" style="left: 15%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 35%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 55%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 75%; animation-delay: 9s;"></div>
    <div class="particle" style="left: 25%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 65%; animation-delay: 15s;"></div>
  </div>

  <nav class="d-flex justify-content-between align-items-center">
    <a href="index.php" style="text-decoration: none;">
      <div class="logo">💪 OnlineEdző</div>
    </a>
    <div>
<a href="index.php">🏠 Főoldal</a>
      <a href="logout.php">Kijelentkezés</a>
    </div>
  </nav>

  <div class="container">
    <h2>💪 Edzéstervek</h2>
    
    <div class="text-center">
      <a href="generate_plan.php" class="btn-main">➕ Új terv generálása</a>
    </div>

    <?php
    $plans = [
      [
        'name' => 'Izomnövelő edzésterv (Középhaladó)',
        'goal' => 'Tömegnövelés',
        'days' => [
          ['day' => 'Hétfő — Mell & Tricepsz', 'exercises' => ['Fekvenyomás — 4x8', 'Tárogatás — 3x12', 'Tolódzkodás — 3x10', 'Tricepsznyújtás kötéllel — 3x12']],
          ['day' => 'Kedd — Hát & Bicepsz', 'exercises' => ['Húzódzkodás — 4x8', 'Evezés — 4x10', 'Bicepsz hajlítás — 3x12']],
          ['day' => 'Szerda — Pihenő'],
          ['day' => 'Csütörtök — Láb', 'exercises' => ['Guggolás — 4x8', 'Lábtológép — 3x12', 'Lábhajlítás — 3x15', 'Vádli emelés — 4x20']],
          ['day' => 'Péntek — Váll', 'exercises' => ['Oldalemelés — 4x12', 'Vállból nyomás — 3x10', 'Előreemelés — 3x15']]
        ]
      ],
      [
        'name' => 'Zsírégető program (Haladó)',
        'goal' => 'Fogyás, állóképesség fejlesztés',
        'days' => [
          ['day' => 'Hétfő — HIIT & Core', 'exercises' => ['Burpee — 4x15', 'Plank — 3x1 perc', 'Futás — 20 perc']],
          ['day' => 'Kedd — Teljes test', 'exercises' => ['Kettlebell swing — 4x15', 'Guggolás — 4x12', 'Fekvőtámasz — 3x15']],
          ['day' => 'Szerda — Pihenő / könnyű kardió'],
          ['day' => 'Csütörtök — Láb + has', 'exercises' => ['Kitörés — 4x12', 'Lábemelés — 3x20', 'Futás — 25 perc']]
        ]
      ],
      [
        'name' => 'Kezdő erősítő program',
        'goal' => 'Alap erő és állóképesség fejlesztés',
        'days' => [
          ['day' => 'Hétfő — Felső test', 'exercises' => ['Fekvőtámasz — 3x10', 'Húzódzkodás gumiszalaggal — 3x6', 'Plank — 3x30mp']],
          ['day' => 'Kedd — Láb', 'exercises' => ['Guggolás — 3x12', 'Vádli emelés — 3x20', 'Kitörés — 3x10']],
          ['day' => 'Szerda — Pihenő'],
          ['day' => 'Csütörtök — Kardió + Core', 'exercises' => ['Kocogás — 20 perc', 'Hasprés — 3x20', 'Plank — 3x45mp']]
        ]
      ]
    ];

    foreach ($plans as $p): ?>
      <div class="plan-card">
        <h4><?= htmlspecialchars($p['name']) ?></h4>
        <p><strong>🎯 Cél:</strong> <?= htmlspecialchars($p['goal']) ?></p>
        <?php foreach ($p['days'] as $day): ?>
          <div class="day">
            <h6><?= htmlspecialchars($day['day']) ?></h6>
            <?php if (isset($day['exercises'])): ?>
              <ul>
                <?php foreach ($day['exercises'] as $ex): ?>
                  <li><?= htmlspecialchars($ex) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <em>Pihenőnap — regeneráció és felépülés</em>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>