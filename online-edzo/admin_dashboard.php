<?php
session_start();
require_once 'db.php';
include_once 'premium_check.php';

// Admin ellenőrzés
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

// Statisztikák lekérése
$stats = [];

// Összes felhasználó
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Prémium felhasználók
$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM subscriptions WHERE status = 'active'");
$stats['premium_users'] = $stmt->fetch()['total'];

// Nyitott support jegyek
$stmt = $pdo->query("SELECT COUNT(*) as total FROM support_tickets WHERE status = 'open'");
$stats['open_tickets'] = $stmt->fetch()['total'];

// Mai edzések
$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM workout_log WHERE date = CURDATE()");
$stats['today_workouts'] = $stmt->fetch()['total'];

// Aktív kihívások
$stmt = $pdo->query("SELECT COUNT(*) as total FROM user_challenges WHERE status = 'active'");
$stats['active_challenges'] = $stmt->fetch()['total'];

// Legutóbbi regisztrációk (5)
$stmt = $pdo->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Legutóbbi support jegyek (5)
$stmt = $pdo->query("
  SELECT st.*, u.username, u.email 
  FROM support_tickets st 
  LEFT JOIN users u ON st.user_id = u.id 
  ORDER BY st.created_at DESC 
  LIMIT 5
");
$recent_tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - OnlineEdző</title>
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
      border-bottom: 2px solid rgba(239, 68, 68, 0.3);
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
      margin: 0 5px;
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
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }
    
    .subtitle {
      color: #9ca3af;
      margin-bottom: 40px;
      font-size: 1.1rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
    }
    
    .stat-card {
      background: rgba(10, 14, 39, 0.6);
      border: 2px solid rgba(239, 68, 68, 0.2);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ef4444, #dc2626);
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(239, 68, 68, 0.3);
      border-color: #ef4444;
    }
    
    .stat-icon {
      font-size: 3rem;
      margin-bottom: 15px;
      filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.5));
    }
    
    .stat-number {
      font-size: 3rem;
      font-weight: 900;
      color: #ef4444;
      margin-bottom: 10px;
      text-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
    }
    
    .stat-label {
      color: #9ca3af;
      font-size: 1rem;
      font-weight: 600;
    }
    
    .actions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
    }
    
    .action-card {
      background: rgba(10, 14, 39, 0.6);
      border: 2px solid rgba(88, 166, 255, 0.2);
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
      text-decoration: none;
      display: block;
    }
    
    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(88, 166, 255, 0.3);
      border-color: #58a6ff;
    }
    
    .action-icon {
      font-size: 3.5rem;
      margin-bottom: 20px;
    }
    
    .action-title {
      color: #58a6ff;
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .action-desc {
      color: #9ca3af;
      font-size: 0.95rem;
    }
    
    .section {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 25px;
      padding: 35px;
      margin-bottom: 30px;
    }
    
    .section h3 {
      color: #ef4444;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 25px;
      border-bottom: 2px solid rgba(239, 68, 68, 0.2);
      padding-bottom: 15px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th {
      background: rgba(239, 68, 68, 0.1);
      color: #fca5a5;
      padding: 15px;
      text-align: left;
      font-weight: 700;
      border-bottom: 2px solid rgba(239, 68, 68, 0.3);
    }
    
    td {
      padding: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      color: #d1d5db;
    }
    
    tr:hover {
      background: rgba(239, 68, 68, 0.05);
    }
    
    .badge {
      padding: 6px 15px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 700;
    }
    
    .badge-open {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
    }
    
    .badge-answered {
      background: rgba(251, 191, 36, 0.2);
      color: #fbbf24;
    }
    
    .badge-closed {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
    }
    
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      h2 {
        font-size: 2rem;
      }
      
      .stats-grid, .actions-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <nav class="d-flex justify-content-between align-items-center">
    <div class="logo">🔐 ADMIN DASHBOARD</div>
    <div>
      <a href="admin_dashboard.php">📊 Dashboard</a>
      <a href="support_admin.php">💬 Support</a>
      <a href="index.php">👤 Előnézet</a>
      <a href="admin_logout.php">🚪 Kilépés</a>
    </div>
  </nav>

  <div class="container">
    <h2>⚡ Vezérlőpult</h2>
    <p class="subtitle">Üdv, Admin! Teljes hozzáférés az alkalmazáshoz.</p>

    <!-- Statisztikák -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?= $stats['total_users'] ?></div>
        <div class="stat-label">Összes Felhasználó</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⭐</div>
        <div class="stat-number"><?= $stats['premium_users'] ?></div>
        <div class="stat-label">Prémium Tagok</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🎫</div>
        <div class="stat-number"><?= $stats['open_tickets'] ?></div>
        <div class="stat-label">Nyitott Jegyek</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">💪</div>
        <div class="stat-number"><?= $stats['today_workouts'] ?></div>
        <div class="stat-label">Mai Edzések</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-number"><?= $stats['active_challenges'] ?></div>
        <div class="stat-label">Aktív Kihívások</div>
      </div>
    </div>

    <!-- Gyors Műveletek -->
    <h3 style="color: #58a6ff; font-size: 2rem; font-weight: 700; margin-bottom: 30px;">⚡ Gyors Műveletek</h3>
    <div class="actions-grid">
      <a href="support_admin.php" class="action-card">
        <div class="action-icon">💬</div>
        <div class="action-title">Support Jegyek</div>
        <div class="action-desc">Válaszolj a felhasználók kérdéseire</div>
      </a>
      
      <a href="index.php" class="action-card">
        <div class="action-icon">🏠</div>
        <div class="action-title">Oldal Használata</div>
        <div class="action-desc">Nézd meg az oldalt felhasználóként</div>
      </a>
      
      <a href="challenges.php" class="action-card">
        <div class="action-icon">🏆</div>
        <div class="action-title">Kihívások</div>
        <div class="action-desc">Kezeld a kihívásokat és célokat</div>
      </a>
      
      <a href="workout_log.php" class="action-card">
        <div class="action-icon">📝</div>
        <div class="action-title">Edzésnapló</div>
        <div class="action-desc">Rögzítsd az edzéseidet</div>
      </a>
      
      <a href="body_tracker.php" class="action-card">
        <div class="action-icon">📊</div>
        <div class="action-title">Testméret Követés</div>
        <div class="action-desc">Kövesd a változásokat</div>
      </a>
      
      <a href="exercise_browser.php" class="action-card">
        <div class="action-icon">💪</div>
        <div class="action-title">Gyakorlatok</div>
        <div class="action-desc">Böngészd a gyakorlatokat</div>
      </a>
    </div>

    <!-- Legutóbbi Regisztrációk -->
    <div class="section">
      <h3>👥 Legutóbbi Regisztrációk</h3>
      <?php if (empty($recent_users)): ?>
        <p style="text-align: center; color: #9ca3af; padding: 40px;">Még nincs új regisztráció</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Felhasználónév</th>
              <th>Email</th>
              <th>Regisztráció</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_users as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= date('Y.m.d H:i', strtotime($user['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Legutóbbi Support Jegyek -->
    <div class="section">
      <h3>💬 Legutóbbi Support Jegyek</h3>
      <?php if (empty($recent_tickets)): ?>
        <p style="text-align: center; color: #9ca3af; padding: 40px;">Még nincs support jegy</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Felhasználó</th>
              <th>Kategória</th>
              <th>Státusz</th>
              <th>Létrehozva</th>
              <th>Művelet</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_tickets as $ticket): ?>
              <tr>
                <td>#<?= $ticket['id'] ?></td>
                <td><?= htmlspecialchars($ticket['username']) ?></td>
                <td><?= htmlspecialchars($ticket['category']) ?></td>
                <td>
                  <?php
                    $badge_class = 'badge-' . $ticket['status'];
                    $status_text = [
                      'open' => '🔴 Nyitott',
                      'answered' => '💬 Válaszolt',
                      'closed' => '✅ Lezárt'
                    ];
                  ?>
                  <span class="badge <?= $badge_class ?>">
                    <?= $status_text[$ticket['status']] ?? $ticket['status'] ?>
                  </span>
                </td>
                <td><?= date('Y.m.d H:i', strtotime($ticket['created_at'])) ?></td>
                <td>
                  <a href="support_admin.php?filter=<?= $ticket['status'] ?>" style="color: #58a6ff; text-decoration: none;">
                    Megtekintés →
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>

</body>
</html>