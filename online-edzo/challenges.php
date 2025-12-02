<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Új cél hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_goal'])) {
  $goal_title = trim($_POST['goal_title'] ?? '');
  $goal_type = $_POST['goal_type'] ?? '';
  $target_value = $_POST['target_value'] ?? 0;
  $target_date = $_POST['target_date'] ?? null;
  $description = trim($_POST['description'] ?? '');
  
  if ($goal_title && $goal_type) {
    $stmt = $pdo->prepare("INSERT INTO user_goals (user_id, title, goal_type, target_value, current_value, target_date, description, status) VALUES (?, ?, ?, ?, 0, ?, ?, 'active')");
    $stmt->execute([$user_id, $goal_title, $goal_type, $target_value, $target_date, $description]);
  }
}

// Cél frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_goal'])) {
  $goal_id = $_POST['goal_id'] ?? 0;
  $current_value = $_POST['current_value'] ?? 0;
  
  $stmt = $pdo->prepare("UPDATE user_goals SET current_value = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$current_value, $goal_id, $user_id]);
  
  // Ellenőrizzük, hogy elérte-e a célt
  $stmt = $pdo->prepare("SELECT target_value FROM user_goals WHERE id = ? AND user_id = ?");
  $stmt->execute([$goal_id, $user_id]);
  $goal = $stmt->fetch();
  
  if ($goal && $current_value >= $goal['target_value']) {
    $stmt = $pdo->prepare("UPDATE user_goals SET status = 'completed', completed_date = CURDATE() WHERE id = ? AND user_id = ?");
    $stmt->execute([$goal_id, $user_id]);
  }
}

// Cél törlése
if (isset($_GET['delete_goal'])) {
  $goal_id = $_GET['delete_goal'];
  $stmt = $pdo->prepare("DELETE FROM user_goals WHERE id = ? AND user_id = ?");
  $stmt->execute([$goal_id, $user_id]);
  header('Location: challenges.php');
  exit;
}

// Kihíváshoz csatlakozás
if (isset($_GET['join_challenge'])) {
  $challenge_id = $_GET['join_challenge'];
  $stmt = $pdo->prepare("INSERT INTO user_challenges (user_id, challenge_id, joined_date, progress, status) VALUES (?, ?, CURDATE(), 0, 'active') ON DUPLICATE KEY UPDATE status = 'active'");
  $stmt->execute([$user_id, $challenge_id]);
  header('Location: challenges.php');
  exit;
}

// Kihívás haladás frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_challenge'])) {
  $challenge_id = $_POST['challenge_id'] ?? 0;
  $progress = $_POST['progress'] ?? 0;
  
  $stmt = $pdo->prepare("UPDATE user_challenges SET progress = ? WHERE user_id = ? AND challenge_id = ?");
  $stmt->execute([$progress, $user_id, $challenge_id]);
  
  // Ellenőrizzük, hogy teljesítette-e
  $stmt = $pdo->prepare("SELECT c.target_value FROM challenges c JOIN user_challenges uc ON c.id = uc.challenge_id WHERE uc.user_id = ? AND uc.challenge_id = ?");
  $stmt->execute([$user_id, $challenge_id]);
  $challenge = $stmt->fetch();
  
  if ($challenge && $progress >= $challenge['target_value']) {
    $stmt = $pdo->prepare("UPDATE user_challenges SET status = 'completed', completed_date = CURDATE() WHERE user_id = ? AND challenge_id = ?");
    $stmt->execute([$user_id, $challenge_id]);
  }
}

