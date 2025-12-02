<?php
session_start();
require_once 'db.php';

// Admin kijelentkezés
if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: admin_login.php');
  exit;
}

// Válasz küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_answer'])) {
  $ticket_id = $_POST['ticket_id'] ?? 0;
  $answer = trim($_POST['answer'] ?? '');
  
  if ($ticket_id && $answer) {
    $stmt = $pdo->prepare("UPDATE support_tickets SET answer = ?, answered_at = NOW(), status = 'answered', user_read = 0 WHERE id = ?");
    $stmt->execute([$answer, $ticket_id]);
    
    header('Location: support_admin.php?answered=' . $ticket_id);
    exit;
  }
}

// Válasz szerkesztése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_answer'])) {
  $ticket_id = $_POST['ticket_id'] ?? 0;
  $answer = trim($_POST['answer'] ?? '');
  
  if ($ticket_id && $answer) {
    $stmt = $pdo->prepare("UPDATE support_tickets SET answer = ?, user_read = 0 WHERE id = ?");
    $stmt->execute([$answer, $ticket_id]);
    
    header('Location: support_admin.php?edited=' . $ticket_id);
    exit;
  }
}

// Ticket törlése
if (isset($_GET['delete'])) {
  $ticket_id = $_GET['delete'];
  $stmt = $pdo->prepare("DELETE FROM support_tickets WHERE id = ?");
  $stmt->execute([$ticket_id]);
  header('Location: support_admin.php?deleted=1');
  exit;
}

// Ticket lezárása
if (isset($_GET['close'])) {
  $ticket_id = $_GET['close'];
  $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'closed' WHERE id = ?");
  $stmt->execute([$ticket_id]);
  header('Location: support_admin.php?closed=1');
  exit;
}

// Szűrés
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$where_clauses = [];
$params = [];

if ($filter === 'open') {
  $where_clauses[] = "status = 'open'";
} elseif ($filter === 'answered') {
  $where_clauses[] = "status = 'answered'";
} elseif ($filter === 'closed') {
  $where_clauses[] = "status = 'closed'";
}

