<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$user_id = $_SESSION['user_id'];
$is_premium = isPremium($user_id);

// Prémium ellenőrzés - csak prémium felhasználóknak
if (!$is_premium) {
  header('Location: upgrade.php?reason=support');
  exit;
}

// Felhasználó adatainak lekérése
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

// Új kérdés beküldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_question'])) {
  $question = trim($_POST['question'] ?? '');
  $category = $_POST['category'] ?? 'általános';
  
  if ($question) {
    $stmt = $pdo->prepare("INSERT INTO support_tickets (user_id, category, question, status) VALUES (?, ?, ?, 'open')");
    $stmt->execute([$user_id, $category, $question]);
    
    $ticket_id = $pdo->lastInsertId();
    
    header('Location: support.php?success=1&ticket_id=' . $ticket_id);
    exit;
  }
}

// Válasz olvasottá jelölése
if (isset($_GET['mark_read'])) {
  $ticket_id = $_GET['mark_read'];
  $stmt = $pdo->prepare("UPDATE support_tickets SET user_read = 1 WHERE id = ? AND user_id = ?");
  $stmt->execute([$ticket_id, $user_id]);
  header('Location: support.php');
  exit;
}

// Ticket lezárása
if (isset($_GET['close_ticket'])) {
  $ticket_id = $_GET['close_ticket'];
  $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed' WHERE id = ? AND user_id = ?");
  $stmt->execute([$ticket_id, $user_id]);
  header('Location: support.php?closed=1');
  exit;
}

// Felhasználó kérdéseinek lekérése
$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

