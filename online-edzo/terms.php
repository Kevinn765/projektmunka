<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Felhasználási Feltételek - OnlineEdző</title>
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
      max-width: 900px;
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
    
    .last-updated {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 50px;
      font-size: 1rem;
    }
    
    .content-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 50px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
    }
    
    .content-card h2 {
      color: #58a6ff;
      font-size: 1.8rem;
      font-weight: 700;
      margin: 40px 0 20px 0;
    }
    
    .content-card h2:first-child {
      margin-top: 0;
    }
    
    .content-card p {
      color: #d1d5db;
      line-height: 1.8;
      margin-bottom: 20px;
      font-size: 1.05rem;
    }
    
    .content-card ul {
      color: #d1d5db;
      line-height: 1.8;
      margin-bottom: 20px;
      padding-left: 25px;
    }
    
    .content-card li {
      margin-bottom: 10px;
      font-size: 1.05rem;
    }
    
    .content-card strong {
      color: #58a6ff;
    }
    
    .highlight-box {
      background: rgba(88, 166, 255, 0.1);
      border-left: 4px solid #58a6ff;
      padding: 20px;
      margin: 25px 0;
      border-radius: 10px;
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
    <h1>📜 Felhasználási Feltételek</h1>
    <p class="last-updated">Utolsó frissítés: 2025. január 26.</p>

    <div class="content-card">
      <h2>1. Általános rendelkezések</h2>
      <p>
        Az OnlineEdző szolgáltatás használatával Ön elfogadja jelen Felhasználási Feltételeket. 
        Kérjük, figyelmesen olvassa el ezeket a feltételeket, mielőtt regisztrál vagy használja szolgáltatásainkat.
      </p>

      <h2>2. A szolgáltatás leírása</h2>
      <p>
        Az OnlineEdző egy online edzésmenedzsment platform, amely a következő funkciókat kínálja:
      </p>
      <ul>
        <li>Személyre szabott edzéstervek generálása</li>
        <li>Edzésnapló vezetése</li>
        <li>Testméret és súly követése</li>
        <li>Gyakorlat adatbázis böngészése</li>
        <li>Edzés timerek és stopperórák</li>
        <li>Prémium előfizetéssel: Táplálkozási napló, AI asszisztens (hamarosan)</li>
      </ul>

      <h2>3. Regisztráció és fiók</h2>
      <p>
        A szolgáltatás használatához regisztráció szükséges. Ön felelős:
      </p>
      <ul>
        <li>A regisztráció során megadott adatok pontosságáért</li>
        <li>Fiókja biztonságáért és jelszavának titokban tartásáért</li>
        <li>A fiókjával végzett összes tevékenységért</li>
      </ul>

      <div class="highlight-box">
        <strong>⚠️ Fontos:</strong> Ha gyanítja, hogy fiókját illetéktelen személy használja, 
        azonnal változtassa meg jelszavát és értesítsen minket.
      </div>

      <h2>4. Felhasználói kötelezettségek</h2>
      <p>A szolgáltatás használata során Ön vállalja, hogy:</p>
      <ul>
        <li>Betartja a hatályos magyar jogszabályokat</li>
        <li>Nem használja a szolgáltatást jogellenes célokra</li>
        <li>Nem osztja meg fiókját más személyekkel</li>
        <li>Nem próbálja meg megkerülni a biztonsági intézkedéseket</li>
        <li>Tiszteletben tartja más felhasználók jogait</li>
      </ul>

      <h2>5. Szellemi tulajdon</h2>
      <p>
        Az OnlineEdző weboldal tartalma, dizájnja, logója és szoftvere szerzői jogi védelem alatt áll. 
        A tartalom másolása, terjesztése vagy módosítása csak előzetes írásbeli engedéllyel lehetséges.
      </p>

      <h2>6. Prémium előfizetés</h2>
      <p>
        A prémium előfizetés havi vagy éves díj ellenében további funkciókat biztosít:
      </p>
      <ul>
        <li><strong>Díjszabás:</strong> 2.990 Ft/hó vagy 29.990 Ft/év</li>
        <li><strong>Ingyenes próbaidőszak:</strong> 7 nap (csak új felhasználóknak)</li>
        <li><strong>Lemondás:</strong> Bármikor lemondható, a már kifizetett időszak végéig használható</li>
        <li><strong>Visszatérítés:</strong> 7 napos pénzvisszafizetési garancia</li>
      </ul>

      <h2>7. Felelősség korlátozása</h2>
      
      <div class="highlight-box">
        <strong>⚠️ Fontos egészségügyi figyelmeztetés:</strong> 
        Az OnlineEdző szolgáltatás NEM helyettesíti az orvosi tanácsadást, diagnózist vagy kezelést. 
        Új edzésprogram kezdése előtt mindig konzultáljon orvosával!
      </div>

      <p>Az OnlineEdző nem vállal felelősséget:</p>
      <ul>
        <li>A szolgáltatás használata során bekövetkező sérülésekért</li>
        <li>Egészségügyi problémákért, amelyek az edzésprogramok követéséből erednek</li>
        <li>A szolgáltatás esetleges megszakadásából vagy hibájából eredő károkért</li>
        <li>Harmadik felek által okozott károkért</li>
      </ul>

      <h2>8. Szolgáltatás módosítása és megszüntetése</h2>
      <p>
        Fenntartjuk a jogot, hogy:
      </p>
      <ul>
        <li>Bármikor módosítsuk vagy megszüntessük a szolgáltatást</li>
        <li>Előzetes értesítés nélkül frissítsük a funkciókat</li>
        <li>Felfüggesszük vagy töröljük a feltételeket megszegő fiókokat</li>
      </ul>

      <h2>9. Adatvédelem</h2>
      <p>
        Az Ön adatainak kezelésére vonatkozó információkat az 
        <a href="privacy.php" style="color: #58a6ff; text-decoration: underline;">Adatvédelmi Szabályzatban</a> 
        találja meg.
      </p>

      <h2>10. A feltételek módosítása</h2>
      <p>
        Fenntartjuk a jogot, hogy jelen feltételeket bármikor módosítsuk. 
        A módosításokról e-mailben értesítjük felhasználóinkat. 
        A módosítások a közzétételt követő 15 napon belül lépnek hatályba.
      </p>

      <h2>11. Irányadó jog és jogviták</h2>
      <p>
        A jelen feltételekre a magyar jog az irányadó. 
        A szolgáltatással kapcsolatos vitás kérdések rendezésére a magyar bíróságok illetékesek.
      </p>

      <h2>12. Kapcsolat</h2>
      <p>
        Ha kérdése van a Felhasználási Feltételekkel kapcsolatban, keressen minket:
      </p>
      <ul>
        <li><strong>Email:</strong> info@onlineedzo.hu</li>
        <li><strong>Weboldal:</strong> <a href="index.php" style="color: #58a6ff;">www.onlineedzo.hu</a></li>
      </ul>

      <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid rgba(88, 166, 255, 0.2); text-align: center; color: #9ca3af;">
        <p>A szolgáltatás használatával Ön elismeri, hogy elolvasta és megértette jelen Felhasználási Feltételeket, 
        és vállalja azok betartását.</p>
        <p style="margin-top: 15px;"><strong>© 2025 OnlineEdző. Minden jog fenntartva.</strong></p>
      </div>
    </div>
  </div>

</body>
</html>