if ($search) {
  $where_clauses[] = "(question LIKE ? OR answer LIKE ? OR users.username LIKE ? OR users.email LIKE ?)";
  $search_param = "%$search%";
  $params = [$search_param, $search_param, $search_param, $search_param];
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Összes ticket lekérése felhasználó adatokkal
$sql = "
  SELECT 
    support_tickets.*,
    users.username,
    users.email
  FROM support_tickets
  LEFT JOIN users ON support_tickets.user_id = users.id
  $where_sql
  ORDER BY 
    CASE 
      WHEN support_tickets.status = 'open' THEN 1
      WHEN support_tickets.status = 'answered' THEN 2
      WHEN support_tickets.status = 'closed' THEN 3
    END,
    support_tickets.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// Statisztikák
$stmt = $pdo->query("
  SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered_count,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count
  FROM support_tickets
");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Támogatás Panel</title>
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
    
    nav {
      background: rgba(10, 14, 39, 0.8);
      backdrop-filter: blur(20px);
      padding: 20px 50px;
      border-bottom: 1px solid rgba(255, 68, 68, 0.3);
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 30px rgba(0,0,0,0.3);
    }
    
    .logo {
      font-size: 1.8rem;
      font-weight: 900;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -1px;
    }
    
    nav a {
      color: #fca5a5;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      padding: 8px 20px;
      border-radius: 8px;
    }
    
    nav a:hover {
      color: #ef4444;
      background: rgba(239, 68, 68, 0.1);
    }
    
    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 40px;
      font-size: 1.1rem;
    }
    
    .alert-success {
      background: rgba(34, 197, 94, 0.2);
      border: 2px solid #22c55e;
      color: #7fffd4;
      border-radius: 15px;
      padding: 18px;
      margin-bottom: 30px;
      text-align: center;
      font-weight: 600;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .stat-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 20px;
      padding: 25px;
      text-align: center;
      transition: all 0.3s;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 900;
      color: #ef4444;
      margin-bottom: 10px;
    }
    
    .stat-label {
      color: #9ca3af;
      font-size: 1rem;
    }
    
    .filters {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 30px;
    }
    
    .filter-buttons {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    
    .filter-btn {
      padding: 10px 25px;
      border-radius: 10px;
      border: 2px solid rgba(239, 68, 68, 0.3);
      background: transparent;
      color: #fca5a5;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
    }
    
    .filter-btn:hover, .filter-btn.active {
      background: rgba(239, 68, 68, 0.2);
      border-color: #ef4444;
      color: #ef4444;
      transform: translateY(-2px);
    }
    
    .search-box {
      display: flex;
      gap: 15px;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 12px 18px;
      flex: 1;
    }
    
    .form-control:focus {
      background: rgba(255,255,255,0.12);
      border-color: #ef4444;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
      outline: none;
    }
    
    .btn-search {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 12px;
      font-weight: 700;
      cursor: pointer;
    }
    
    .ticket-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 25px;
      transition: all 0.3s;
    }
    
    .ticket-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }
    
    .ticket-card.open {
      border-color: #ef4444;
      box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
    }
    
    .ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .ticket-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #fff;
    }
    
    .ticket-status {
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 700;
    }
    
    .status-open {
      background: rgba(239, 68, 68, 0.3);
      color: #ef4444;
    }
    
    .status-answered {
      background: rgba(251, 191, 36, 0.3);
      color: #fbbf24;
    }
    
    .status-closed {
      background: rgba(107, 114, 128, 0.3);
      color: #9ca3af;
    }
    
    .user-info {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.2);
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .question-box {
      background: rgba(255, 255, 255, 0.05);
      border-left: 4px solid #ef4444;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
    }
    
    .answer-box {
      background: rgba(34, 197, 94, 0.1);
      border-left: 4px solid #22c55e;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
    }
    
    textarea {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 15px;
      width: 100%;
      min-height: 150px;
      resize: vertical;
      font-family: 'Poppins', sans-serif;
    }
    
    textarea:focus {
      background: rgba(255,255,255,0.12);
      border-color: #ef4444;
      outline: none;
    }
    
    .btn-main {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      margin-right: 10px;
    }
    
    .btn-main:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(34, 197, 94, 0.4);
    }
    
    .btn-danger {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
      border: 1px solid #ef4444;
      padding: 12px 30px;
      border-radius: 12px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-danger:hover {
      background: rgba(239, 68, 68, 0.3);
      color: #ef4444;
    }
    
    @media (max-width: 768px) {
      h2 {
        font-size: 2rem;
      }
      
      .ticket-card {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>

  <nav class="d-flex justify-content-between align-items-center">
    <div class="logo">🔐 ADMIN PANEL</div>
    <div>
      <a href="support_admin.php">🔄 Frissítés</a>
      <a href="?logout=1">🚪 Kijelentkezés</a>
    </div>
  </nav>

  <div class="container">
    <h2>⚙️ Támogatási Panel</h2>
    <p class="subtitle">Válaszolj a felhasználók kérdéseire</p>

    <?php if (isset($_GET['answered'])): ?>
      <div class="alert-success">✅ Válasz sikeresen elküldve! (Jegy #<?= htmlspecialchars($_GET['answered']) ?>)</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['edited'])): ?>
      <div class="alert-success">✅ Válasz sikeresen módosítva! (Jegy #<?= htmlspecialchars($_GET['edited']) ?>)</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert-success">✅ Jegy sikeresen törölve!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['closed'])): ?>
      <div class="alert-success">✅ Jegy lezárva!</div>
    <?php endif; ?>

    <!-- Statisztikák -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div class="stat-label">📋 Összes</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['open_count'] ?></div>
        <div class="stat-label">🔴 Várakozik</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['answered_count'] ?></div>
        <div class="stat-label">💬 Megválaszolva</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['closed_count'] ?></div>
        <div class="stat-label">✅ Lezárva</div>
      </div>
    </div>

    <!-- Szűrők -->
    <div class="filters">
      <div class="filter-buttons">
        <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">📋 Összes</a>
        <a href="?filter=open" class="filter-btn <?= $filter === 'open' ? 'active' : '' ?>">🔴 Várakozik (<?= $stats['open_count'] ?>)</a>
        <a href="?filter=answered" class="filter-btn <?= $filter === 'answered' ? 'active' : '' ?>">💬 Megválaszolva</a>
        <a href="?filter=closed" class="filter-btn <?= $filter === 'closed' ? 'active' : '' ?>">✅ Lezárva</a>
      </div>
      
      <form method="GET" class="search-box">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="text" name="search" class="form-control" placeholder="🔍 Keresés..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">Keresés</button>
      </form>
    </div>

    <!-- Jegyek -->
    <?php if (empty($tickets)): ?>
      <div style="text-align: center; padding: 80px 20px; color: #9ca3af;">
        <h3>📭 Nincs megjeleníthető jegy</h3>
        <p>Próbálj más szűrőt vagy keresést!</p>
      </div>
    <?php else: ?>
      <?php foreach ($tickets as $ticket): ?>
        <div class="ticket-card <?= $ticket['status'] ?>">
          <div class="ticket-header">
            <div class="ticket-title">
              🎫 #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['category']) ?>
            </div>
            <div class="ticket-status status-<?= $ticket['status'] ?>">
              <?php 
                $status_text = [
                  'open' => '🔴 VÁRAKOZIK',
                  'answered' => '💬 Megválaszolva',
                  'closed' => '✅ Lezárva'
                ];
                echo $status_text[$ticket['status']] ?? $ticket['status'];
              ?>
            </div>
          </div>
          
          <div class="user-info">
            👤 <strong><?= htmlspecialchars($ticket['username']) ?></strong> (<?= htmlspecialchars($ticket['email']) ?>)<br>
            📅 Létrehozva: <?= date('Y.m.d H:i', strtotime($ticket['created_at'])) ?>
            <?php if ($ticket['answered_at']): ?>
              | 💬 Válaszolva: <?= date('Y.m.d H:i', strtotime($ticket['answered_at'])) ?>
            <?php endif; ?>
          </div>
          
          <div class="question-box">
            <strong style="color: #ef4444; display: block; margin-bottom: 10px;">❓ Kérdés:</strong>
            <?= nl2br(htmlspecialchars($ticket['question'])) ?>
          </div>
          
          <?php if ($ticket['answer']): ?>
            <div class="answer-box">
              <strong style="color: #22c55e; display: block; margin-bottom: 10px;">💡 Te válaszoltál:</strong>
              <?= nl2br(htmlspecialchars($ticket['answer'])) ?>
            </div>
            
            <?php if ($ticket['status'] !== 'closed'): ?>
              <form method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="edit_answer" value="1">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <label style="color: #9bbcff; font-weight: 600; display: block; margin-bottom: 10px;">✏️ Válasz szerkesztése:</label>
                <textarea name="answer" required><?= htmlspecialchars($ticket['answer']) ?></textarea>
                <div style="margin-top: 15px;">
                  <button type="submit" class="btn-main">💾 Mentés</button>
                </div>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <form method="POST">
              <input type="hidden" name="send_answer" value="1">
              <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
              <label style="color: #9bbcff; font-weight: 600; display: block; margin-bottom: 10px;">💬 Válaszod:</label>
              <textarea name="answer" placeholder="Írd ide a válaszodat..." required></textarea>
              <div style="margin-top: 15px;">
                <button type="submit" class="btn-main">📤 Válasz Küldése</button>
              </div>
            </form>
          <?php endif; ?>
          
          <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($ticket['status'] !== 'closed'): ?>
              <a href="?close=<?= $ticket['id'] ?>" class="btn-danger" onclick="return confirm('Biztos lezárod?')">
                🔒 Lezárás
              </a>
            <?php endif; ?>
            <a href="?delete=<?= $ticket['id'] ?>" class="btn-danger" onclick="return confirm('Biztos törlöd véglegesen?')">
              🗑️ Törlés
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>