// Felhasználó céljainak lekérése
$stmt = $pdo->prepare("SELECT * FROM user_goals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_goals = $stmt->fetchAll();

// Elérhető kihívások lekérése
$stmt = $pdo->query("SELECT * FROM challenges WHERE status = 'active' ORDER BY difficulty, id");
$available_challenges = $stmt->fetchAll();

// Felhasználó kihívásai
$stmt = $pdo->prepare("
  SELECT c.*, uc.progress, uc.joined_date, uc.completed_date, uc.status as user_status 
  FROM challenges c 
  JOIN user_challenges uc ON c.id = uc.challenge_id 
  WHERE uc.user_id = ? 
  ORDER BY uc.status = 'completed', c.id DESC
");
$stmt->execute([$user_id]);
$user_challenges = $stmt->fetchAll();

// Statisztikák
$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM user_goals WHERE user_id = ?");
$stmt->execute([$user_id]);
$goal_stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM user_challenges WHERE user_id = ?");
$stmt->execute([$user_id]);
$challenge_stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kihívások és Célok - OnlineEdző</title>
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
      max-width: 1400px;
      margin: 0 auto;
      padding: 60px 20px;
      position: relative;
      z-index: 1;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 3.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInDown 0.8s ease;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 50px;
      font-size: 1.2rem;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
    }
    
    .stat-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(88, 166, 255, 0.3);
    }
    
    .stat-icon {
      font-size: 3rem;
      margin-bottom: 15px;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
    }
    
    .stat-label {
      color: #9ca3af;
      font-size: 1rem;
    }
    
    .tabs {
      display: flex;
      gap: 15px;
      margin-bottom: 40px;
      flex-wrap: wrap;
      justify-content: center;
    }
    
    .tab-btn {
      background: rgba(10, 14, 39, 0.6);
      border: 2px solid rgba(88, 166, 255, 0.15);
      color: #9bbcff;
      padding: 15px 35px;
      border-radius: 15px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .tab-btn:hover {
      border-color: #58a6ff;
      color: #58a6ff;
      transform: translateY(-2px);
    }
    
    .tab-btn.active {
      background: linear-gradient(135deg, #238636, #2ea043);
      border-color: #238636;
      color: white;
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 40px;
      margin-bottom: 30px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      transition: all 0.3s;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0,0,0,0.4);
    }
    
    .card h3 {
      color: #58a6ff;
      margin-bottom: 25px;
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .goal-item, .challenge-item {
      background: rgba(0,0,0,0.3);
      border-radius: 18px;
      padding: 30px;
      margin-bottom: 20px;
      border-left: 5px solid #238636;
      transition: all 0.3s;
      position: relative;
    }
    
    .goal-item:hover, .challenge-item:hover {
      background: rgba(0,0,0,0.4);
      transform: translateX(8px);
    }
    
    .goal-item.completed, .challenge-item.completed {
      border-left-color: #fbbf24;
      opacity: 0.8;
    }
    
    .goal-header, .challenge-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .goal-title, .challenge-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #fff;
      flex: 1;
    }
    
    .goal-type, .difficulty-badge {
      background: rgba(88, 166, 255, 0.2);
      color: #58a6ff;
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    .difficulty-easy { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
    .difficulty-medium { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }
    .difficulty-hard { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    
    .progress-bar-container {
      background: rgba(0,0,0,0.4);
      height: 30px;
      border-radius: 15px;
      overflow: hidden;
      margin: 20px 0;
      position: relative;
    }
    
    .progress-bar {
      background: linear-gradient(90deg, #238636, #2ea043);
      height: 100%;
      border-radius: 15px;
      transition: width 0.5s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 0.9rem;
    }
    
    .progress-bar.completed {
      background: linear-gradient(90deg, #fbbf24, #f59e0b);
    }
    
    .goal-info, .challenge-info {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      color: #d1d5db;
      margin-top: 15px;
    }
    
    .goal-info-item, .challenge-info-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1rem;
    }
    
    .goal-actions, .challenge-actions {
      margin-top: 20px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    .btn-update, .btn-delete, .btn-join {
      padding: 10px 25px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-update {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
    }
    
    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
      color: white;
    }
    
    .btn-delete {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
      border: 1px solid #ef4444;
    }
    
    .btn-delete:hover {
      background: rgba(239, 68, 68, 0.3);
      transform: translateY(-2px);
    }
    
    .btn-join {
      background: linear-gradient(135deg, #58a6ff, #3b82f6);
      color: white;
    }
    
    .btn-join:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(88, 166, 255, 0.4);
      color: white;
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
      width: 100%;
    }
    
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(35, 134, 54, 0.6);
      color: white;
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
    
    .completed-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: #000;
      padding: 8px 20px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 0.9rem;
      box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
    }
    
    .reward-points {
      background: rgba(251, 191, 36, 0.1);
      border: 1px solid #fbbf24;
      color: #fbbf24;
      padding: 10px 20px;
      border-radius: 15px;
      font-weight: 700;
      display: inline-block;
      margin-top: 10px;
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
      
      .card {
        padding: 30px 20px;
      }
      
      .goal-item, .challenge-item {
        padding: 25px 20px;
      }
      
      .tabs {
        gap: 10px;
      }
      
      .tab-btn {
        padding: 12px 25px;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>

  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 25%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 55%; animation-delay: 9s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 85%; animation-delay: 15s;"></div>
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
    <h2>🏆 Kihívások és Célok</h2>
    <p class="subtitle">Tűzz ki célokat és teljesíts kihívásokat a motiváció fenntartásához!</p>

    <!-- Statisztikák -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-number"><?= $goal_stats['total'] ?? 0 ?></div>
        <div class="stat-label">Összes célod</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?= $goal_stats['completed'] ?? 0 ?></div>
        <div class="stat-label">Teljesített cél</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-number"><?= $challenge_stats['total'] ?? 0 ?></div>
        <div class="stat-label">Aktív kihívás</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏅</div>
        <div class="stat-number"><?= $challenge_stats['completed'] ?? 0 ?></div>
        <div class="stat-label">Teljesített kihívás</div>
      </div>
    </div>

    <!-- Tabok -->
    <div class="tabs">
      <button class="tab-btn active" onclick="switchTab('my-goals')">🎯 Saját Céljaim</button>
      <button class="tab-btn" onclick="switchTab('my-challenges')">🔥 Kihívásaim</button>
      <button class="tab-btn" onclick="switchTab('available')">🆕 Elérhető Kihívások</button>
      <button class="tab-btn" onclick="switchTab('new-goal')">➕ Új Cél</button>
    </div>

    <!-- Saját Célok -->
    <div id="my-goals" class="tab-content active">
      <div class="card">
        <h3>🎯 Személyes Céljaid</h3>
        
        <?php if (empty($user_goals)): ?>
          <div class="no-data">
            <h4>🤷‍♂️ Még nincs kitűzött célod.</h4>
            <p>Tűzz ki egy célt az "Új Cél" fülön!</p>
          </div>
        <?php else: ?>
          <?php foreach ($user_goals as $goal): 
            $progress_percent = $goal['target_value'] > 0 ? min(100, ($goal['current_value'] / $goal['target_value']) * 100) : 0;
            $is_completed = $goal['status'] === 'completed';
          ?>
            <div class="goal-item <?= $is_completed ? 'completed' : '' ?>">
              <?php if ($is_completed): ?>
                <div class="completed-badge">✅ Teljesítve!</div>
              <?php endif; ?>
              
              <div class="goal-header">
                <div class="goal-title"><?= htmlspecialchars($goal['title']) ?></div>
                <div class="goal-type"><?= htmlspecialchars($goal['goal_type']) ?></div>
              </div>
              
              <?php if ($goal['description']): ?>
                <p style="color: #d1d5db; margin-bottom: 15px;"><?= htmlspecialchars($goal['description']) ?></p>
              <?php endif; ?>
              
              <div class="progress-bar-container">
                <div class="progress-bar <?= $is_completed ? 'completed' : '' ?>" style="width: <?= $progress_percent ?>%">
                  <?= round($progress_percent) ?>%
                </div>
              </div>
              
              <div class="goal-info">
                <div class="goal-info-item">
                  <span>📊</span>
                  <span><?= $goal['current_value'] ?> / <?= $goal['target_value'] ?></span>
                </div>
                <?php if ($goal['target_date']): ?>
                  <div class="goal-info-item">
                    <span>📅</span>
                    <span>Határidő: <?= date('Y.m.d', strtotime($goal['target_date'])) ?></span>
                  </div>
                <?php endif; ?>
                <?php if ($is_completed && $goal['completed_date']): ?>
                  <div class="goal-info-item">
                    <span>🎉</span>
                    <span>Teljesítve: <?= date('Y.m.d', strtotime($goal['completed_date'])) ?></span>
                  </div>
                <?php endif; ?>
              </div>
              
              <?php if (!$is_completed): ?>
                <div class="goal-actions">
                  <button class="btn-update" onclick="updateGoal(<?= $goal['id'] ?>, <?= $goal['current_value'] ?>, <?= $goal['target_value'] ?>)">
                    📈 Haladás Frissítése
                  </button>
                  <a href="?delete_goal=<?= $goal['id'] ?>" class="btn-delete" onclick="return confirm('Biztosan törlöd ezt a célt?')">
                    🗑️ Törlés
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Saját Kihívások -->
    <div id="my-challenges" class="tab-content">
      <div class="card">
        <h3>🔥 Aktív Kihívásaid</h3>
        
        <?php if (empty($user_challenges)): ?>
          <div class="no-data">
            <h4>🤷‍♂️ Még nincs aktív kihívásod.</h4>
            <p>Csatlakozz egy kihíváshoz az "Elérhető Kihívások" fülön!</p>
          </div>
        <?php else: ?>
          <?php foreach ($user_challenges as $challenge): 
            $progress_percent = $challenge['target_value'] > 0 ? min(100, ($challenge['progress'] / $challenge['target_value']) * 100) : 0;
            $is_completed = $challenge['user_status'] === 'completed';
            
            $difficulty_class = 'difficulty-medium';
            if ($challenge['difficulty'] === 'könnyű') $difficulty_class = 'difficulty-easy';
            if ($challenge['difficulty'] === 'nehéz') $difficulty_class = 'difficulty-hard';
          ?>
            <div class="challenge-item <?= $is_completed ? 'completed' : '' ?>">
              <?php if ($is_completed): ?>
                <div class="completed-badge">🏆 Teljesítve!</div>
              <?php endif; ?>
              
              <div class="challenge-header">
                <div class="challenge-title"><?= htmlspecialchars($challenge['title']) ?></div>
                <div class="difficulty-badge <?= $difficulty_class ?>">
                  <?= htmlspecialchars($challenge['difficulty']) ?>
                </div>
              </div>
              
              <p style="color: #d1d5db; margin-bottom: 15px;"><?= htmlspecialchars($challenge['description']) ?></p>
              
              <div class="progress-bar-container">
                <div class="progress-bar <?= $is_completed ? 'completed' : '' ?>" style="width: <?= $progress_percent ?>%">
                  <?= round($progress_percent) ?>%
                </div>
              </div>
              
              <div class="challenge-info">
                <div class="challenge-info-item">
                  <span>📊</span>
                  <span><?= $challenge['progress'] ?> / <?= $challenge['target_value'] ?> <?= htmlspecialchars($challenge['unit']) ?></span>
                </div>
                <div class="challenge-info-item">
                  <span>⏱️</span>
                  <span><?= $challenge['duration_days'] ?> nap</span>
                </div>
                <div class="challenge-info-item">
                  <span>📅</span>
                  <span>Csatlakozva: <?= date('Y.m.d', strtotime($challenge['joined_date'])) ?></span>
                </div>
                <?php if ($is_completed && $challenge['completed_date']): ?>
                  <div class="challenge-info-item">
                    <span>🎉</span>
                    <span>Teljesítve: <?= date('Y.m.d', strtotime($challenge['completed_date'])) ?></span>
                  </div>
                <?php endif; ?>
              </div>
              
              <?php if ($challenge['reward_points'] > 0): ?>
                <div class="reward-points">
                  ⭐ <?= $challenge['reward_points'] ?> pont
                </div>
              <?php endif; ?>
              
              <?php if (!$is_completed): ?>
                <div class="challenge-actions">
                  <button class="btn-update" onclick="updateChallenge(<?= $challenge['id'] ?>, <?= $challenge['progress'] ?>, <?= $challenge['target_value'] ?>)">
                    📈 Haladás Frissítése
                  </button>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Elérhető Kihívások -->
    <div id="available" class="tab-content">
      <div class="card">
        <h3>🆕 Elérhető Kihívások</h3>
        <p style="color: #9ca3af; margin-bottom: 30px;">Csatlakozz és teljesítsd a kihívásokat pontokért és jutalmakért!</p>
        
        <?php if (empty($available_challenges)): ?>
          <div class="no-data">
            <h4>😔 Jelenleg nincs elérhető kihívás.</h4>
            <p>Hamarosan új kihívásokkal bővítjük a kínálatot!</p>
          </div>
        <?php else: ?>
          <?php 
          // Ellenőrizzük, hogy a felhasználó már csatlakozott-e
          $joined_ids = array_column($user_challenges, 'id');
          
          foreach ($available_challenges as $challenge): 
            $already_joined = in_array($challenge['id'], $joined_ids);
            
            $difficulty_class = 'difficulty-medium';
            if ($challenge['difficulty'] === 'könnyű') $difficulty_class = 'difficulty-easy';
            if ($challenge['difficulty'] === 'nehéz') $difficulty_class = 'difficulty-hard';
          ?>
            <div class="challenge-item">
              <div class="challenge-header">
                <div class="challenge-title"><?= htmlspecialchars($challenge['title']) ?></div>
                <div class="difficulty-badge <?= $difficulty_class ?>">
                  <?= htmlspecialchars($challenge['difficulty']) ?>
                </div>
              </div>
              
              <p style="color: #d1d5db; margin-bottom: 15px;"><?= htmlspecialchars($challenge['description']) ?></p>
              
              <div class="challenge-info">
                <div class="challenge-info-item">
                  <span>🎯</span>
                  <span>Cél: <?= $challenge['target_value'] ?> <?= htmlspecialchars($challenge['unit']) ?></span>
                </div>
                <div class="challenge-info-item">
                  <span>⏱️</span>
                  <span><?= $challenge['duration_days'] ?> nap</span>
                </div>
                <div class="challenge-info-item">
                  <span>💪</span>
                  <span><?= htmlspecialchars($challenge['category']) ?></span>
                </div>
              </div>
              
              <?php if ($challenge['reward_points'] > 0): ?>
                <div class="reward-points">
                  ⭐ Jutalom: <?= $challenge['reward_points'] ?> pont
                </div>
              <?php endif; ?>
              
              <div class="challenge-actions">
                <?php if ($already_joined): ?>
                  <button class="btn-update" disabled style="opacity: 0.5; cursor: not-allowed;">
                    ✅ Már csatlakoztál
                  </button>
                <?php else: ?>
                  <a href="?join_challenge=<?= $challenge['id'] ?>" class="btn-join">
                    🚀 Csatlakozás
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Új Cél Létrehozása -->
    <div id="new-goal" class="tab-content">
      <div class="card">
        <h3>➕ Új Személyes Cél</h3>
        <form method="POST">
          <input type="hidden" name="add_goal" value="1">
          
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">🎯 Cél címe</label>
              <input type="text" name="goal_title" class="form-control" placeholder="pl. Elérni a 100kg guggolást" required>
            </div>
            
            <div class="col-md-4">
              <label class="form-label">📁 Típus</label>
              <select name="goal_type" class="form-select" required>
                <option value="Súly">Súly</option>
                <option value="Testzsír %">Testzsír %</option>
                <option value="Edzésszám">Edzésszám</option>
                <option value="Gyakorlat">Gyakorlat</option>
                <option value="Egyéb">Egyéb</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">🎯 Célérték</label>
              <input type="number" name="target_value" class="form-control" placeholder="pl. 100" step="0.1" required>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">📅 Határidő (opcionális)</label>
              <input type="date" name="target_date" class="form-control">
            </div>
            
            <div class="col-12">
              <label class="form-label">📝 Leírás (opcionális)</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Add meg a cél részleteit..."></textarea>
            </div>
            
            <div class="col-12">
              <button type="submit" class="btn-main">💾 Cél Létrehozása</button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>

  <script>
    function switchTab(tabId) {
      // Összes tab-content elrejtése
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      
      // Összes tab-btn inaktiválása
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Kiválasztott tab megjelenítése
      document.getElementById(tabId).classList.add('active');
      event.target.classList.add('active');
    }
    
    function updateGoal(goalId, currentValue, targetValue) {
      const newValue = prompt(`Jelenlegi érték: ${currentValue}\nCélérték: ${targetValue}\n\nÚj érték:`, currentValue);
      
      if (newValue !== null && newValue !== '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="update_goal" value="1">
          <input type="hidden" name="goal_id" value="${goalId}">
          <input type="hidden" name="current_value" value="${newValue}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
    
    function updateChallenge(challengeId, currentProgress, targetValue) {
      const newProgress = prompt(`Jelenlegi haladás: ${currentProgress}\nCél: ${targetValue}\n\nÚj haladás:`, currentProgress);
      
      if (newProgress !== null && newProgress !== '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="update_challenge" value="1">
          <input type="hidden" name="challenge_id" value="${challengeId}">
          <input type="hidden" name="progress" value="${newProgress}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>

</body>
</html>