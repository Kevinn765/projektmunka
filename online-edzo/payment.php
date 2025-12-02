<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$user_id = $_SESSION['user_id'];
$plan = $_GET['plan'] ?? 'monthly';
$success = false;

// Fizetés feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $card_number = $_POST['card_number'] ?? '';
  $card_name = $_POST['card_name'] ?? '';
  $expiry = $_POST['expiry'] ?? '';
  $cvv = $_POST['cvv'] ?? '';
  
  // Fake validáció - bármit elfogad ha van kitöltve
  if ($card_number && $card_name && $expiry && $cvv) {
    
    // Prémium aktiválás
    $plan_type = $plan === 'yearly' ? 'premium' : 'premium';
    $end_date = $plan === 'yearly' 
      ? date('Y-m-d', strtotime('+1 year')) 
      : date('Y-m-d', strtotime('+1 month'));
    
    // Töröljük a régi előfizetést
    $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Új prémium előfizetés
    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, status, start_date, end_date, payment_method) VALUES (?, ?, 'active', CURDATE(), ?, 'card')");
    $stmt->execute([$user_id, $plan_type, $end_date]);
    
    $success = true;
  }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fizetés - OnlineEdző</title>
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
      padding: 20px;
    }
    
    .payment-container {
      max-width: 500px;
      width: 100%;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5);
      animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .payment-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .payment-header h2 {
      color: #fbbf24;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .plan-info {
      background: rgba(251, 191, 36, 0.1);
      border: 1px solid #fbbf24;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 30px;
      text-align: center;
    }
    
    .plan-info .plan-name {
      font-size: 1.2rem;
      font-weight: 600;
      color: #fbbf24;
      margin-bottom: 5px;
    }
    
    .plan-info .plan-price {
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
    }
    
    .form-label {
      color: #9bbcff;
      font-weight: 500;
      margin-bottom: 8px;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 10px;
      padding: 12px 15px;
      transition: all 0.3s;
    }
    
    .form-control:focus {
      background: rgba(255,255,255,0.12);
      border-color: #fbbf24;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
      outline: none;
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .card-visual {
      background: linear-gradient(135deg, #1e293b, #334155);
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      min-height: 180px;
      position: relative;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .card-chip {
      width: 50px;
      height: 40px;
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      border-radius: 8px;
      margin-bottom: 20px;
    }
    
    .card-number-display {
      font-size: 1.5rem;
      letter-spacing: 3px;
      color: #fff;
      margin-bottom: 20px;
      font-family: 'Courier New', monospace;
    }
    
    .card-details {
      display: flex;
      justify-content: space-between;
    }
    
    .card-holder, .card-expiry {
      color: #9ca3af;
      font-size: 0.9rem;
    }
    
    .btn-pay {
      width: 100%;
      background: linear-gradient(135deg, #fbbf24, #f59e0b);
      color: #000;
      border: none;
      padding: 14px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);
    }
    
    .btn-pay:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, 0.6);
    }
    
    .security-info {
      text-align: center;
      margin-top: 20px;
      color: #9ca3af;
      font-size: 0.85rem;
    }
    
    .security-info i {
      color: #22c55e;
    }
    
    .success-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
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
    
    @keyframes scaleIn {
      from { transform: scale(0.8); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    
    .success-icon {
      font-size: 5rem;
      margin-bottom: 20px;
    }
    
    .success-content h2 {
      color: #22c55e;
      margin-bottom: 15px;
    }
    
    .success-content p {
      color: #d1d5db;
      margin-bottom: 30px;
      font-size: 1.1rem;
    }
    
    .btn-success {
      background: #22c55e;
      color: #fff;
      border: none;
      padding: 12px 30px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: 0.3s;
    }
    
    .btn-success:hover {
      background: #16a34a;
      transform: translateY(-2px);
      color: #fff;
    }
    
    @media (max-width: 768px) {
      .payment-container {
        padding: 30px 20px;
      }
      
      .card-number-display {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

  <?php if ($success): ?>
  <div class="success-modal">
    <div class="success-content">
      <div class="success-icon">🎉</div>
      <h2>Sikeres fizetés!</h2>
      <p>Gratulálunk! Mostantól Prémium tag vagy!<br>Élvezd az összes funkciót korlátlanul!</p>
      <a href="index.php" class="btn-success">Irány a főoldalra! 🚀</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="payment-container">
    <div class="payment-header">
      <h2>💳 Fizetési adatok</h2>
      <p style="color: #9ca3af;">Biztonságos fizetés</p>
    </div>

    <div class="plan-info">
      <div class="plan-name">
        <?= $plan === 'yearly' ? '💎 Prémium Éves' : '⭐ Prémium Havi' ?>
      </div>
      <div class="plan-price">
        <?= $plan === 'yearly' ? '29.990 Ft / év' : '2.990 Ft / hó' ?>
      </div>
      <?php if ($plan !== 'yearly'): ?>
        <p style="color: #22c55e; font-size: 0.9rem; margin-top: 10px;">
          🎁 Első 7 nap ingyen!
        </p>
      <?php endif; ?>
    </div>

    <div class="card-visual">
      <div class="card-chip"></div>
      <div class="card-number-display" id="cardDisplay">
        **** **** **** ****
      </div>
      <div class="card-details">
        <div class="card-holder">
          <small>KÁRTYABIRTOKOS</small>
          <div id="nameDisplay" style="color: #fff; margin-top: 5px;">NÉV</div>
        </div>
        <div class="card-expiry">
          <small>LEJÁRAT</small>
          <div id="expiryDisplay" style="color: #fff; margin-top: 5px;">MM/YY</div>
        </div>
      </div>
    </div>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">💳 Kártyaszám</label>
        <input 
          type="text" 
          name="card_number" 
          class="form-control" 
          placeholder="1234 5678 9012 3456"
          maxlength="19"
          required
          id="cardNumber"
          oninput="formatCardNumber(this); updateCardDisplay()">
      </div>

      <div class="mb-3">
        <label class="form-label">👤 Kártyabirtokos neve</label>
        <input 
          type="text" 
          name="card_name" 
          class="form-control" 
          placeholder="KOVÁCS JÁNOS"
          required
          id="cardName"
          oninput="updateCardDisplay()"
          style="text-transform: uppercase;">
      </div>

      <div class="row g-3 mb-4">
        <div class="col-6">
          <label class="form-label">📅 Lejárat (HH/ÉÉ)</label>
          <input 
            type="text" 
            name="expiry" 
            class="form-control" 
            placeholder="12/25"
            maxlength="5"
            required
            id="cardExpiry"
            oninput="formatExpiry(this); updateCardDisplay()">
        </div>
        <div class="col-6">
          <label class="form-label">🔒 CVV</label>
          <input 
            type="text" 
            name="cvv" 
            class="form-control" 
            placeholder="123"
            maxlength="3"
            required>
        </div>
      </div>

      <button type="submit" class="btn-pay">
        🔒 Biztonságos fizetés - <?= $plan === 'yearly' ? '29.990 Ft' : '2.990 Ft' ?>
      </button>

      <div class="security-info">
        <p>🔒 Biztonságos SSL titkosítás<br>
        ✓ 7 napos pénzvisszafizetési garancia<br>
        ✓ Bármikor lemondható</p>
      </div>

      <div style="text-align: center; margin-top: 20px;">
        <a href="upgrade.php" style="color: #9bbcff; text-decoration: none;">← Vissza a csomagokhoz</a>
      </div>
    </form>
  </div>

  <script>
    function formatCardNumber(input) {
      let value = input.value.replace(/\s/g, '');
      let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
      input.value = formattedValue;
    }

    function formatExpiry(input) {
      let value = input.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
      }
      input.value = value;
    }

    function updateCardDisplay() {
      const cardNumber = document.getElementById('cardNumber').value || '**** **** **** ****';
      const cardName = document.getElementById('cardName').value || 'NÉV';
      const cardExpiry = document.getElementById('cardExpiry').value || 'MM/YY';

      document.getElementById('cardDisplay').textContent = cardNumber;
      document.getElementById('nameDisplay').textContent = cardName.toUpperCase();
      document.getElementById('expiryDisplay').textContent = cardExpiry;
    }
  </script>

</body>
</html>