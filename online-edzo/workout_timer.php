<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edzés Timer</title>
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
      max-width: 1000px;
      margin: 60px auto;
      padding: 0 20px;
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
    
    .timer-card {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 40px;
      margin-bottom: 35px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      transition: all 0.3s;
    }
    
    .timer-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 50px rgba(0,0,0,0.4);
    }
    
    .timer-card h3 {
      color: #58a6ff;
      margin-bottom: 30px;
      text-align: center;
      font-size: 1.8rem;
      font-weight: 700;
    }
    
    .timer-display {
      font-size: 5rem;
      font-weight: 900;
      text-align: center;
      color: #fff;
      margin: 40px 0;
      font-family: 'Courier New', monospace;
      text-shadow: 0 0 30px rgba(88, 166, 255, 0.6);
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .timer-controls {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .btn-timer {
      padding: 15px 40px;
      font-size: 1.15rem;
      font-weight: 700;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    .btn-start {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
    }
    
    .btn-start:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(35, 134, 54, 0.5);
    }
    
    .btn-pause {
      background: linear-gradient(135deg, #f59e0b, #f97316);
      color: white;
    }
    
    .btn-pause:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(245, 158, 11, 0.5);
    }
    
    .btn-reset {
      background: linear-gradient(135deg, #dc2626, #ef4444);
      color: white;
    }
    
    .btn-reset:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(220, 38, 38, 0.5);
    }
    
    .preset-buttons {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    
    .btn-preset {
      background: rgba(88, 166, 255, 0.2);
      border: 2px solid #58a6ff;
      color: #58a6ff;
      padding: 10px 25px;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s;
      font-weight: 600;
      font-size: 1rem;
    }
    
    .btn-preset:hover {
      background: rgba(88, 166, 255, 0.3);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(88, 166, 255, 0.4);
    }
    
    .hiit-controls {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 25px;
    }
    
    .hiit-input-group {
      background: rgba(0,0,0,0.3);
      padding: 20px;
      border-radius: 12px;
      border: 1px solid rgba(88, 166, 255, 0.1);
    }
    
    .hiit-input-group label {
      display: block;
      color: #9bbcff;
      margin-bottom: 10px;
      font-size: 1rem;
      font-weight: 600;
    }
    
    .hiit-input-group input {
      width: 100%;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      padding: 12px;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: 600;
    }
    
    .hiit-input-group input:focus {
      outline: none;
      border-color: #58a6ff;
      box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.15);
    }
    
    .hiit-status {
      text-align: center;
      font-size: 1.8rem;
      font-weight: 700;
      margin: 25px 0;
      padding: 20px;
      border-radius: 12px;
    }
    
    .status-work {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
      border: 2px solid #22c55e;
    }
    
    .status-rest {
      background: rgba(245, 158, 11, 0.2);
      color: #f59e0b;
      border: 2px solid #f59e0b;
    }
    
    .round-counter {
      text-align: center;
      font-size: 1.3rem;
      color: #9bbcff;
      margin-top: 15px;
      font-weight: 600;
    }
    
    .alarm-flash {
      animation: flash 0.5s ease-in-out 3;
    }
    
    @keyframes flash {
      0%, 100% { background: rgba(10, 14, 39, 0.6); }
      50% { background: rgba(220, 38, 38, 0.4); }
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
      
      .timer-display {
        font-size: 3.5rem;
      }
      
      .hiit-controls {
        grid-template-columns: 1fr;
      }
      
      .btn-timer {
        padding: 12px 30px;
        font-size: 1rem;
      }
    }
    
    @media (max-width: 480px) {
      h2 {
        font-size: 2rem;
      }
      
      .timer-display {
        font-size: 3rem;
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
    <h2>⏱️ Edzés Timer</h2>

    <!-- Pihenőidő Timer -->
    <div class="timer-card">
      <h3>🛋️ Pihenőidő Timer (szettek között)</h3>
      <div class="timer-display" id="restDisplay">01:30</div>
      <div class="timer-controls">
        <button class="btn-timer btn-start" onclick="startRestTimer()">▶️ Start</button>
        <button class="btn-timer btn-pause" onclick="pauseRestTimer()">⏸️ Pause</button>
        <button class="btn-timer btn-reset" onclick="resetRestTimer()">🔄 Reset</button>
      </div>
      <div class="preset-buttons">
        <button class="btn-preset" onclick="setRestTime(30)">30s</button>
        <button class="btn-preset" onclick="setRestTime(60)">1 perc</button>
        <button class="btn-preset" onclick="setRestTime(90)">1:30</button>
        <button class="btn-preset" onclick="setRestTime(120)">2 perc</button>
        <button class="btn-preset" onclick="setRestTime(180)">3 perc</button>
      </div>
    </div>

    <!-- Stopperóra -->
    <div class="timer-card">
      <h3>⏲️ Stopperóra (edzés időtartam)</h3>
      <div class="timer-display" id="stopwatchDisplay">00:00:00</div>
      <div class="timer-controls">
        <button class="btn-timer btn-start" onclick="startStopwatch()">▶️ Start</button>
        <button class="btn-timer btn-pause" onclick="pauseStopwatch()">⏸️ Pause</button>
        <button class="btn-timer btn-reset" onclick="resetStopwatch()">🔄 Reset</button>
      </div>
    </div>

    <!-- HIIT Timer -->
    <div class="timer-card">
      <h3>🔥 HIIT Interval Timer</h3>
      <div class="hiit-controls">
        <div class="hiit-input-group">
          <label>💪 Munka idő (mp)</label>
          <input type="number" id="workTime" value="20" min="5" max="300">
        </div>
        <div class="hiit-input-group">
          <label>😮‍💨 Pihenő idő (mp)</label>
          <input type="number" id="restTimeHiit" value="10" min="5" max="300">
        </div>
        <div class="hiit-input-group">
          <label>🔢 Körök száma</label>
          <input type="number" id="rounds" value="8" min="1" max="50">
        </div>
        <div class="hiit-input-group">
          <label>⏳ Bemelegítés (mp)</label>
          <input type="number" id="warmup" value="10" min="0" max="300">
        </div>
      </div>
      <div id="hiitStatus" class="hiit-status" style="display:none;"></div>
      <div class="timer-display" id="hiitDisplay">00:00</div>
      <div class="round-counter" id="roundCounter"></div>
      <div class="timer-controls">
        <button class="btn-timer btn-start" onclick="startHiit()">▶️ Start</button>
        <button class="btn-timer btn-pause" onclick="pauseHiit()">⏸️ Pause</button>
        <button class="btn-timer btn-reset" onclick="resetHiit()">🔄 Reset</button>
      </div>
    </div>
  </div>

  <script>
    // Pihenőidő Timer
    let restTime = 90;
    let restTimeLeft = 90;
    let restInterval = null;
    let restRunning = false;

    function setRestTime(seconds) {
      restTime = seconds;
      restTimeLeft = seconds;
      updateRestDisplay();
    }

    function updateRestDisplay() {
      const mins = Math.floor(restTimeLeft / 60);
      const secs = restTimeLeft % 60;
      document.getElementById('restDisplay').textContent = 
        `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function startRestTimer() {
      if (restRunning) return;
      restRunning = true;
      restInterval = setInterval(() => {
        restTimeLeft--;
        updateRestDisplay();
        if (restTimeLeft <= 0) {
          pauseRestTimer();
          playAlarm();
          document.querySelectorAll('.timer-card')[0].classList.add('alarm-flash');
          setTimeout(() => {
            document.querySelectorAll('.timer-card')[0].classList.remove('alarm-flash');
          }, 1500);
          restTimeLeft = restTime;
          updateRestDisplay();
        }
      }, 1000);
    }

    function pauseRestTimer() {
      restRunning = false;
      clearInterval(restInterval);
    }

    function resetRestTimer() {
      pauseRestTimer();
      restTimeLeft = restTime;
      updateRestDisplay();
    }

    // Stopperóra
    let stopwatchTime = 0;
    let stopwatchInterval = null;
    let stopwatchRunning = false;

    function updateStopwatchDisplay() {
      const hours = Math.floor(stopwatchTime / 3600);
      const mins = Math.floor((stopwatchTime % 3600) / 60);
      const secs = stopwatchTime % 60;
      document.getElementById('stopwatchDisplay').textContent = 
        `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function startStopwatch() {
      if (stopwatchRunning) return;
      stopwatchRunning = true;
      stopwatchInterval = setInterval(() => {
        stopwatchTime++;
        updateStopwatchDisplay();
      }, 1000);
    }

    function pauseStopwatch() {
      stopwatchRunning = false;
      clearInterval(stopwatchInterval);
    }

    function resetStopwatch() {
      pauseStopwatch();
      stopwatchTime = 0;
      updateStopwatchDisplay();
    }

    // HIIT Timer
    let hiitInterval = null;
    let hiitRunning = false;
    let hiitTimeLeft = 0;
    let hiitPhase = 'warmup';
    let currentRound = 0;
    let totalRounds = 0;
    let workDuration = 0;
    let restDuration = 0;
    let warmupDuration = 0;

    function updateHiitDisplay() {
      const mins = Math.floor(hiitTimeLeft / 60);
      const secs = hiitTimeLeft % 60;
      document.getElementById('hiitDisplay').textContent = 
        `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function updateHiitStatus() {
      const statusEl = document.getElementById('hiitStatus');
      const roundEl = document.getElementById('roundCounter');
      statusEl.style.display = 'block';
      
      if (hiitPhase === 'warmup') {
        statusEl.textContent = '🏃 Bemelegítés';
        statusEl.className = 'hiit-status status-work';
        roundEl.textContent = '';
      } else if (hiitPhase === 'work') {
        statusEl.textContent = '💪 DOLGOZZ!';
        statusEl.className = 'hiit-status status-work';
        roundEl.textContent = `Kör: ${currentRound} / ${totalRounds}`;
      } else if (hiitPhase === 'rest') {
        statusEl.textContent = '😮‍💨 Pihenj';
        statusEl.className = 'hiit-status status-rest';
        roundEl.textContent = `Kör: ${currentRound} / ${totalRounds}`;
      }
    }

    function startHiit() {
      if (hiitRunning) return;
      
      if (currentRound === 0) {
        workDuration = parseInt(document.getElementById('workTime').value);
        restDuration = parseInt(document.getElementById('restTimeHiit').value);
        totalRounds = parseInt(document.getElementById('rounds').value);
        warmupDuration = parseInt(document.getElementById('warmup').value);
        
        if (warmupDuration > 0) {
          hiitPhase = 'warmup';
          hiitTimeLeft = warmupDuration;
        } else {
          hiitPhase = 'work';
          currentRound = 1;
          hiitTimeLeft = workDuration;
        }
      }
      
      hiitRunning = true;
      updateHiitStatus();
      updateHiitDisplay();
      
      hiitInterval = setInterval(() => {
        hiitTimeLeft--;
        updateHiitDisplay();
        
        if (hiitTimeLeft <= 0) {
          playAlarm();
          
          if (hiitPhase === 'warmup') {
            hiitPhase = 'work';
            currentRound = 1;
            hiitTimeLeft = workDuration;
          } else if (hiitPhase === 'work') {
            if (currentRound < totalRounds) {
              hiitPhase = 'rest';
              hiitTimeLeft = restDuration;
            } else {
              pauseHiit();
              document.getElementById('hiitStatus').textContent = '🎉 KÉSZ!';
              return;
            }
          } else if (hiitPhase === 'rest') {
            hiitPhase = 'work';
            currentRound++;
            hiitTimeLeft = workDuration;
          }
          
          updateHiitStatus();
        }
      }, 1000);
    }

    function pauseHiit() {
      hiitRunning = false;
      clearInterval(hiitInterval);
    }

    function resetHiit() {
      pauseHiit();
      hiitTimeLeft = 0;
      currentRound = 0;
      hiitPhase = 'warmup';
      document.getElementById('hiitDisplay').textContent = '00:00';
      document.getElementById('hiitStatus').style.display = 'none';
      document.getElementById('roundCounter').textContent = '';
    }

    // Hangjelzés
    function playAlarm() {
      const audioContext = new (window.AudioContext || window.webkitAudioContext)();
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();
      
      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);
      
      oscillator.frequency.value = 800;
      oscillator.type = 'sine';
      
      gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
      
      oscillator.start(audioContext.currentTime);
      oscillator.stop(audioContext.currentTime + 0.5);
    }

    // Init
    updateRestDisplay();
    updateStopwatchDisplay();
  </script>

</body>
</html>