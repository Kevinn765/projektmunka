<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$uid = $_SESSION['user_id'];
$message = '';

// Új mérés mentése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_measurement'])) {
  $date = $_POST['date'] ?? date('Y-m-d');
  $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
  $body_fat = !empty($_POST['body_fat']) ? $_POST['body_fat'] : null;
  $chest = !empty($_POST['chest']) ? $_POST['chest'] : null;
  $waist = !empty($_POST['waist']) ? $_POST['waist'] : null;
  $notes = $_POST['notes'] ?? '';

  $stmt = $pdo->prepare("INSERT INTO body_measurements (user_id, measurement_date, weight, body_fat_percentage, chest, waist, notes) VALUES (?,?,?,?,?,?,?)");
  $stmt->execute([$uid, $date, $weight, $body_fat, $chest, $waist, $notes]);
  $message = '✅ Mérés sikeresen mentve!';
}

// Fotó feltöltés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo']) && isset($_FILES['photo'])) {
  $photo_date = $_POST['photo_date'] ?? date('Y-m-d');
  $description = $_POST['description'] ?? '';
  
  $upload_dir = 'uploads/progress_photos/';
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }
  
  $file = $_FILES['photo'];
  $allowed = ['jpg', 'jpeg', 'png', 'gif'];
  $filename = $file['name'];
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  
  if (in_array($ext, $allowed) && $file['size'] < 5000000) {
    $new_filename = $uid . '_' . time() . '.' . $ext;
    $target = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
      $stmt = $pdo->prepare("INSERT INTO progress_photos (user_id, photo_date, photo_path, description) VALUES (?,?,?,?)");
      $stmt->execute([$uid, $photo_date, $target, $description]);
      $message = '✅ Fotó sikeresen feltöltve!';
    }
  } else {
    $message = '❌ Csak jpg, png, gif fájlokat tölthetsz fel (max 5MB)!';
  }
}

// Mérések lekérése
$stmt = $pdo->prepare("SELECT * FROM body_measurements WHERE user_id = ? ORDER BY measurement_date DESC");
$stmt->execute([$uid]);
$measurements = $stmt->fetchAll();

// Fotók lekérése
$stmt = $pdo->prepare("SELECT * FROM progress_photos WHERE user_id = ? ORDER BY photo_date DESC");
$stmt->execute([$uid]);
$photos = $stmt->fetchAll();

