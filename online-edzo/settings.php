<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$uid = $_SESSION['user_id'];
$message = '';
$error = '';

// Felhasználó adatainak lekérése
$stmt = $pdo->prepare("SELECT username, email, daily_calorie_goal FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Előfizetés lekérése
$subscription = getUserPlan($uid);

// Profil frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $username = trim($_POST['username'] ?? '');
  $calorie_goal = $_POST['calorie_goal'] ?? 2000;
  
  if (empty($username)) {
    $error = 'A felhasználónév nem lehet üres!';
  } else {
    $stmt = $pdo->prepare("UPDATE users SET username = ?, daily_calorie_goal = ? WHERE id = ?");
    $stmt->execute([$username, $calorie_goal, $uid]);
    $message = '✅ Profil sikeresen frissítve!';
    $user['username'] = $username;
    $user['daily_calorie_goal'] = $calorie_goal;
  }
}

// Jelszó változtatás
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $old_password = $_POST['old_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    $error = 'Minden mező kitöltése kötelező!';
  } elseif ($new_password !== $confirm_password) {
    $error = 'Az új jelszavak nem egyeznek!';
  } elseif (strlen($new_password) < 6) {
    $error = 'A jelszó minimum 6 karakter legyen!';
  } else {
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $current_hash = $stmt->fetch()['password_hash'];
    
    if (password_verify($old_password, $current_hash)) {
      $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
      $stmt->execute([$new_hash, $uid]);
      $message = '✅ Jelszó sikeresen megváltoztatva!';
    } else {
      $error = 'A régi jelszó helytelen!';
    }
  }
}

// Előfizetés lemondása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_subscription'])) {
  $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
  $stmt->execute([$uid]);
  $message = '✅ Előfizetés lemondva. A fiókod prémium marad a jelenlegi időszak végéig.';
  $subscription = null;
}

