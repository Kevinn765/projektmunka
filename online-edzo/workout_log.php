<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$uid = $_SESSION['user_id'];

// Új edzés mentése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = $_POST['date'] ?? date('Y-m-d');
  $muscle = $_POST['muscle'] ?? '';
  $exercise = $_POST['exercise'] ?? '';
  $sets = $_POST['sets'] ?? 0;
  $reps = $_POST['reps'] ?? 0;
  $weight = $_POST['weight'] ?? 0;
  $note = $_POST['note'] ?? '';

  $stmt = $pdo->prepare("INSERT INTO workout_log (user_id, date, muscle_group, exercise, sets, reps, weight, note) VALUES (?,?,?,?,?,?,?,?)");
  $stmt->execute([$uid, $date, $muscle, $exercise, $sets, $reps, $weight, $note]);
}

// Napló bejegyzések lekérése
$stmt = $pdo->prepare("SELECT * FROM workout_log WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$uid]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edzésnapló</title>
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
    
    .table-responsive {
      border-radius: 18px;
      overflow: hidden;
      margin-top: 25px;
    }
    
    table {
      width: 100%;
      background: rgba(0,0,0,0.3);
      border-collapse: collapse;
    }
    
    th {
      background: rgba(88, 166, 255, 0.15);
      color: #58a6ff;
      padding: 18px;
      font-weight: 700;
      text-align: left;
      border: none;
      font-size: 1rem;
    }
    
    td {
      padding: 18px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      color: #d1d5db;
      font-size: 1rem;
    }
    
    tr:hover {
      background: rgba(255,255,255,0.05);
    }
    
    tr:last-child td {
      border-bottom: none;
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
      
      .card {
        padding: 30px 20px;
      }
      
      table {
        font-size: 0.9rem;
      }
      
      th, td {
        padding: 12px 10px;
      }
    }
    
    @media (max-width: 480px) {
      h2 {
        font-size: 2rem;
      }
      
      .table-responsive {
        overflow-x: auto;
      }
      
      table {
        min-width: 600px;
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
    <h2>📖 Edzésnapló</h2>

    <div class="card">
      <h3>➕ Új edzés rögzítése</h3>
      <form method="post">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">📅 Dátum</label>
            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">💪 Izomcsoport</label>
            <select name="muscle" class="form-select">
              <option>Mell</option>
              <option>Hát</option>
              <option>Láb</option>
              <option>Váll</option>
              <option>Bicepsz</option>
              <option>Tricepsz</option>
              <option>Has</option>
              <option>Vádli</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">🏋️ Gyakorlat</label>
            <input type="text" name="exercise" class="form-control" placeholder="pl. Fekvenyomás">
          </div>
          <div class="col-md-4">
            <label class="form-label">🔢 Sorozatok</label>
            <input type="number" name="sets" class="form-control" min="1" placeholder="pl. 4">
          </div>
          <div class="col-md-4">
            <label class="form-label">🔁 Ismétlések</label>
            <input type="number" name="reps" class="form-control" min="1" placeholder="pl. 12">
          </div>
          <div class="col-md-4">
            <label class="form-label">⚖️ Súly (kg)</label>
            <input type="number" name="weight" class="form-control" min="0" step="0.5" placeholder="pl. 60">
          </div>
          <div class="col-12">
            <label class="form-label">📝 Megjegyzés</label>
            <textarea name="note" class="form-control" rows="2" placeholder="pl. Utolsó sorozat nehéz volt, de jó formával"></textarea>
          </div>
        </div>
        <div class="mt-4 text-end">
          <button type="submit" class="btn-main">💾 Mentés</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h3>📋 Korábbi edzéseid</h3>
      <?php if (empty($logs)): ?>
        <div class="no-data">
          <h4>🤷‍♂️ Még nincs rögzített edzésed.</h4>
          <p>Töltsd ki a fenti űrlapot az első bejegyzésedhez!</p>
        </div>
      <?php else: ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>📅 Dátum</th>
              <th>💪 Izomcsoport</th>
              <th>🏋️ Gyakorlat</th>
              <th>🔢 Sorozat × Ism.</th>
              <th>⚖️ Súly (kg)</th>
              <th>📝 Megjegyzés</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
              <td><?= htmlspecialchars($log['date']) ?></td>
              <td><?= htmlspecialchars($log['muscle_group']) ?></td>
              <td><?= htmlspecialchars($log['exercise']) ?></td>
              <td><?= $log['sets'] ?>×<?= $log['reps'] ?></td>
              <td><?= $log['weight'] ?></td>
              <td><?= htmlspecialchars($log['note']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>