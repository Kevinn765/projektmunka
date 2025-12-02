<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

// PRÉMIUM ELLENŐRZÉS
if (!isPremium($_SESSION['user_id'])) {
  header('Location: upgrade.php');
  exit;
}

$uid = $_SESSION['user_id'];
$message = '';

// Napi cél lekérése
$stmt = $pdo->prepare("SELECT daily_calorie_goal FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();
$daily_goal = $user['daily_calorie_goal'] ?? 2000;

// Új étel hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_food'])) {
  $food_name = $_POST['food_name'] ?? '';
  $calories = $_POST['calories'] ?? 0;
  $protein = $_POST['protein'] ?? 0;
  
  $stmt = $pdo->prepare("INSERT INTO nutrition_log (user_id, food_name, calories, protein, created_at) VALUES (?,?,?,?, NOW())");
  $stmt->execute([$uid, $food_name, $calories, $protein]);
  $message = '✅ Étel hozzáadva!';
}

// Törlés
if (isset($_GET['delete'])) {
  $stmt = $pdo->prepare("DELETE FROM nutrition_log WHERE id = ? AND user_id = ?");
  $stmt->execute([$_GET['delete'], $uid]);
  header('Location: nutrition_log.php');
  exit;
}

// Mai ételek
$stmt = $pdo->prepare("SELECT * FROM nutrition_log WHERE user_id = ? AND DATE(created_at) = CURDATE() ORDER BY id DESC");
$stmt->execute([$uid]);
$foods = $stmt->fetchAll();

