<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$uid = $_SESSION['user_id'];

// Összes edzés száma
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workout_log WHERE user_id = ?");
$stmt->execute([$uid]);
$total_workouts = $stmt->fetch()['total'];

// Összes gyakorlat száma
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT exercise) as total FROM workout_log WHERE user_id = ?");
$stmt->execute([$uid]);
$total_exercises = $stmt->fetch()['total'];

// Legtöbbet edzett izomcsoport
$stmt = $pdo->prepare("SELECT muscle_group, COUNT(*) as count FROM workout_log WHERE user_id = ? GROUP BY muscle_group ORDER BY count DESC LIMIT 1");
$stmt->execute([$uid]);
$top_muscle = $stmt->fetch();

// Mai edzés
$stmt = $pdo->prepare("SELECT COUNT(*) as today FROM workout_log WHERE user_id = ? AND DATE(date) = CURDATE()");
$stmt->execute([$uid]);
$today_workout = $stmt->fetch()['today'];

// Heti statisztika (utolsó 7 nap)
$stmt = $pdo->prepare("SELECT DATE(date) as workout_date, COUNT(*) as count FROM workout_log WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date) ORDER BY date");
$stmt->execute([$uid]);
$weekly_data = $stmt->fetchAll();

// Izomcsoportok megoszlása
$stmt = $pdo->prepare("SELECT muscle_group, COUNT(*) as count FROM workout_log WHERE user_id = ? GROUP BY muscle_group ORDER BY count DESC");
$stmt->execute([$uid]);
$muscle_distribution = $stmt->fetchAll();

// Legnehezebb súlyok gyakorlatonként
$stmt = $pdo->prepare("SELECT exercise, MAX(weight) as max_weight FROM workout_log WHERE user_id = ? AND weight > 0 GROUP BY exercise ORDER BY max_weight DESC LIMIT 5");
$stmt->execute([$uid]);
$personal_records = $stmt->fetchAll();

