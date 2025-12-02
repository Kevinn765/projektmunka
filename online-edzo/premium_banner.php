<?php
// Ezt includolhatod bárhova: include 'premium_banner.php';
if (!isset($_SESSION['user_id'])) return;

include_once 'db.php';
include_once 'premium_check.php';

if (isPremium($_SESSION['user_id'])) return; // Ha már prémium, ne mutassuk
?>

<style>
.premium-banner {
  background: linear-gradient(135deg, #fbbf24, #f59e0b, #ea580c);
  border-radius: 16px;
  padding: 25px;
  margin: 20px 0;
  box-shadow: 0 8px 30px rgba(251, 191, 36, 0.3);
  position: relative;
  overflow: hidden;
  animation: fadeIn 0.6s ease;
}

.premium-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: pulse 3s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 0.3; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.premium-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.premium-text h3 {
  color: #fff;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 8px 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.premium-text p {
  color: rgba(255,255,255,0.95);
  margin: 0;
  font-size: 1rem;
}

.premium-features {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  margin-top: 15px;
}

.premium-feature {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #fff;
  font-size: 0.9rem;
  font-weight: 500;
}

.premium-feature::before {
  content: '✓';
  background: rgba(255,255,255,0.3);
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.btn-upgrade {
  background: #fff;
  color: #ea580c;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  text-decoration: none;
  display: inline-block;
}

.btn-upgrade:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  color: #ea580c;
}

.close-banner {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255,255,255,0.2);
  border: none;
  color: #fff;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
  transition: 0.2s;
  z-index: 2;
}

.close-banner:hover {
  background: rgba(255,255,255,0.3);
}

@media (max-width: 768px) {
  .premium-content {
    flex-direction: column;
    text-align: center;
  }
  
  .premium-features {
    justify-content: center;
  }
  
  .premium-text h3 {
    font-size: 1.3rem;
  }
}
</style>

<div class="premium-banner" id="premium-banner">
  <button class="close-banner" onclick="document.getElementById('premium-banner').style.display='none'">×</button>
  
  <div class="premium-content">
    <div class="premium-text">
      <h3>⭐ Fejleszd Premium-ra!</h3>
      <p>Szerezd meg a teljes hozzáférést minden funkcióhoz</p>
      
      <div class="premium-features">
        <div class="premium-feature">🤖 AI Edző</div>
        <div class="premium-feature">📊 Statisztikák</div>
        <div class="premium-feature">🍎 Táplálkozás</div>
        <div class="premium-feature">🏆 Kihívások</div>
      </div>
    </div>
    
    <div>
      <a href="upgrade.php" class="btn-upgrade">
        Próbáld ki ingyen 7 napig! 🚀
      </a>
      <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; margin-top: 8px; text-align: center;">
        Csak 2.990 Ft/hó ezután
      </p>
    </div>
  </div>
</div>