// Grafikon adatok
$stmt = $pdo->prepare("SELECT measurement_date, weight FROM body_measurements WHERE user_id = ? AND weight IS NOT NULL ORDER BY measurement_date ASC LIMIT 30");
$stmt->execute([$uid]);
$chart_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Testméret Követés</title>
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
    
    .stats-grid {
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
      font-weight: 600;
    }
    
    .stat-change {
      color: #22c55e;
      font-size: 1.1rem;
      margin-top: 8px;
      font-weight: 600;
    }
    
    .measurement-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }
    
    .photo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 25px;
      margin-top: 25px;
    }
    
    .photo-card {
      background: rgba(0,0,0,0.4);
      border-radius: 18px;
      overflow: hidden;
      border: 1px solid rgba(88, 166, 255, 0.1);
      transition: 0.3s;
    }
    
    .photo-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.5);
      border-color: #58a6ff;
    }
    
    .photo-card img {
      width: 100%;
      height: 280px;
      object-fit: cover;
    }
    
    .photo-info {
      padding: 18px;
    }
    
    .photo-info .date {
      color: #58a6ff;
      font-weight: 700;
      margin-bottom: 6px;
      font-size: 1.05rem;
    }
    
    .photo-info .desc {
      color: #9ca3af;
      font-size: 0.95rem;
    }
    
    .chart-container {
      position: relative;
      height: 350px;
      margin-top: 25px;
    }
    
    table {
      width: 100%;
      margin-top: 20px;
    }
    
    th {
      background: rgba(88, 166, 255, 0.15);
      color: #58a6ff;
      padding: 16px;
      font-weight: 700;
      text-align: left;
    }
    
    td {
      padding: 16px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      color: #d1d5db;
    }
    
    tr:hover {
      background: rgba(255,255,255,0.05);
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
      
      .measurement-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .photo-grid {
        grid-template-columns: 1fr;
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
    <h2>📏 Testméret Követés</h2>

    <?php if ($message): ?>
      <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Statisztikák -->
    <?php if (!empty($measurements)): 
      $latest = $measurements[0];
      $previous = $measurements[1] ?? null;
    ?>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Aktuális testsúly</div>
        <div class="stat-value"><?= $latest['weight'] ? $latest['weight'] . ' kg' : '—' ?></div>
        <?php if ($previous && $latest['weight'] && $previous['weight']): 
          $diff = $latest['weight'] - $previous['weight'];
        ?>
          <div class="stat-change" style="color: <?= $diff < 0 ? '#22c55e' : '#ef4444' ?>">
            <?= $diff > 0 ? '+' : '' ?><?= number_format($diff, 1) ?> kg
          </div>
        <?php endif; ?>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">Testzsír %</div>
        <div class="stat-value"><?= $latest['body_fat_percentage'] ? $latest['body_fat_percentage'] . '%' : '—' ?></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">Derék</div>
        <div class="stat-value"><?= $latest['waist'] ? $latest['waist'] . ' cm' : '—' ?></div>
      </div>
      
      <div class="stat-card">
        <div class="stat-label">Mérések száma</div>
        <div class="stat-value"><?= count($measurements) ?></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Grafikon -->
    <?php if (!empty($chart_data) && count($chart_data) >= 2): ?>
    <div class="card">
      <h3>📊 Testsúly változás</h3>
      <div class="chart-container">
        <canvas id="weightChart"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <!-- Új mérés -->
    <div class="card">
      <h3>➕ Új mérés rögzítése</h3>
      <form method="post">
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">📅 Dátum</label>
            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        
        <div class="measurement-grid">
          <div>
            <label class="form-label">⚖️ Testsúly (kg)</label>
            <input type="number" step="0.1" name="weight" class="form-control" placeholder="75.5">
          </div>
          <div>
            <label class="form-label">📊 Testzsír %</label>
            <input type="number" step="0.1" name="body_fat" class="form-control" placeholder="18.5">
          </div>
          <div>
            <label class="form-label">💪 Mell (cm)</label>
            <input type="number" step="0.1" name="chest" class="form-control" placeholder="95">
          </div>
          <div>
            <label class="form-label">⭕ Derék (cm)</label>
            <input type="number" step="0.1" name="waist" class="form-control" placeholder="80">
          </div>
        </div>
        
        <div class="mb-3 mt-3">
          <label class="form-label">📝 Megjegyzés</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="pl. Edzés előtt mérve"></textarea>
        </div>
        
        <div class="text-end">
          <button type="submit" name="save_measurement" class="btn btn-main">💾 Mentés</button>
        </div>
      </form>
    </div>

    <!-- Fotó feltöltés -->
    <div class="card">
      <h3>📸 Progress fotó feltöltése</h3>
      <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">📅 Dátum</label>
            <input type="date" name="photo_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">📝 Leírás</label>
            <input type="text" name="description" class="form-control" placeholder="pl. Előlnézet, 3 hónap után">
          </div>
          <div class="col-12">
            <label class="form-label">🖼️ Válassz fotót (max 5MB)</label>
            <input type="file" name="photo" class="form-control" accept="image/*" required>
          </div>
        </div>
        <div class="text-end mt-3">
          <button type="submit" name="upload_photo" class="btn btn-main">📤 Feltöltés</button>
        </div>
      </form>
    </div>

    <!-- Progress fotók -->
    <?php if (!empty($photos)): ?>
    <div class="card">
      <h3>🖼️ Progress fotók</h3>
      <div class="photo-grid">
        <?php foreach ($photos as $photo): ?>
          <div class="photo-card">
            <img src="<?= htmlspecialchars($photo['photo_path']) ?>" alt="Progress" loading="lazy">
            <div class="photo-info">
              <div class="date">📅 <?= htmlspecialchars($photo['photo_date']) ?></div>
              <div class="desc"><?= htmlspecialchars($photo['description']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Mérések története -->
    <?php if (!empty($measurements)): ?>
    <div class="card">
      <h3>📋 Mérések története</h3>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Dátum</th>
              <th>Súly (kg)</th>
              <th>Testzsír %</th>
              <th>Mell</th>
              <th>Derék</th>
              <th>Megjegyzés</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($measurements as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['measurement_date']) ?></td>
              <td><?= $m['weight'] ? $m['weight'] : '—' ?></td>
              <td><?= $m['body_fat_percentage'] ? $m['body_fat_percentage'] . '%' : '—' ?></td>
              <td><?= $m['chest'] ? $m['chest'] : '—' ?></td>
              <td><?= $m['waist'] ? $m['waist'] : '—' ?></td>
              <td><?= htmlspecialchars($m['notes']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($chart_data) && count($chart_data) >= 2): ?>
  <script>
    const ctx = document.getElementById('weightChart').getContext('2d');
    const chartData = <?= json_encode($chart_data) ?>;
    
    const labels = chartData.map(d => d.measurement_date);
    const weights = chartData.map(d => parseFloat(d.weight));
    
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Testsúly (kg)',
          data: weights,
          borderColor: '#58a6ff',
          backgroundColor: 'rgba(88, 166, 255, 0.1)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 6,
          pointBackgroundColor: '#58a6ff',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: '#e6edf3',
              font: { size: 14, weight: 600 }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: { color: '#9ca3af', font: { weight: 600 } },
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