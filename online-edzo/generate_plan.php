<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Lekérjük a user profilját
$stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
  header('Location: onboarding.php');
  exit;
}

$goal = $profile['goal'];
$level = $profile['fitness_level'];
$sessions = (int)$profile['weekly_sessions'];
$restrictions = strtolower($profile['restrictions'] ?? '');

// Korlátozások elemzése
$has_back_issue = (strpos($restrictions, 'hát') !== false || strpos($restrictions, 'derék') !== false || strpos($restrictions, 'gerinc') !== false);
$has_knee_issue = (strpos($restrictions, 'térd') !== false);
$has_shoulder_issue = (strpos($restrictions, 'váll') !== false);

// Edzéstervek goal szerint
$plans = [
  'fogyás' => [
    'name' => 'Zsírégető Program',
    'description' => 'Kardió és HIIT alapú edzések zsírégetésre',
    'icon' => '🔥',
    'days' => [
      'Hétfő' => ['type' => 'HIIT & Core', 'focus' => 'Intenzív intervallum edzés + hasizom', 'duration' => '45 perc'],
      'Kedd' => ['type' => 'Teljes test', 'focus' => 'Funkcionális gyakorlatok testsúllyal', 'duration' => '40 perc'],
      'Szerda' => ['type' => 'Pihenő / Aktív pihenő', 'focus' => 'Séta, nyújtás vagy könnyű kardió', 'duration' => '30 perc'],
      'Csütörtök' => ['type' => 'Kardió + Has', 'focus' => 'Futás/kerékpár + core munka', 'duration' => '50 perc'],
      'Péntek' => ['type' => 'HIIT', 'focus' => 'Sprint intervallumok + burpee', 'duration' => '40 perc'],
      'Szombat' => ['type' => 'Aktív pihenő', 'focus' => 'Könnyű séta vagy úszás', 'duration' => '30 perc'],
    ]
  ],
  
  'izomnövelés' => [
    'name' => 'Tömegnövelő Program',
    'description' => 'Súlyzós alapgyakorlatok izomépítésre',
    'icon' => '💪',
    'days' => [
      'Hétfő' => ['type' => 'Mell & Tricepsz', 'focus' => 'Fekvenyomás, tárogatás, tricepsz munka', 'duration' => '60 perc'],
      'Kedd' => ['type' => $has_back_issue ? 'Hát (könnyített)' : 'Hát & Bicepsz', 'focus' => $has_back_issue ? 'Gépes evezés, lehúzás, bicepsz' : 'Deadlift, húzódzkodás, evezés, bicepsz', 'duration' => '60 perc'],
      'Szerda' => ['type' => 'Pihenő', 'focus' => 'Regeneráció és növekedés', 'duration' => '—'],
      'Csütörtök' => ['type' => $has_knee_issue ? 'Láb (könnyített)' : 'Láb', 'focus' => $has_knee_issue ? 'Lábtológép, lábhajlítás, vádli' : 'Guggolás, kitörés, lábhajlítás', 'duration' => '60 perc'],
      'Péntek' => ['type' => $has_shoulder_issue ? 'Váll & Has (könnyített)' : 'Váll & Has', 'focus' => $has_shoulder_issue ? 'Könnyű oldalemelés, core' : 'Vállnyomás, emelések, hasizom', 'duration' => '50 perc'],
      'Szombat' => ['type' => 'Opcionális', 'focus' => 'Gyenge pontok vagy pihenő', 'duration' => '30-45 perc'],
    ]
  ],
  
  'erősödés' => [
    'name' => 'Erőnövelő Program',
    'description' => 'Alacsony ismétlés, magas súly, nagy összetett gyakorlatok',
    'icon' => '🏋️',
    'days' => [
      'Hétfő' => ['type' => 'Erő A - Láb & Mell', 'focus' => $has_back_issue ? 'Lábtológép, fekvenyomás' : 'Guggolás, fekvenyomás', 'duration' => '70 perc'],
      'Szerda' => ['type' => 'Erő B - Hát & Váll', 'focus' => $has_back_issue ? 'Gépes hátgyakorlatok, váll' : 'Deadlift, evezés, vállnyomás', 'duration' => '70 perc'],
      'Péntek' => ['type' => 'Erő C - Mix', 'focus' => 'Kisegítő gyakorlatok + core', 'duration' => '60 perc'],
    ]
  ],
  
  'állóképesség' => [
    'name' => 'Kardió & Fitness Program',
    'description' => 'Állóképesség építés kardió fókusszal',
    'icon' => '🏃',
    'days' => [
      'Hétfő' => ['type' => 'Long Run', 'focus' => 'Folyamatos futás könnyű tempóban', 'duration' => '35-45 perc'],
      'Kedd' => ['type' => 'Körbedzés', 'focus' => 'Teljes test funkcionális gyakorlatok', 'duration' => '40 perc'],
      'Szerda' => ['type' => 'Pihenő / Séta', 'focus' => 'Aktív regeneráció', 'duration' => '30 perc'],
      'Csütörtök' => ['type' => 'Intervallum', 'focus' => 'Gyors-lassú futás váltakozva', 'duration' => '30 perc'],
      'Péntek' => ['type' => 'Cross Training', 'focus' => 'Kerékpár, úszás vagy más kardió', 'duration' => '45 perc'],
      'Szombat' => ['type' => 'Long Slow Distance', 'focus' => 'Hosszú, lassú állóképesség építés', 'duration' => '60 perc'],
    ]
  ]
];