// Mai összesítés
$stmt = $pdo->prepare("SELECT SUM(calories) as total_cal, SUM(protein) as total_protein FROM nutrition_log WHERE user_id = ? AND DATE(created_at) = CURDATE()");
$stmt->execute([$uid]);
$totals = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Táplálkozási Napló</title>
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
      max-width: 1100px;
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
    
    .card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 40px;
      margin-bottom: 40px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      transition: all 0.3s;
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 50px rgba(0,0,0,0.4);
    }
    
    .card h3 {
      color: #58a6ff;
      margin-bottom: 30px;
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .form-label {
      color: #9bbcff;
      font-weight: 600;
      margin-bottom: 10px;
      font-size: 1rem;
    }
    
    .form-control, .form-select {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      background: rgba(255,255,255,0.12);
      border-color: #58a6ff;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.15);
      outline: none;
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .btn-main {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 35px;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
    }
    
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(35, 134, 54, 0.6);
      color: white;
    }
    
    .btn-danger {
      background: linear-gradient(135deg, #dc2626, #ef4444);
      border: none;
      padding: 8px 18px;
      border-radius: 10px;
      color: white;
      font-size: 0.9rem;
      transition: 0.3s;
      text-decoration: none;
      display: inline-block;
      font-weight: 600;
    }
    
    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(220, 38, 38, 0.5);
      color: white;
    }
    
    .alert-success {
      background: rgba(34, 197, 94, 0.2);
      border: 2px solid #22c55e;
      color: #7fffd4;
      border-radius: 15px;
      padding: 18px;
      text-align: center;
      animation: fadeIn 0.5s ease;
      margin-bottom: 30px;
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .stat-card {
      background: rgba(10, 14, 39, 0.6);
      border: 2px solid rgba(88, 166, 255, 0.15);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
    }
    
    .stat-card:hover {
      transform: translateY(-8px);
      border-color: #58a6ff;
      box-shadow: 0 12px 35px rgba(88, 166, 255, 0.4);
    }
    
    .stat-icon {
      font-size: 3rem;
      margin-bottom: 15px;
    }
    
    .stat-value {
      font-size: 2.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 10px 0;
    }
    
    .stat-label {
      color: #9bbcff;
      font-size: 1rem;
      font-weight: 600;
    }
    
    .stat-subtext {
      color: #9ca3af;
      font-size: 0.9rem;
      margin-top: 10px;
    }
    
    .progress-bar-container {
      background: rgba(255,255,255,0.08);
      border-radius: 12px;
      height: 12px;
      margin-top: 15px;
      overflow: hidden;
    }
    
    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #238636, #2ea043);
      border-radius: 12px;
      transition: width 0.5s ease;
    }
    
    .progress-bar-fill.over {
      background: linear-gradient(90deg, #dc2626, #ef4444);
    }
    
    .food-item {
      background: rgba(0,0,0,0.4);
      border: 1px solid rgba(88, 166, 255, 0.1);
      border-radius: 15px;
      padding: 20px 25px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.3s;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .food-item:hover {
      background: rgba(0,0,0,0.5);
      border-color: #58a6ff;
      transform: translateX(8px);
    }
    
    .food-info {
      flex: 1;
      min-width: 200px;
    }
    
    .food-name {
      color: #fff;
      font-weight: 700;
      font-size: 1.15rem;
      margin-bottom: 8px;
    }
    
    .food-macros {
      color: #9ca3af;
      font-size: 0.95rem;
      display: flex;
      gap: 18px;
      flex-wrap: wrap;
    }
    
    .food-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    .no-data {
      text-align: center;
      padding: 60px 20px;
      color: #9ca3af;
    }
    
    .no-data h4 {
      font-size: 1.8rem;
      margin-bottom: 15px;
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
      
      .stats-row {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .card {
        padding: 30px 25px;
      }
      
      .food-item {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .food-actions {
        width: 100%;
        justify-content: flex-end;
      }
    }
    
    @media (max-width: 480px) {
      h2 {
        font-size: 2rem;
      }
      
      .stats-row {
        grid-template-columns: 1fr;
      }
      
      .stat-value {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

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
    <a href="index.php" style="text-decoration: none;">
      <div class="logo">💪 OnlineEdző</div>
    </a>
    <div>
      <a href="index.php">🏠 Főoldal</a>
      <a href="logout.php">Kijelentkezés</a>
    </div>
  </nav>

  <div class="container">
    <h2>🍎 Táplálkozási Napló</h2>

    <?php if ($message): ?>
      <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Napi statisztikák -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= round($totals['total_cal'] ?? 0) ?></div>
        <div class="stat-label">Kalória</div>
        <div class="stat-subtext">Cél: <?= $daily_goal ?> kcal</div>
        <div class="progress-bar-container">
          <?php 
            $cal_percent = ($totals['total_cal'] ?? 0) / $daily_goal * 100;
            $over_class = $cal_percent > 100 ? 'over' : '';
          ?>
          <div class="progress-bar-fill <?= $over_class ?>" style="width: <?= min($cal_percent, 100) ?>%"></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🥩</div>
        <div class="stat-value"><?= round($totals['total_protein'] ?? 0) ?>g</div>
        <div class="stat-label">Fehérje</div>
        <div class="stat-subtext">Mai bevitel</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-value"><?= count($foods) ?></div>
        <div class="stat-label">Étkezések</div>
        <div class="stat-subtext">Ma felvéve</div>
      </div>
    </div>

    <!-- Új étel hozzáadása -->
    <div class="card">
      <h3>➕ Új étel hozzáadása</h3>
      <form method="post">
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">🍴 Étel neve</label>
            <input type="text" name="food_name" class="form-control" placeholder="pl. Csirkemell rizzsel" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">🔥 Kalória</label>
            <input type="number" step="1" name="calories" class="form-control" placeholder="0" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">🥩 Fehérje (g)</label>
            <input type="number" step="0.1" name="protein" class="form-control" placeholder="0">
          </div>
          <div class="col-md-2">
            <button type="submit" name="add_food" class="btn btn-main w-100" style="margin-top: 32px;">Hozzáad</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Mai ételek -->
    <?php if (!empty($foods)): ?>
      <div class="card">
        <h3>📋 Mai étkezések</h3>
        
        <?php foreach ($foods as $food): ?>
          <div class="food-item">
            <div class="food-info">
              <div class="food-name"><?= htmlspecialchars($food['food_name']) ?></div>
              <div class="food-macros">
                <span>🔥 <?= round($food['calories']) ?> kcal</span>
                <?php if ($food['protein'] > 0): ?>
                  <span>🥩 <?= round($food['protein']) ?>g fehérje</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="food-actions">
              <a href="nutrition_log.php?delete=<?= $food['id'] ?>" class="btn-danger" onclick="return confirm('Biztosan törlöd?')">🗑️ Törlés</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="no-data">
          <h4>🤷‍♂️ Még nincs rögzített étkezés ma</h4>
          <p>Add hozzá az első ételt a fenti űrlappal!</p>
        </div>
      </div>
    <?php endif; ?>

  </div>

</body>
</html>