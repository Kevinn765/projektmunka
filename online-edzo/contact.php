<?php
session_start();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $msg = trim($_POST['message'] ?? '');
  
  if (empty($name) || empty($email) || empty($subject) || empty($msg)) {
    $error = 'Kérjük, töltsd ki az összes mezőt!';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Érvénytelen email cím!';
  } else {
    // Itt küldhetsz emailt vagy mentheted adatbázisba
    // mail('info@onlineedzo.hu', $subject, $msg, "From: $email");
    
    $message = 'Üzeneted sikeresen elküldve! Hamarosan válaszolunk.';
  }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kapcsolat - OnlineEdző</title>
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
      max-width: 800px;
      margin: 60px auto;
      padding: 0 20px 80px;
      position: relative;
      z-index: 1;
    }
    
    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 3.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 50px;
      font-size: 1.1rem;
    }
    
    .content-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 50px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
    }
    
    .contact-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 30px;
      margin-bottom: 50px;
    }
    
    .info-card {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.2);
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      transition: 0.3s;
    }
    
    .info-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(88, 166, 255, 0.3);
    }
    
    .info-icon {
      font-size: 2.5rem;
      margin-bottom: 15px;
    }
    
    .info-label {
      color: #9bbcff;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 8px;
    }
    
    .info-value {
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
    }
    
    .info-value a {
      color: #58a6ff;
      text-decoration: none;
    }
    
    .info-value a:hover {
      text-decoration: underline;
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
      padding: 14px 40px;
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
    
    @media (max-width: 768px) {
      nav {
        padding: 15px 20px;
      }
      
      .logo {
        font-size: 1.4rem;
      }
      
      h1 {
        font-size: 2.5rem;
      }
      
      .content-card {
        padding: 30px 25px;
      }
      
      .contact-info {
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
    </div>
  </nav>

  <div class="container">
    <h1>📧 Kapcsolat</h1>
    <p class="subtitle">Van kérdésed? Keress minket bizalommal!</p>

    <div class="content-card">
      
      <!-- Kapcsolati információk -->
      <div class="contact-info">
        <div class="info-card">
          <div class="info-icon">📧</div>
          <div class="info-label">Email</div>
          <div class="info-value">
            <a href="mailto:info@onlineedzo.hu">info@onlineedzo.hu</a>
          </div>
        </div>
        
        <div class="info-card">
          <div class="info-icon">⏰</div>
          <div class="info-label">Válaszidő</div>
          <div class="info-value">
            24-48 óra
          </div>
        </div>
        
        <div class="info-card">
          <div class="info-icon">🌐</div>
          <div class="info-label">Weboldal</div>
          <div class="info-value">
            <a href="index.php">onlineedzo.hu</a>
          </div>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert-success">✅ <?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert-danger">❌ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Kapcsolatfelvételi űrlap -->
      <h3 style="color: #58a6ff; margin-bottom: 25px; font-size: 1.6rem; font-weight: 700;">
        ✉️ Üzenet küldése
      </h3>
      
      <form method="post">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">👤 Neved</label>
            <input type="text" name="name" class="form-control" placeholder="pl. Kiss János" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">📧 Email címed</label>
            <input type="email" name="email" class="form-control" placeholder="pelda@email.hu" required>
          </div>
          
          <div class="col-12">
            <label class="form-label">📝 Tárgy</label>
            <select name="subject" class="form-select" required>
              <option value="">Válassz...</option>
              <option value="Általános kérdés">Általános kérdés</option>
              <option value="Technikai probléma">Technikai probléma</option>
              <option value="Prémium előfizetés">Prémium előfizetés</option>
              <option value="Fiók probléma">Fiók probléma</option>
              <option value="Visszajelzés">Visszajelzés / Javaslat</option>
              <option value="Egyéb">Egyéb</option>
            </select>
          </div>
          
          <div class="col-12">
            <label class="form-label">💬 Üzenet</label>
            <textarea name="message" class="form-control" rows="6" placeholder="Írd le részletesen a kérdésedet vagy problémádat..." required></textarea>
          </div>
          
          <div class="col-12">
            <button type="submit" class="btn-main">📤 Üzenet küldése</button>
          </div>
        </div>
      </form>

      <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(88, 166, 255, 0.2);">
        <h4 style="color: #58a6ff; margin-bottom: 15px; font-size: 1.3rem;">💡 Gyakran Ismételt Kérdések (GYIK)</h4>
        <div style="color: #d1d5db; line-height: 1.8;">
          <p><strong>Mennyi idő alatt válaszoltok?</strong><br>
          Általában 24-48 órán belül válaszolunk minden megkeresésre.</p>
          
          <p><strong>Hogyan törölhetem a fiókomat?</strong><br>
          Bejelentkezés után a Beállítások menüpontban találod a "Fiók törlése" opciót.</p>
          
          <p><strong>Hogyan mondhatom le a prémium előfizetésemet?</strong><br>
          Bármikor lemondható a Beállítások > Előfizetés menüpontban.</p>
          
          <p><strong>Milyen fizetési módokat fogadtok el?</strong><br>
          Bankkártya (Visa, Mastercard), PayPal és átutalás.</p>
        </div>
      </div>

      <div style="margin-top: 30px; text-align: center; color: #9ca3af;">
        <p>További információ:</p>
        <p style="margin-top: 10px;">
          <a href="privacy.php" style="color: #58a6ff; text-decoration: none;">Adatvédelem</a> · 
          <a href="terms.php" style="color: #58a6ff; text-decoration: none;">Felhasználási feltételek</a>
        </p>
      </div>
    </div>
  </div>

</body>
</html>