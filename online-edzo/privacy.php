<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adatvédelmi Szabályzat - OnlineEdző</title>
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
    
    .data-table {
      width: 100%;
      margin: 25px 0;
      border-collapse: collapse;
    }
    
    .data-table th {
      background: rgba(88, 166, 255, 0.15);
      color: #58a6ff;
      padding: 15px;
      text-align: left;
      font-weight: 700;
    }
    
    .data-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
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
      
      .data-table {
        font-size: 0.9rem;
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
    <h1>🔒 Adatvédelmi Szabályzat</h1>
    <p class="last-updated">Utolsó frissítés: 2025. január 26.</p>

    <div class="content-card">
      <h2>1. Bevezetés</h2>
      <p>
        Az OnlineEdző elkötelezett felhasználói adatainak védelme mellett. 
        Jelen Adatvédelmi Szabályzat részletesen ismerteti, hogy milyen adatokat gyűjtünk, 
        hogyan használjuk, tároljuk és védjük azokat.
      </p>
      
      <div class="highlight-box">
        <strong>📌 GDPR megfelelőség:</strong> Adatkezelésünk megfelel az Európai Unió 
        Általános Adatvédelmi Rendeletének (GDPR) és a hatályos magyar jogszabályoknak.
      </div>

      <h2>2. Adatkezelő adatai</h2>
      <p>
        <strong>Név:</strong> OnlineEdző<br>
        <strong>Email:</strong> info@onlineedzo.hu<br>
        <strong>Weboldal:</strong> www.onlineedzo.hu
      </p>

      <h2>3. Gyűjtött adatok</h2>
      
      <table class="data-table">
        <thead>
          <tr>
            <th>Adattípus</th>
            <th>Cél</th>
            <th>Jogalap</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Regisztrációs adatok</strong><br>
            (név, email, jelszó)</td>
            <td>Fiók létrehozása, azonosítás</td>
            <td>Szerződés teljesítése</td>
          </tr>
          <tr>
            <td><strong>Edzési adatok</strong><br>
            (edzésnapló, gyakorlatok)</td>
            <td>Szolgáltatás nyújtása, fejlődés követése</td>
            <td>Szerződés teljesítése</td>
          </tr>
          <tr>
            <td><strong>Testméretek</strong><br>
            (súly, testzsír%, körméretek)</td>
            <td>Progress követés</td>
            <td>Szerződés teljesítése</td>
          </tr>
          <tr>
            <td><strong>Táplálkozási adatok</strong><br>
            (étkezések, kalória)</td>
            <td>Prémium szolgáltatás (opcionális)</td>
            <td>Szerződés teljesítése</td>
          </tr>
          <tr>
            <td><strong>Progress fotók</strong><br>
            (feltöltött képek)</td>
            <td>Vizuális fejlődés követés</td>
            <td>Önkéntes hozzájárulás</td>
          </tr>
          <tr>
            <td><strong>Fizetési adatok</strong><br>
            (tranzakció ID)</td>
            <td>Prémium előfizetés kezelése</td>
            <td>Szerződés teljesítése</td>
          </tr>
          <tr>
            <td><strong>Technikai adatok</strong><br>
            (IP cím, böngésző, eszköz)</td>
            <td>Biztonság, hibaelhárítás</td>
            <td>Jogos érdek</td>
          </tr>
        </tbody>
      </table>

      <h2>4. Az adatgyűjtés célja</h2>
      <p>Az összegyűjtött adatokat a következő célokra használjuk:</p>
      <ul>
        <li>Felhasználói fiók létrehozása és kezelése</li>
        <li>Szolgáltatásaink nyújtása (edzéstervek, napló, követés)</li>
        <li>Személyre szabott edzéstervek generálása</li>
        <li>Statisztikák és fejlődés követés biztosítása</li>
        <li>Prémium előfizetés kezelése és számlázás</li>
        <li>Ügyfélszolgálat és technikai támogatás</li>
        <li>A szolgáltatás fejlesztése és optimalizálása</li>
        <li>Jogsértések megelőzése és biztonság garantálása</li>
      </ul>

      <h2>5. Adattárolás és biztonság</h2>
      <p>
        Az Ön adatait biztonságos szervereken tároljuk, és a következő biztonsági intézkedéseket alkalmazzuk:
      </p>
      <ul>
        <li><strong>Titkosítás:</strong> Az adatokat titkosított kapcsolaton (HTTPS/SSL) továbbítjuk</li>
        <li><strong>Jelszóvédelem:</strong> A jelszavakat hash algoritmussal tároljuk (nem láthatók)</li>
        <li><strong>Hozzáférés-korlátozás:</strong> Csak jogosult személyek férhetnek hozzá az adatokhoz</li>
        <li><strong>Rendszeres biztonsági mentés:</strong> Adatvesztés megelőzése</li>
        <li><strong>Frissítések:</strong> Rendszeres biztonsági frissítések</li>
      </ul>

      <div class="highlight-box">
        <strong>🔐 Fontos:</strong> Soha nem kérjük el az Ön jelszavát e-mailben vagy telefonon!
      </div>

      <h2>6. Adatmegőrzési időtartam</h2>
      <p>Az adatokat a következő időtartamig őrizzük meg:</p>
      <ul>
        <li><strong>Aktív fiók esetén:</strong> A fiók törlésig</li>
        <li><strong>Törölt fiók esetén:</strong> 30 napig (visszaállítás lehetősége)</li>
        <li><strong>Számlázási adatok:</strong> 8 év (számviteli törvény)</li>
        <li><strong>Technikai logok:</strong> Maximum 90 nap</li>
      </ul>

      <h2>7. Adatmegosztás harmadik féllel</h2>
      <p>
        Az Ön adatait <strong>NEM adjuk el</strong> harmadik félnek. Adatokat csak a következő esetekben osztunk meg:
      </p>
      <ul>
        <li><strong>Fizetési szolgáltatók:</strong> Prémium előfizetés feldolgozásához (pl. Stripe, PayPal)</li>
        <li><strong>Tárhelyszolgáltató:</strong> Szerverek üzemeltetéséhez</li>
        <li><strong>Jogi kötelezettség:</strong> Ha törvény vagy hatósági kérés kötelezi</li>
        <li><strong>Szolgáltatás védelmében:</strong> Visszaélés, csalás megelőzése</li>
      </ul>

      <h2>8. Sütik (Cookies)</h2>
      <p>
        Weboldalunk sütiket használ a felhasználói élmény javítása érdekében:
      </p>
      <ul>
        <li><strong>Munkamenet sütik:</strong> Bejelentkezés fenntartása</li>
        <li><strong>Preferencia sütik:</strong> Beállítások megjegyzése</li>
        <li><strong>Analitikai sütik:</strong> Látogatottsági statisztikák (anonim)</li>
      </ul>
      <p>A sütiket bármikor törölheti böngészője beállításaiban.</p>

      <h2>9. Az Ön jogai (GDPR)</h2>
      <p>
        Az adatvédelmi szabályozás alapján Önnek joga van:
      </p>
      <ul>
        <li><strong>Hozzáférés:</strong> Megtekintheti, milyen adatokat tárolunk Önről</li>
        <li><strong>Helyesbítés:</strong> Kérheti téves adatok javítását</li>
        <li><strong>Törlés:</strong> Kérheti adatai törlését ("elfeledtetéshez való jog")</li>
        <li><strong>Korlátozás:</strong> Kérheti adatkezelés korlátozását</li>
        <li><strong>Adathordozhatóság:</strong> Kérheti adatai exportálását</li>
        <li><strong>Tiltakozás:</strong> Tiltakozhat az adatkezelés ellen</li>
        <li><strong>Hozzájárulás visszavonása:</strong> Bármikor visszavonhatja hozzájárulását</li>
      </ul>

      <div class="highlight-box">
        <strong>📧 Jogai gyakorlása:</strong> Az adatvédelmi jogai gyakorlásához 
        küldjön emailt az <strong>info@onlineedzo.hu</strong> címre. 
        Kérését 30 napon belül teljesítjük.
      </div>

      <h2>10. Fiók törlése</h2>
      <p>
        Fiókja bármikor törölhető:
      </p>
      <ol>
        <li>Jelentkezzen be fiókjába</li>
        <li>Menjen a Beállítások menüpontba</li>
        <li>Kattintson a "Fiók törlése" gombra</li>
        <li>Erősítse meg döntését</li>
      </ol>
      <p>
        <strong>Figyelem:</strong> A fiók törlésekor minden edzési adat, mérés és fotó véglegesen törlődik. 
        Ez a művelet 30 napon belül visszafordítható, utána az adatok véglegesen törlésre kerülnek.
      </p>

      <h2>11. Gyermekek adatainak védelme</h2>
      <p>
        Szolgáltatásunk <strong>18 év alatti személyek</strong> számára nem elérhető. 
        Tudatosan nem gyűjtünk gyermekektől adatokat. Ha tudomásunkra jut, hogy 18 év alatti 
        személy regisztrált, azonnal töröljük fiókját.
      </p>

      <h2>12. Módosítások</h2>
      <p>
        Fenntartjuk a jogot, hogy jelen Adatvédelmi Szabályzatot bármikor módosítsuk. 
        Jelentős változások esetén e-mailben értesítjük felhasználóinkat. 
        A módosítások a közzétételtől számított 15 napon belül lépnek hatályba.
      </p>

      <h2>13. Panaszkezelés</h2>
      <p>
        Ha úgy érzi, hogy adatkezelésünk sérti jogait, az alábbi lehetőségei vannak:
      </p>
      <ul>
        <li><strong>Első lépés:</strong> Keressen minket az info@onlineedzo.hu címen</li>
        <li><strong>Felügyeleti hatóság:</strong> Panaszt tehet a Nemzeti Adatvédelmi és 
        Információszabadság Hatóságnál (NAIH)<br>
        <em>Cím: 1055 Budapest, Falk Miksa utca 9-11.<br>
        Email: ugyfelszolgalat@naih.hu<br>
        Telefon: +36 (1) 391-1400</em></li>
      </ul>

      <h2>14. Kapcsolat</h2>
      <p>
        Ha kérdése van az adatvédelemmel kapcsolatban, keressen minket bizalommal:
      </p>
      <ul>
        <li><strong>Email:</strong> info@onlineedzo.hu</li>
        <li><strong>Tárgy:</strong> "Adatvédelmi kérdés"</li>
        <li><strong>Válaszidő:</strong> Maximum 30 nap</li>
      </ul>

      <h2>15. További információk</h2>
      <p>
        További információért látogassa meg:
      </p>
      <ul>
        <li><a href="terms.php" style="color: #58a6ff; text-decoration: underline;">Felhasználási Feltételek</a></li>
        <li><a href="index.php" style="color: #58a6ff; text-decoration: underline;">Főoldal</a></li>
      </ul>

      <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid rgba(88, 166, 255, 0.2); text-align: center; color: #9ca3af;">
        <p>
          <strong>🔒 Adatai biztonságban vannak!</strong><br>
          Köszönjük, hogy megbízik az OnlineEdző szolgáltatásban. 
          Elkötelezettek vagyunk amellett, hogy adatait a lehető legnagyobb biztonságban tartsuk.
        </p>
        <p style="margin-top: 20px;"><strong>© 2025 OnlineEdző. Minden jog fenntartva.</strong></p>
      </div>
    </div>
  </div>

</body>
</html>