// Statisztikák
$stmt = $pdo->prepare("
  SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered_count,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count,
    SUM(CASE WHEN status = 'answered' AND user_read = 0 THEN 1 ELSE 0 END) as unread_count
  FROM support_tickets 
  WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edző Támogatás - OnlineEdző</title>
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
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 50px;
    }
    
    .stat-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 20px;
      padding: 25px;
      text-align: center;
      transition: all 0.3s;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(88, 166, 255, 0.3);
    }
    
    .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 900;
      color: #58a6ff;
      margin-bottom: 5px;
    }
    
    .stat-label {
      color: #9ca3af;
      font-size: 0.95rem;
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
    
    .alert-success {
      background: rgba(34, 197, 94, 0.2);
      border: 2px solid #22c55e;
      color: #7fffd4;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 30px;
      text-align: center;
      font-weight: 600;
      animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
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
    
    textarea.form-control {
      resize: vertical;
      min-height: 150px;
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
    
    .ticket-item {
      background: rgba(0,0,0,0.3);
      border-radius: 18px;
      padding: 25px;
      margin-bottom: 20px;
      border-left: 5px solid #238636;
      transition: all 0.3s;
    }
    
    .ticket-item:hover {
      background: rgba(0,0,0,0.4);
      transform: translateX(5px);
    }
    
    .ticket-item.answered {
      border-left-color: #fbbf24;
    }
    
    .ticket-item.answered.unread {
      border-left-color: #ef4444;
      box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
    }
    
    .ticket-item.closed {
      opacity: 0.6;
      border-left-color: #6b7280;
    }
    
    .ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .ticket-title {
      font-size: 1.2rem;
      font-weight: 700;
      color: #fff;
      flex: 1;
    }
    
    .ticket-status {
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    .status-open {
      background: rgba(88, 166, 255, 0.2);
      color: #58a6ff;
    }
    
    .status-answered {
      background: rgba(251, 191, 36, 0.2);
      color: #fbbf24;
    }
    
    .status-closed {
      background: rgba(107, 114, 128, 0.2);
      color: #9ca3af;
    }
    
    .ticket-meta {
      color: #9ca3af;
      font-size: 0.9rem;
      margin-bottom: 15px;
    }
    
    .ticket-question {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.2);
      border-radius: 12px;
      padding: 20px;
      color: #d1d5db;
      margin-bottom: 15px;
      line-height: 1.6;
    }
    
    .ticket-answer {
      background: rgba(34, 197, 94, 0.1);
      border: 1px solid rgba(34, 197, 94, 0.2);
      border-radius: 12px;
      padding: 20px;
      color: #d1d5db;
      margin-bottom: 15px;
      line-height: 1.6;
    }
    
    .ticket-answer strong {
      color: #22c55e;
      display: block;
      margin-bottom: 10px;
      font-size: 1.1rem;
    }
    
    .ticket-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    .btn-small {
      padding: 8px 20px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
      border: none;
    }
    
    .btn-read {
      background: rgba(88, 166, 255, 0.2);
      color: #58a6ff;
      border: 1px solid #58a6ff;
    }
    
    .btn-read:hover {
      background: rgba(88, 166, 255, 0.3);
      color: #58a6ff;
      transform: translateY(-2px);
    }
    
    .btn-close {
      background: rgba(107, 114, 128, 0.2);
      color: #9ca3af;
      border: 1px solid #6b7280;
    }
    
    .btn-close:hover {
      background: rgba(107, 114, 128, 0.3);
      color: #9ca3af;
      transform: translateY(-2px);
    }
    
    .no-tickets {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    
    .no-tickets h4 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: #58a6ff;
    }
    
    .unread-badge {
      background: #ef4444;
      color: white;
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 700;
      margin-left: 10px;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
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
        padding: 30px 20px;
      }
      
      .ticket-item {
        padding: 20px 15px;
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
    <h2>💬 Edző Támogatás</h2>
    <p class="subtitle">Kérdezz, és személyesen válaszolok! <?php if ($stats['unread_count'] > 0): ?><span class="unread-badge">🔔 <?= $stats['unread_count'] ?> új válasz!</span><?php endif; ?></p>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert-success">
        ✅ Kérdésed sikeresen elküldve! Hamarosan válaszolok rá. (Jegy #<?= htmlspecialchars($_GET['ticket_id']) ?>)
      </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['closed'])): ?>
      <div class="alert-success">
        ✅ Jegy sikeresen lezárva!
      </div>
    <?php endif; ?>

    <!-- Statisztikák -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div class="stat-label">Összes kérdés</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🕐</div>
        <div class="stat-number"><?= $stats['open_count'] ?></div>
        <div class="stat-label">Várakozás</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💬</div>
        <div class="stat-number"><?= $stats['answered_count'] ?></div>
        <div class="stat-label">Megválaszolva</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?= $stats['closed_count'] ?></div>
        <div class="stat-label">Lezárva</div>
      </div>
    </div>

    <!-- Új kérdés -->
    <div class="card">
      <h3>❓ Új Kérdés Küldése</h3>
      <form method="POST">
        <input type="hidden" name="send_question" value="1">
        
        <div class="mb-4">
          <label class="form-label">📁 Kategória</label>
          <select name="category" class="form-select" required>
            <option value="edzésterv">🏋️ Edzésterv</option>
            <option value="táplálkozás">🍎 Táplálkozás</option>
            <option value="gyakorlat technika">💪 Gyakorlat technika</option>
            <option value="motiváció">⚡ Motiváció</option>
            <option value="sérülés">🏥 Sérülés / Fájdalom</option>
            <option value="kiegészítők">💊 Kiegészítők</option>
            <option value="általános">❓ Általános</option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="form-label">💬 Kérdésed</label>
          <textarea name="question" class="form-control" rows="6" placeholder="Írd le részletesen a kérdésedet, minél több információt adsz, annál pontosabb válasz tudok adni!" required></textarea>
        </div>
        
        <button type="submit" class="btn-main">📤 Kérdés Elküldése</button>
      </form>
    </div>

    <!-- Kérdések listája -->
    <div class="card">
      <h3>📜 Kérdéseim</h3>
      
      <?php if (empty($tickets)): ?>
        <div class="no-tickets">
          <h4>📭 Még nincs kérdésed</h4>
          <p>Töltsd ki a fenti űrlapot az első kérdésed elküldéséhez!</p>
        </div>
      <?php else: ?>
        <?php foreach ($tickets as $ticket): 
          $status_class = 'status-' . $ticket['status'];
          $is_unread = ($ticket['status'] === 'answered' && $ticket['user_read'] == 0);
        ?>
          <div class="ticket-item <?= $ticket['status'] ?> <?= $is_unread ? 'unread' : '' ?>">
            <div class="ticket-header">
              <div class="ticket-title">
                🎫 Jegy #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['category']) ?>
                <?php if ($is_unread): ?>
                  <span class="unread-badge">ÚJ</span>
                <?php endif; ?>
              </div>
              <div class="ticket-status <?= $status_class ?>">
                <?php 
                  $status_text = [
                    'open' => '🕐 Várakozik',
                    'answered' => '💬 Megválaszolva',
                    'closed' => '✅ Lezárva'
                  ];
                  echo $status_text[$ticket['status']] ?? $ticket['status'];
                ?>
              </div>
            </div>
            
            <div class="ticket-meta">
              📅 Létrehozva: <?= date('Y.m.d H:i', strtotime($ticket['created_at'])) ?>
              <?php if ($ticket['answered_at']): ?>
                | 💬 Válaszolva: <?= date('Y.m.d H:i', strtotime($ticket['answered_at'])) ?>
              <?php endif; ?>
            </div>
            
            <div class="ticket-question">
              <strong>❓ Te kérdezted:</strong><br>
              <?= nl2br(htmlspecialchars($ticket['question'])) ?>
            </div>
            
            <?php if ($ticket['answer']): ?>
              <div class="ticket-answer">
                <strong>💡 Edződ válasza:</strong>
                <?= nl2br(htmlspecialchars($ticket['answer'])) ?>
              </div>
            <?php endif; ?>
            
            <?php if ($ticket['status'] !== 'closed'): ?>
              <div class="ticket-actions">
                <?php if ($is_unread): ?>
                  <a href="?mark_read=<?= $ticket['id'] ?>" class="btn-small btn-read">
                    ✅ Elolvastam
                  </a>
                <?php endif; ?>
                
                <?php if ($ticket['status'] === 'answered'): ?>
                  <a href="?close_ticket=<?= $ticket['id'] ?>" class="btn-small btn-close" onclick="return confirm('Biztosan lezárod ezt a jegyet?')">
                    🔒 Lezárás
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>