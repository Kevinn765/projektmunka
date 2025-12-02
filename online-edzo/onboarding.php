<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Ellenőrizzük, hogy már kitöltötte-e
$stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if ($profile && $profile['onboarding_completed']) {
  // Már kitöltötte, irány a főoldal
  header('Location: index.php');
  exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $age = (int)($_POST['age'] ?? 0);
  $gender = $_POST['gender'] ?? '';
  $height = (int)($_POST['height'] ?? 0);
  $weight = (float)($_POST['weight'] ?? 0);
  $goal = $_POST['goal'] ?? '';
  $level = $_POST['level'] ?? '';
  $sessions = (int)($_POST['sessions'] ?? 3);
  $restrictions = $_POST['restrictions'] ?? '';

  // Validáció
  if ($age < 14 || $age > 100) $errors[] = 'Kérlek adj meg érvényes életkort (14-100 év)!';
  if (!in_array($gender, ['férfi', 'nő', 'egyéb'])) $errors[] = 'Válassz nemet!';
  if ($height < 100 || $height > 250) $errors[] = 'Kérlek adj meg érvényes magasságot (100-250 cm)!';
  if ($weight < 30 || $weight > 300) $errors[] = 'Kérlek adj meg érvényes testsúlyt (30-300 kg)!';
  if (!in_array($goal, ['fogyás', 'izomnövelés', 'erősödés', 'állóképesség'])) $errors[] = 'Válassz célt!';
  if (!in_array($level, ['kezdő', 'középhaladó', 'haladó'])) $errors[] = 'Válassz edzettségi szintet!';
  if ($sessions < 2 || $sessions > 7) $errors[] = 'Heti edzések: 2-7 között!';

  if (empty($errors)) {
    if ($profile) {
      // Frissítés
      $stmt = $pdo->prepare("UPDATE user_profiles SET age=?, gender=?, height=?, current_weight=?, goal=?, fitness_level=?, weekly_sessions=?, restrictions=?, onboarding_completed=1 WHERE user_id=?");
      $stmt->execute([$age, $gender, $height, $weight, $goal, $level, $sessions, $restrictions, $user_id]);
    } else {
      // Új létrehozás
      $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, age, gender, height, current_weight, goal, fitness_level, weekly_sessions, restrictions, onboarding_completed) VALUES (?,?,?,?,?,?,?,?,?,1)");
      $stmt->execute([$user_id, $age, $gender, $height, $weight, $goal, $level, $sessions, $restrictions]);
    }
    $success = true;
  }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil beállítása - OnlineEdző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
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
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }
    
    .onboarding-container {
      max-width: 700px;
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 50px 40px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5);
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .onboarding-header {
      text-align: center;
      margin-bottom: 40px;
    }
    
    .onboarding-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
    }
    
    .onboarding-header p {
      color: #9ca3af;
      font-size: 1.1rem;
    }
    
    .progress-bar-custom {
      height: 8px;
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      margin-bottom: 40px;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #58a6ff, #238636);
      border-radius: 10px;
      transition: width 0.3s;
    }
    
    .form-step {
      display: none;
      animation: slideIn 0.4s ease;
    }
    
    .form-step.active {
      display: block;
    }
    
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(20px); }
      to { opacity: 1; transform: translateX(0); }
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
      border-radius: 10px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      background: rgba(255,255,255,0.12);
      border-color: #58a6ff;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
      outline: none;
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .option-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }
    
    .option-card {
      background: rgba(255,255,255,0.05);
      border: 2px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .option-card:hover {
      border-color: #58a6ff;
      background: rgba(88, 166, 255, 0.1);
      transform: translateY(-3px);
    }
    
    .option-card.selected {
      border-color: #238636;
      background: rgba(35, 134, 54, 0.2);
    }
    
    .option-card input[type="radio"] {
      display: none;
    }
    
    .option-card .icon {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    
    .option-card .label {
      color: #fff;
      font-weight: 600;
    }
    
    .btn-nav {
      padding: 12px 30px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 1rem;
    }
    
    .btn-next {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
    }
    
    .btn-next:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(35, 134, 54, 0.4);
    }
    
    .btn-prev {
      background: rgba(255,255,255,0.1);
      color: #9ca3af;
    }
    
    .btn-prev:hover {
      background: rgba(255,255,255,0.15);
    }
    
    .btn-submit {
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: #000;
      width: 100%;
      padding: 14px;
      font-size: 1.1rem;
    }
    
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, 0.5);
    }
    
    .error-message {
      background: rgba(239, 68, 68, 0.2);
      border: 1px solid #ef4444;
      color: #ff6b6b;
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    
    .success-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }
    
    .success-content {
      background: rgba(255,255,255,0.05);
      border: 2px solid #22c55e;
      border-radius: 20px;
      padding: 50px;
      text-align: center;
      max-width: 500px;
      animation: scaleIn 0.5s ease;
    }
    
    .success-content h2 {
      color: #22c55e;
      margin-bottom: 15px;
    }
    
    .success-content p {
      color: #d1d5db;
      margin-bottom: 30px;
    }
    
    @media (max-width: 768px) {
      .onboarding-container {
        padding: 30px 20px;
      }
      
      .onboarding-header h1 {
        font-size: 2rem;
      }
      
      .option-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <?php if ($success): ?>
  <div class="success-modal">
    <div class="success-content">
      <div style="font-size: 5rem; margin-bottom: 20px;">🎉</div>
      <h2>Profil sikeresen beállítva!</h2>
      <p>Most már személyre szabott edzéstervet generálhatsz!</p>
      <button onclick="window.location.href='generate_plan.php'" class="btn-nav btn-submit">
        Edzésterv generálása 🚀
      </button>
    </div>
  </div>
  <?php endif; ?>

  <div class="onboarding-container">
    <div class="onboarding-header">
      <h1>👋 Üdvözlünk!</h1>
      <p>Állítsuk be a profilod, hogy a legjobb edzéstervet kapd</p>
    </div>

    <div class="progress-bar-custom">
      <div class="progress-fill" id="progressBar" style="width: 20%;"></div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <strong>⚠️ Hibák:</strong>
        <ul style="margin: 10px 0 0 20px;">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" id="onboardingForm">
      
      <!-- 1. lépés: Alapadatok -->
      <div class="form-step active" data-step="1">
        <h3 style="color: #58a6ff; margin-bottom: 25px;">📋 Alapadatok</h3>
        
        <div class="mb-3">
          <label class="form-label">🎂 Életkor (év)</label>
          <input type="number" name="age" class="form-control" placeholder="pl. 25" min="14" max="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label">⚧️ Nemed</label>
          <div class="option-grid">
            <label class="option-card">
              <input type="radio" name="gender" value="férfi" required>
              <div class="icon">♂️</div>
              <div class="label">Férfi</div>
            </label>
            <label class="option-card">
              <input type="radio" name="gender" value="nő">
              <div class="icon">♀️</div>
              <div class="label">Nő</div>
            </label>
            <label class="option-card">
              <input type="radio" name="gender" value="egyéb">
              <div class="icon">⚧</div>
              <div class="label">Egyéb</div>
            </label>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">📏 Magasság (cm)</label>
            <input type="number" name="height" class="form-control" placeholder="pl. 175" min="100" max="250" required>
          </div>
          <div class="col-6">
            <label class="form-label">⚖️ Testsúly (kg)</label>
            <input type="number" step="0.1" name="weight" class="form-control" placeholder="pl. 75.5" min="30" max="300" required>
          </div>
        </div>

        <div class="text-end mt-4">
          <button type="button" class="btn-nav btn-next" onclick="nextStep()">Tovább →</button>
        </div>
      </div>

      <!-- 2. lépés: Célok -->
      <div class="form-step" data-step="2">
        <h3 style="color: #58a6ff; margin-bottom: 25px;">🎯 Mi a célod?</h3>
        
        <div class="option-grid">
          <label class="option-card">
            <input type="radio" name="goal" value="fogyás" required>
            <div class="icon">🔥</div>
            <div class="label">Fogyás</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">Zsírégető edzések</small>
          </label>
          <label class="option-card">
            <input type="radio" name="goal" value="izomnövelés">
            <div class="icon">💪</div>
            <div class="label">Izomnövelés</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">Tömegnövelő program</small>
          </label>
          <label class="option-card">
            <input type="radio" name="goal" value="erősödés">
            <div class="icon">🏋️</div>
            <div class="label">Erősödés</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">Erőnövelő terv</small>
          </label>
          <label class="option-card">
            <input type="radio" name="goal" value="állóképesség">
            <div class="icon">🏃</div>
            <div class="label">Állóképesség</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">Kardió és fitness</small>
          </label>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn-nav btn-prev" onclick="prevStep()">← Vissza</button>
          <button type="button" class="btn-nav btn-next" onclick="nextStep()">Tovább →</button>
        </div>
      </div>

      <!-- 3. lépés: Edzettségi szint -->
      <div class="form-step" data-step="3">
        <h3 style="color: #58a6ff; margin-bottom: 25px;">📈 Edzettségi szinted</h3>
        
        <div class="option-grid">
          <label class="option-card">
            <input type="radio" name="level" value="kezdő" required>
            <div class="icon">🌱</div>
            <div class="label">Kezdő</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">0-6 hónap tapasztalat</small>
          </label>
          <label class="option-card">
            <input type="radio" name="level" value="középhaladó">
            <div class="icon">🌿</div>
            <div class="label">Középhaladó</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">6-24 hónap</small>
          </label>
          <label class="option-card">
            <input type="radio" name="level" value="haladó">
            <div class="icon">🌳</div>
            <div class="label">Haladó</div>
            <small style="color: #9ca3af; display: block; margin-top: 8px;">2+ év tapasztalat</small>
          </label>
        </div>

        <div class="mt-4">
          <label class="form-label">📅 Heti edzések száma</label>
          <input type="number" name="sessions" class="form-control" value="3" min="2" max="7" required>
          <small style="color: #9ca3af; display: block; margin-top: 8px;">Ajánlott: 3-5 edzés hetente</small>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn-nav btn-prev" onclick="prevStep()">← Vissza</button>
          <button type="button" class="btn-nav btn-next" onclick="nextStep()">Tovább →</button>
        </div>
      </div>

      <!-- 4. lépés: Korlátozások -->
      <div class="form-step" data-step="4">
        <h3 style="color: #58a6ff; margin-bottom: 25px;">⚕️ Van valamilyen korlátozásod?</h3>
        
        <div class="mb-3">
          <label class="form-label">📝 Sérülések, egészségügyi problémák</label>
          <textarea name="restrictions" class="form-control" rows="4" placeholder="pl. Térdprobléma, hátfájás, asztma... (opcionális)"></textarea>
          <small style="color: #9ca3af; display: block; margin-top: 8px;">Ezeket figyelembe vesszük az edzésterv generálásakor</small>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="button" class="btn-nav btn-prev" onclick="prevStep()">← Vissza</button>
          <button type="submit" class="btn-nav btn-submit">Mentés és Indulás! 🚀</button>
        </div>
      </div>

    </form>
  </div>

  <script>
    let currentStep = 1;
    const totalSteps = 4;

    function updateProgress() {
      const progress = (currentStep / totalSteps) * 100;
      document.getElementById('progressBar').style.width = progress + '%';
    }

    function nextStep() {
      if (currentStep < totalSteps) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        currentStep++;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
        window.scrollTo(0, 0);
      }
    }

    function prevStep() {
      if (currentStep > 1) {
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        currentStep--;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        updateProgress();
        window.scrollTo(0, 0);
      }
    }

    // Option card selection
    document.querySelectorAll('.option-card').forEach(card => {
      card.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        const name = radio.getAttribute('name');
        
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
          r.closest('.option-card').classList.remove('selected');
        });
        
        radio.checked = true;
        this.classList.add('selected');
      });
    });

    updateProgress();
  </script>

</body>
</html>