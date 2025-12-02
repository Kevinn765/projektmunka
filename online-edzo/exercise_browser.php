<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edzéskereső</title>
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
    
    .muscle-groups {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 25px;
      margin-bottom: 50px;
    }
    
    .muscle-card {
      background: rgba(10, 14, 39, 0.6);
      border: 2px solid rgba(88, 166, 255, 0.15);
      border-radius: 20px;
      padding: 35px;
      text-align: center;
      cursor: pointer;
      transition: all 0.4s;
      position: relative;
      overflow: hidden;
      min-height: 180px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    
    .muscle-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(88, 166, 255, 0.1), rgba(35, 134, 54, 0.1));
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .muscle-card:hover::before {
      opacity: 1;
    }
    
    .muscle-card:hover {
      border-color: #238636;
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 15px 40px rgba(35, 134, 54, 0.4);
    }
    
    .muscle-card.active {
      background: linear-gradient(135deg, rgba(88, 166, 255, 0.2), rgba(35, 134, 54, 0.2));
      border-color: #58a6ff;
      box-shadow: 0 12px 35px rgba(88, 166, 255, 0.5);
    }
    
    .muscle-card h5 {
      font-size: 3.5rem;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
      transition: transform 0.3s;
    }
    
    .muscle-card:hover h5 {
      transform: scale(1.15);
    }
    
    .muscle-card p {
      color: #9bbcff;
      font-weight: 700;
      margin: 0;
      font-size: 1.15rem;
      position: relative;
      z-index: 1;
    }
    
    .exercise-results {
      margin-top: 50px;
    }
    
    .exercise-results h3 {
      color: #58a6ff;
      margin-bottom: 30px;
      font-size: 2rem;
      font-weight: 700;
    }
    
    .exercise-item {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 25px;
      transition: all 0.3s;
    }
    
    .exercise-item:hover {
      background: rgba(10, 14, 39, 0.8);
      border-color: #238636;
      transform: translateX(8px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    }
    
    .exercise-item h5 {
      color: #58a6ff;
      margin-bottom: 18px;
      font-size: 1.5rem;
      font-weight: 700;
    }
    
    .exercise-item p {
      color: #d1d5db;
      line-height: 1.7;
      margin-bottom: 12px;
      font-size: 1.05rem;
    }
    
    .exercise-item small {
      color: #fbbf24;
      display: block;
      margin-top: 15px;
      padding: 15px;
      background: rgba(251, 191, 36, 0.1);
      border-radius: 12px;
      border-left: 4px solid #fbbf24;
      font-size: 1rem;
      font-weight: 600;
    }
    
    .no-results {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    
    .no-results h4 {
      font-size: 2rem;
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
      
      .muscle-groups {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .muscle-card h5 {
        font-size: 3rem;
      }
    }
    
    @media (max-width: 480px) {
      h2 {
        font-size: 2rem;
      }
      
      .muscle-card h5 {
        font-size: 2.5rem;
      }
      
      .muscle-card {
        padding: 25px;
        min-height: 150px;
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
    <h2>🏋️ Edzéskereső</h2>

    <div class="muscle-groups">
      <div class="muscle-card" onclick="loadExercises('Mell')">
        <h5>💪</h5>
        <p>Mell</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Hát')">
        <h5>🦸</h5>
        <p>Hát</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Láb')">
        <h5>🦵</h5>
        <p>Láb</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Váll')">
        <h5>🏋️</h5>
        <p>Váll</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Bicepsz')">
        <h5>💪</h5>
        <p>Bicepsz</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Tricepsz')">
        <h5>🦾</h5>
        <p>Tricepsz</p>
      </div>
      <div class="muscle-card" onclick="loadExercises('Has')">
        <h5>🔥</h5>
        <p>Has</p>
      </div>
    </div>

    <div id="exercise-results" class="exercise-results"></div>
  </div>

  <script>
    const exercises = {
      'Mell': [
        { name: 'Fekvenyomás', desc: 'Alapgyakorlat a mellizom fejlesztésére súlyzóval.', tips: 'Lapockák össze, ne pattogj a mellkasodon!' },
        { name: 'Tárogatás', desc: 'Mellizom nyújtása kézi súlyzókkal.', tips: 'Ne túl nehéz súly, érezd a nyújtást!' },
        { name: 'Tolódzkodás', desc: 'Összetett gyakorlat mellre és tricepszre.', tips: 'Dőlj előre a mell hangsúlyozásához!' },
        { name: 'Fekvőtámasz', desc: 'Klasszikus testsúlyos gyakorlat.', tips: 'Test egyenes, core aktív!' }
      ],
      'Hát': [
        { name: 'Húzódzkodás', desc: 'Alapgyakorlat széles hát fejlesztésére.', tips: 'Lapockák le, ne lengess!' },
        { name: 'Evezés súlyzóval', desc: 'Hát vastagítására.', tips: 'Hát egyenes, ne görnyed!' },
        { name: 'Lehúzás', desc: 'Gép gyakorlat széles hátra.', tips: 'Lapockák össze lent!' },
        { name: 'Deadlift', desc: 'Az egyik legjobb össztest gyakorlat.', tips: 'HÁT MINDIG EGYENES! Kritikus!' }
      ],
      'Láb': [
        { name: 'Guggolás', desc: 'A lábedzés királya.', tips: 'Térd ne menjen túl előre, feneked hátra!' },
        { name: 'Kitörés', desc: 'Egyoldali lábgyakorlat.', tips: 'Törzs függőleges, tarts egyensúlyt!' },
        { name: 'Lábtológép', desc: 'Biztonságos gépi gyakorlat.', tips: 'Csípő ne billenjen el!' },
        { name: 'Vádli emelés', desc: 'Vádli fejlesztő gyakorlat.', tips: 'Teljes mozgástartomány!' }
      ],
      'Váll': [
        { name: 'Vállból nyomás', desc: 'Alapgyakorlat vállra.', tips: 'Ne dőlj hátra, core aktív!' },
        { name: 'Oldalemelés', desc: 'Középső váll fejlesztő.', tips: 'Könyökkel vezess, ne lendíts!' },
        { name: 'Előreemelés', desc: 'Elülső váll gyakorlat.', tips: 'Kontrollált mozgás!' },
        { name: 'Vállvonás', desc: 'Trapéz (nyak) fejlesztő.', tips: 'Egyenesen fel, szorítsd össze!' }
      ],
      'Bicepsz': [
        { name: 'Bicepsz hajlítás', desc: 'Alapgyakorlat bicepszre.', tips: 'Könyök fix, ne lendíts!' },
        { name: 'Kalapács hajlítás', desc: 'Bicepsz és karalkar.', tips: 'Tenyér befelé!' },
        { name: 'Koncentrációs hajlítás', desc: 'Izolált bicepsz munka.', tips: 'Teljes fókusz, lassú mozgás!' }
      ],
      'Tricepsz': [
        { name: 'Tricepsz nyújtás', desc: 'Kábeles tricepsz gyakorlat.', tips: 'Könyök fix, nyújtsd ki teljesen!' },
        { name: 'Hátra nyújtás', desc: 'Tricepsz hosszú fej fejlesztő.', tips: 'Könyök fix, felfelé néz!' },
        { name: 'Szűk fekvenyomás', desc: 'Összetett tricepsz gyakorlat.', tips: 'Könyök közel a testhez!' },
        { name: 'Gyémánt fekvőtámasz', desc: 'Testsúlyos tricepsz munka.', tips: 'Kezek közel, intenzív!' }
      ],
      'Has': [
        { name: 'Plank', desc: 'Core stabilizáció.', tips: 'Fenék ne álljon ki, test egyenes!' },
        { name: 'Crunch', desc: 'Felső has gyakorlat.', tips: 'Csak lapocka emelkedik fel!' },
        { name: 'Lábemelés', desc: 'Alsó has fejlesztő.', tips: 'Ne lendíts, lassan!' },
        { name: 'Russian Twist', desc: 'Ferde hasizmok.', tips: 'Kontrollált forgás!' },
        { name: 'Hegymászó', desc: 'Kardió + core.', tips: 'Csípő ne ugráljon!' }
      ]
    };
    
    function loadExercises(group) {
      document.querySelectorAll('.muscle-card').forEach(card => {
        card.classList.remove('active');
      });
      event.target.closest('.muscle-card').classList.add('active');
      
      const container = document.getElementById('exercise-results');
      const list = exercises[group] || [];
      
      if (list.length === 0) {
        container.innerHTML = `
          <div class="no-results">
            <h4>😕 Nincs elérhető gyakorlat</h4>
          </div>
        `;
        return;
      }
      
      let html = `<h3>${group} gyakorlatok (${list.length} db)</h3>`;
      
      list.forEach(ex => {
        html += `
          <div class="exercise-item">
            <h5>${ex.name}</h5>
            <p>${ex.desc}</p>
            <small><strong>💡 Tipp:</strong> ${ex.tips}</small>
          </div>
        `;
      });
      
      container.innerHTML = html;
      container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  </script>

</body>
</html>