// Havi edzésszám trend (utolsó 6 hónap)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count FROM workout_log WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY month");
$stmt->execute([$uid]);
$monthly_trend = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statisztikák - OnlineEdző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      max-width: 1400px;
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
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
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
      font-size: 2.8rem;
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
      margin-top: 8px;
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
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0,0,0,0.4);
    }
    
    .card h3 {
      color: #58a6ff;
      margin-bottom: 30px;
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .chart-container {
      position: relative;
      height: 350px;
      margin-top: 20px;
    }
    
    .records-list {
      list-style: none;
      padding: 0;
    }
    
    .record-item {
      background: rgba(0,0,0,0.3);
      border-left: 4px solid #fbbf24;
      padding: 20px 25px;
      margin-bottom: 15px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: 0.3s;
    }
    
    .record-item:hover {
      background: rgba(0,0,0,0.4);
      transform: translateX(5px);
    }
    
    .record-name {
      font-weight: 700;
      color: #fff;
      font-size: 1.15rem;
    }
    
    .record-weight {
      font-size: 1.5rem;
      font-weight: 900;
      color: #fbbf24;
    }
    
    .muscle-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }
    
    .muscle-item {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.2);
      border-radius: 15px;
      padding: 20px;
      text-align: center;
      transition: 0.3s;
    }
    
    .muscle-item:hover {
      background: rgba(88, 166, 255, 0.15);
      transform: scale(1.05);
    }
    
    .muscle-name {
      font-weight: 700;
      color: #58a6ff;
      margin-bottom: 10px;
      font-size: 1.1rem;
    }
    
    .muscle-count {
      font-size: 2rem;
      font-weight: 900;
      color: #fff;
    }
    
    .no-data {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    
    .no-data h4 {
      font-size: 2rem;
      margin-bottom: 20px;
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
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .chart-container {
        height: 280px;
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
    <h2>📊 Statisztikák</h2>

    <?php if ($total_workouts > 0): ?>
    
    <!-- Összesítő statisztikák -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🏋️</div>
        <div class="stat-value"><?= $total_workouts ?></div>
        <div class="stat-label">Összes edzés</div>
        <div class="stat-subtext">Eddig rögzítve</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">💪</div>
        <div class="stat-value"><?= $total_exercises ?></div>
        <div class="stat-label">Gyakorlat típus</div>
        <div class="stat-subtext">Kipróbálva</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div class="stat-value"><?= $today_workout ?></div>
        <div class="stat-label">Mai edzések</div>
        <div class="stat-subtext">Ma teljesítve</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-value"><?= $top_muscle['muscle_group'] ?? '-' ?></div>
        <div class="stat-label">Top izomcsoport</div>
        <div class="stat-subtext"><?= $top_muscle['count'] ?? 0 ?> edzés</div>
      </div>
    </div>

    <!-- Heti aktivitás -->
    <?php if (!empty($weekly_data)): ?>
    <div class="card">
      <h3>📈 Heti aktivitás (utolsó 7 nap)</h3>
      <div class="chart-container">
        <canvas id="weeklyChart"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <!-- Havi trend -->
    <?php if (!empty($monthly_trend)): ?>
    <div class="card">
      <h3>📊 Havi edzésszám trend</h3>
      <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <!-- Személyes rekordok -->
    <?php if (!empty($personal_records)): ?>
    <div class="card">
      <h3>🏆 Személyes rekordok (max súly)</h3>
      <ul class="records-list">
        <?php foreach ($personal_records as $record): ?>
        <li class="record-item">
          <span class="record-name"><?= htmlspecialchars($record['exercise']) ?></span>
          <span class="record-weight"><?= $record['max_weight'] ?> kg</span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Izomcsoport megoszlás -->
    <?php if (!empty($muscle_distribution)): ?>
    <div class="card">
      <h3>💪 Izomcsoport megoszlás</h3>
      <div class="muscle-list">
        <?php foreach ($muscle_distribution as $muscle): ?>
        <div class="muscle-item">
          <div class="muscle-name"><?= htmlspecialchars($muscle['muscle_group']) ?></div>
          <div class="muscle-count"><?= $muscle['count'] ?></div>
          <div style="color: #9ca3af; font-size: 0.9rem;">edzés</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="card">
      <div class="no-data">
        <h4>📊 Még nincs elegendő adat</h4>
        <p>Kezdj el edzéseket rögzíteni, hogy statisztikákat láss!</p>
        <a href="workout_log.php" style="color: #58a6ff; text-decoration: underline; font-weight: 600;">➜ Edzésnapló</a>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <?php if (!empty($weekly_data)): ?>
  <script>
    // Heti aktivitás chart
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyData = <?= json_encode($weekly_data) ?>;
    
    const weeklyLabels = weeklyData.map(d => d.workout_date);
    const weeklyCounts = weeklyData.map(d => parseInt(d.count));
    
    new Chart(weeklyCtx, {
      type: 'bar',
      data: {
        labels: weeklyLabels,
        datasets: [{
          label: 'Edzések száma',
          data: weeklyCounts,
          backgroundColor: 'rgba(88, 166, 255, 0.6)',
          borderColor: '#58a6ff',
          borderWidth: 2,
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: { color: '#e6edf3', font: { size: 14, weight: 600 } }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#9ca3af', font: { weight: 600 }, stepSize: 1 },
            grid: { color: 'rgba(255,255,255,0.05)' }
          },
          x: {
            ticks: { color: '#9ca3af', font: { weight: 600 } },
            grid: { color: 'rgba(255,255,255,0.05)' }
          }
        }
      }
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($monthly_trend)): ?>
  <script>
    // Havi trend chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = <?= json_encode($monthly_trend) ?>;
    
    const monthlyLabels = monthlyData.map(d => d.month);
    const monthlyCounts = monthlyData.map(d => parseInt(d.count));
    
    new Chart(monthlyCtx, {
      type: 'line',
      data: {
        labels: monthlyLabels,
        datasets: [{
          label: 'Havi edzések',
          data: monthlyCounts,
          borderColor: '#238636',
          backgroundColor: 'rgba(35, 134, 54, 0.1)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 6,
          pointBackgroundColor: '#238636',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: { color: '#e6edf3', font: { size: 14, weight: 600 } }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#9ca3af', font: { weight: 600 }, stepSize: 5 },
            grid: { color: 'rgba(255,255,255,0.05)' }
          },
          x: {
            ticks: { color: '#9ca3af', font: { weight: 600 } },
            grid: { color: 'rgba(255,255,255,0.05)' }
          }
        }
      }
    });
  </script>
  <?php endif; ?>

</body>
</html>