$selected_plan = $plans[$goal] ?? $plans['izomnövelés'];

// Ha kevesebb edzést akar, csak annyi napot mutat
$plan_days = array_slice($selected_plan['days'], 0, $sessions, true);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Személyre szabott Edzésterv</title>
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
      background: rgba(88, 166, 255, 0.2);
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
      max-width: 1200px;
      margin: 0 auto;
      padding: 50px 20px;
      position: relative;
      z-index: 1;
    }
    
    .plan-header {
      text-align: center;
      margin-bottom: 50px;
      animation: fadeInDown 0.8s ease;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .plan-header h1 {
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }
    
    .plan-header p {
      font-size: 1.2rem;
      color: #9ca3af;
    }
    
    .user-info {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid #58a6ff;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 40px;
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .user-info-item {
      text-align: center;
    }
    
    .user-info-item .label {
      color: #9bbcff;
      font-size: 0.9rem;
      margin-bottom: 5px;
    }
    
    .user-info-item .value {
      color: #fff;
      font-size: 1.3rem;
      font-weight: 700;
    }
    
    .warning-box {
      background: rgba(251, 191, 36, 0.1);
      border: 1px solid #fbbf24;
      border-radius: 12px;
      padding: 15px 20px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .warning-box .icon {
      font-size: 2rem;
    }
    
    .day-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 25px;
      transition: all 0.3s;
      animation: fadeInUp 0.6s ease;
      position: relative;
      overflow: hidden;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .day-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: linear-gradient(135deg, #58a6ff, #238636);
    }
    
    .day-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
      border-color: #238636;
    }
    
    .day-card.rest {
      background: rgba(251, 191, 36, 0.05);
      border-color: rgba(251, 191, 36, 0.2);
    }
    
    .day-card.rest::before {
      background: #fbbf24;
    }
    
    .day-name {
      font-size: 1.8rem;
      font-weight: 700;
      color: #58a6ff;
      margin-bottom: 10px;
    }
    
    .day-type {
      font-size: 1.3rem;
      color: #fff;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .day-focus {
      color: #d1d5db;
      margin-bottom: 10px;
      line-height: 1.6;
    }
    
    .day-duration {
      display: inline-block;
      background: rgba(35, 134, 54, 0.2);
      color: #22c55e;
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      margin-top: 10px;
    }
    
    .btn-main {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 30px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 4px 15px rgba(35, 134, 54, 0.3);
    }
    
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(35, 134, 54, 0.5);
      color: white;
    }
    
    .actions {
      text-align: center;
      margin-top: 50px;
    }
    
    @media (max-width: 768px) {
      .plan-header h1 {
        font-size: 2rem;
      }
      
      .day-card {
        padding: 20px;
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
      <a href="body_tracker.php">Testméretek</a>
      <a href="logout.php">Kijelentkezés</a>
    </div>
  </nav>

  <div class="container">
    <div class="plan-header">
      <h1><?= $selected_plan['icon'] ?> <?= htmlspecialchars($selected_plan['name']) ?></h1>
      <p><?= htmlspecialchars($selected_plan['description']) ?></p>
    </div>

    <div class="user-info">
      <div class="user-info-item">
        <div class="label">Cél</div>
        <div class="value"><?= ucfirst($goal) ?></div>
      </div>
      <div class="user-info-item">
        <div class="label">Szint</div>
        <div class="value"><?= ucfirst($level) ?></div>
      </div>
      <div class="user-info-item">
        <div class="label">Heti edzések</div>
        <div class="value"><?= $sessions ?> nap</div>
      </div>
    </div>

    <?php if ($has_back_issue || $has_knee_issue || $has_shoulder_issue): ?>
    <div class="warning-box">
      <div class="icon">⚕️</div>
      <div>
        <strong>A terv figyelembe veszi a korlátozásaidat:</strong><br>
        <?php if ($has_back_issue) echo '• Hát/derék kímélése - könnyített gyakorlatokkal<br>'; ?>
        <?php if ($has_knee_issue) echo '• Térd kímélése - gépi gyakorlatok előnyben<br>'; ?>
        <?php if ($has_shoulder_issue) echo '• Váll kímélése - könnyebb súlyokkal'; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php foreach ($plan_days as $day => $details): ?>
      <div class="day-card <?= strpos($details['type'], 'Pihenő') !== false ? 'rest' : '' ?>">
        <div class="day-name">📅 <?= htmlspecialchars($day) ?></div>
        <div class="day-type"><?= htmlspecialchars($details['type']) ?></div>
        <div class="day-focus">
          <strong>Fókusz:</strong> <?= htmlspecialchars($details['focus']) ?>
        </div>
        <span class="day-duration">⏱️ <?= htmlspecialchars($details['duration']) ?></span>
      </div>
    <?php endforeach; ?>

    <div class="actions">
      <a href="plans.php" class="btn-main">✅ Terv mentése és kezdés!</a>
      <p style="color: #9ca3af; margin-top: 20px;">
        A pontos gyakorlatokat az <a href="exercise_browser.php" style="color: #58a6ff;">Edzéskeresőben</a> találod!
      </p>
    </div>
  </div>

</body>
</html>