// Fiók törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
  $confirm = $_POST['confirm_delete'] ?? '';
  
  if ($confirm === 'TÖRLÉS') {
    // Töröljük a kapcsolódó adatokat
    $pdo->prepare("DELETE FROM workout_log WHERE user_id = ?")->execute([$uid]);
    $pdo->prepare("DELETE FROM body_measurements WHERE user_id = ?")->execute([$uid]);
    $pdo->prepare("DELETE FROM progress_photos WHERE user_id = ?")->execute([$uid]);
    $pdo->prepare("DELETE FROM nutrition_log WHERE user_id = ?")->execute([$uid]);
    $pdo->prepare("DELETE FROM subscriptions WHERE user_id = ?")->execute([$uid]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    
    session_destroy();
    header('Location: index.php?deleted=1');
    exit;
  } else {
    $error = 'Írd be pontosan: TÖRLÉS (nagy betűkkel)';
  }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beállítások - OnlineEdző</title>
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
      max-width: 900px;
      margin: 60px auto;
      padding: 0 20px 80px;
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
      font-size: 1.6rem;
      font-weight: 700;
    }
    
    .card.danger {
      border-color: rgba(220, 38, 38, 0.3);
    }
    
    .card.danger h3 {
      color: #ef4444;
    }
    
    .form-label {
      color: #9bbcff;
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 14px 18px;
      transition: all 0.3s;
    }
    
    .form-control:focus {
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
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 35px;
      font-weight: 700;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }
    
    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(220, 38, 38, 0.6);
      color: white;
    }
    
    .alert-success {
      background: rgba(34, 197, 94, 0.2);
      border: 2px solid #22c55e;
      color: #7fffd4;
      border-radius: 15px;
      padding: 18px;
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
    }
    
    .alert-danger {
      background: rgba(220, 38, 38, 0.2);
      border: 2px solid #dc2626;
      color: #fca5a5;
      border-radius: 15px;
      padding: 18px;
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
    }
    
    .subscription-info {
      background: rgba(251, 191, 36, 0.1);
      border: 2px solid #fbbf24;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 25px;
    }
    
    .subscription-info h4 {
      color: #fbbf24;
      margin-bottom: 15px;
      font-size: 1.3rem;
    }
    
    .subscription-info p {
      color: #d1d5db;
      margin-bottom: 8px;
    }
    
    .warning-box {
      background: rgba(220, 38, 38, 0.1);
      border: 2px solid #dc2626;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .warning-box p {
      color: #fca5a5;
      margin: 0;
      font-weight: 600;
    }
    
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      h2 {
        font-size: 2.5rem;
      }
      
      .card {
        padding: 30px 25px;
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
    <h2>⚙️ Beállítások</h2>

    <?php if ($message): ?>
      <div class="alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Profil adatok -->
    <div class="card">
      <h3>👤 Profil beállítások</h3>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Felhasználónév</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Email cím (nem változtatható)</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Napi kalória cél (kcal)</label>
          <input type="number" name="calorie_goal" class="form-control" value="<?= $user['daily_calorie_goal'] ?? 2000 ?>" min="1000" max="5000">
        </div>
        
        <div class="text-end">
          <button type="submit" name="update_profile" class="btn-main">💾 Mentés</button>
        </div>
      </form>
    </div>

    <!-- Jelszó változtatás -->
    <div class="card">
      <h3>🔐 Jelszó változtatás</h3>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Jelenlegi jelszó</label>
          <input type="password" name="old_password" class="form-control" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Új jelszó</label>
          <input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter" required>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Új jelszó megerősítése</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="text-end">
          <button type="submit" name="change_password" class="btn-main">🔄 Jelszó megváltoztatása</button>
        </div>
      </form>
    </div>

    <!-- Előfizetés kezelése -->
    <div class="card">
      <h3>💎 Előfizetés kezelése</h3>
      
      <?php if ($subscription && $subscription['status'] === 'active'): ?>
        <div class="subscription-info">
          <h4>✅ Aktív előfizetés</h4>
          <p><strong>Típus:</strong> <?= ucfirst($subscription['plan_type']) ?></p>
          <p><strong>Kezdet:</strong> <?= $subscription['start_date'] ?></p>
          <p><strong>Lejár:</strong> <?= $subscription['end_date'] ?? 'Korlátlan' ?></p>
        </div>
        
        <form method="post" onsubmit="return confirm('Biztosan lemondod az előfizetést? A fiókod prémium marad a jelenlegi időszak végéig.')">
          <button type="submit" name="cancel_subscription" class="btn-danger">❌ Előfizetés lemondása</button>
        </form>
      <?php else: ?>
        <p style="color: #d1d5db; margin-bottom: 20px;">
          Jelenleg nincs aktív előfizetésed. Frissíts prémiumra és használd ki az összes funkciót!
        </p>
        <a href="upgrade.php" class="btn-main" style="text-decoration: none; display: inline-block;">
          ⭐ Prémium vásárlás
        </a>
      <?php endif; ?>
    </div>

    <!-- Fiók törlése -->
    <div class="card danger">
      <h3>🗑️ Fiók törlése</h3>
      
      <div class="warning-box">
        <p>⚠️ FIGYELEM! Ez a művelet VISSZAFORDÍTHATATLAN!</p>
      </div>
      
      <p style="color: #d1d5db; margin-bottom: 20px;">
        A fiókod törlésével az alábbi adatok <strong>véglegesen elvesznek</strong>:
      </p>
      <ul style="color: #d1d5db; margin-bottom: 25px;">
        <li>Összes edzésnapló</li>
        <li>Testméret mérések</li>
        <li>Progress fotók</li>
        <li>Táplálkozási adatok</li>
        <li>Előfizetési információk</li>
      </ul>
      
      <form method="post" onsubmit="return confirm('UTOLSÓ FIGYELMEZTETÉS! Tényleg törlöd a fiókodat? Ez a művelet visszafordíthatatlan!')">
        <div class="mb-3">
          <label class="form-label" style="color: #ef4444;">
            Írd be a "TÖRLÉS" szót (nagy betűkkel) a megerősítéshez:
          </label>
          <input type="text" name="confirm_delete" class="form-control" placeholder="TÖRLÉS" required>
        </div>
        
        <button type="submit" name="delete_account" class="btn-danger">
          🗑️ Fiók végleges törlése
        </button>
      </form>
    </div>

  </div>

</body>
</html>