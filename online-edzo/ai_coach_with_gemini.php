<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
include_once 'premium_check.php';

$user_id = $_SESSION['user_id'];
$is_premium = isPremium($user_id);

// Prémium ellenőrzés - csak prémium felhasználóknak
if (!$is_premium) {
  header('Location: upgrade.php?reason=ai_coach');
  exit;
}

// ⚠️ FONTOS: Add meg itt az API kulcsodat!
define('GEMINI_API_KEY', 'AIzaSyA-y1EuZ4V_p6eMv1Y98D4vbYIGCTDDr2U'); // <-- Cseréld le!

// Chat üzenetek lekérése
$stmt = $pdo->prepare("SELECT * FROM ai_chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Új üzenet küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_message'])) {
  $user_message = trim($_POST['user_message'] ?? '');
  
  if ($user_message) {
    // Felhasználó üzenetének mentése
    $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (user_id, role, message) VALUES (?, 'user', ?)");
    $stmt->execute([$user_id, $user_message]);
    
    // AI válasz generálása VALÓDI AI-val
    $ai_response = generateGeminiResponse($user_message, $messages);
    
    // AI válasz mentése
    $stmt = $pdo->prepare("INSERT INTO ai_chat_messages (user_id, role, message) VALUES (?, 'assistant', ?)");
    $stmt->execute([$user_id, $ai_response]);
    
    // Redirect to prevent form resubmission
    header('Location: ai_coach.php');
    exit;
  }
}

// Chat history törlése
if (isset($_GET['clear_chat'])) {
  $stmt = $pdo->prepare("DELETE FROM ai_chat_messages WHERE user_id = ?");
  $stmt->execute([$user_id]);
  header('Location: ai_coach.php');
  exit;
}

// VALÓDI AI - Google Gemini API integráció
function generateGeminiResponse($user_message, $chat_history) {
  $api_key = GEMINI_API_KEY;
  
  // Ellenőrizzük, hogy be van-e állítva az API kulcs
  if ($api_key === 'IDE_MÁSOLD_BE_AZ_API_KULCSODAT' || empty($api_key)) {
    return "⚠️ Hiba: Az API kulcs nincs beállítva!\n\nKövesd az alábbi lépéseket:\n\n1. Menj: https://aistudio.google.com/app/apikey\n2. Jelentkezz be Google fiókkal\n3. Hozz létre API kulcsot\n4. Nyisd meg: ai_coach.php fájlt\n5. Keresd meg: define('GEMINI_API_KEY', '...')\n6. Illeszd be az API kulcsodat\n7. Mentsd el a fájlt";
  }
  
  // System prompt - AI személyiség és szabályok
  $system_prompt = "Te egy professzionális személyi edző vagy, aki segít az edzéssel, táplálkozással és egészséges életmóddal kapcsolatos kérdésekben.

FONTOS SZABÁLYOK:
- MINDIG magyar nyelven válaszolj!
- Légy barátságos, motiváló és támogató
- Add részletes, praktikus tanácsokat
- Ha orvosi kérdés, MINDIG mondd, hogy orvoshoz kell fordulni
- Használj emojikat a válaszokban
- Ha nem vagy biztos valamiben, mondd el őszintén
- Konkrét példákat és számításokat adj
- Kérdezz vissza, ha pontosítás kell

TÉMÁK amikben segítesz:
- Edzéstervek (kezdő, haladó, izomépítés, fogyás)
- Gyakorlat technikák
- Táplálkozás (kalória, makrók, étrend)
- Motiváció és célkitűzés
- Regeneráció és alvás
- Kiegészítők
- Sérülésmegelőzés

Ne add magad ki AI-nak, viselkedj mint egy tapasztalt edző!";

  // Chat history összeállítása az AI számára
  $conversation = [];
  
  // System prompt hozzáadása
  $conversation[] = [
    'role' => 'user',
    'parts' => [['text' => $system_prompt]]
  ];
  $conversation[] = [
    'role' => 'model',
    'parts' => [['text' => 'Értettem! Készen állok, hogy professzionális edzőként segítsek. Várom a kérdéseket! 💪']]
  ];
  
  // Előző üzenetek hozzáadása (utolsó 10 üzenet a kontextushoz)
  $recent_messages = array_slice($chat_history, -10);
  foreach ($recent_messages as $msg) {
    $role = $msg['role'] === 'user' ? 'user' : 'model';
    $conversation[] = [
      'role' => $role,
      'parts' => [['text' => $msg['message']]]
    ];
  }
  
  // Jelenlegi üzenet hozzáadása
  $conversation[] = [
    'role' => 'user',
    'parts' => [['text' => $user_message]]
  ];
  
  // API kérés összeállítása
  $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;
  
  $data = [
    'contents' => $conversation,
    'generationConfig' => [
      'temperature' => 0.7,
      'topK' => 40,
      'topP' => 0.95,
      'maxOutputTokens' => 1024,
    ],
    'safetySettings' => [
      [
        'category' => 'HARM_CATEGORY_HARASSMENT',
        'threshold' => 'BLOCK_NONE'
      ],
      [
        'category' => 'HARM_CATEGORY_HATE_SPEECH',
        'threshold' => 'BLOCK_NONE'
      ],
      [
        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
        'threshold' => 'BLOCK_NONE'
      ],
      [
        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
        'threshold' => 'BLOCK_NONE'
      ]
    ]
  ];
  
  // API hívás
  $ch = curl_init($api_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  
  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curl_error = curl_error($ch);
  curl_close($ch);
  
  // Hibakezelés
  if ($curl_error) {
    return "⚠️ Hálózati hiba történt: " . $curl_error . "\n\nPróbáld újra később!";
  }
  
  if ($http_code !== 200) {
    $error_data = json_decode($response, true);
    $error_message = $error_data['error']['message'] ?? 'Ismeretlen hiba';
    
    if (strpos($error_message, 'API key not valid') !== false) {
      return "⚠️ Hibás API kulcs!\n\n1. Ellenőrizd: https://aistudio.google.com/app/apikey\n2. Győződj meg róla, hogy helyesen másoltad be\n3. Az API kulcs aktív-e\n4. Frissítsd az ai_coach.php fájlt az új kulccsal";
    }
    
    return "⚠️ AI hiba történt: " . $error_message . "\n\nPróbáld újra!";
  }
  
  // Válasz feldolgozása
  $result = json_decode($response, true);
  
  if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    return trim($result['candidates'][0]['content']['parts'][0]['text']);
  }
  
  return "⚠️ Nem sikerült választ kapni az AI-tól. Próbáld újra!";
}

// Újra lekérjük az üzeneteket a friss adatokhoz
$stmt = $pdo->prepare("SELECT * FROM ai_chat_messages WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Edző Asszisztens - OnlineEdző</title>
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
      display: flex;
      flex-direction: column;
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
    
    .chat-container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 0 20px 20px;
      position: relative;
      z-index: 1;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    
    h2 {
      text-align: center;
      margin-bottom: 15px;
      font-size: 3rem;
      font-weight: 900;
      background: linear-gradient(135deg, #58a6ff, #238636);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: fadeInDown 0.8s ease;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 30px;
      font-size: 1.1rem;
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .chat-info {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid #58a6ff;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 25px;
      text-align: center;
    }
    
    .chat-info p {
      color: #9bbcff;
      margin: 0;
      line-height: 1.6;
    }
    
    .chat-box {
      background: rgba(10, 14, 39, 0.6);
      border: 1px solid rgba(88, 166, 255, 0.15);
      border-radius: 25px;
      padding: 30px;
      flex: 1;
      display: flex;
      flex-direction: column;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
      min-height: 500px;
      max-height: calc(100vh - 400px);
    }
    
    .chat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(88, 166, 255, 0.2);
    }
    
    .chat-header h3 {
      color: #58a6ff;
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
    }
    
    .btn-clear {
      background: rgba(239, 68, 68, 0.2);
      color: #ef4444;
      border: 1px solid #ef4444;
      padding: 8px 20px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .btn-clear:hover {
      background: rgba(239, 68, 68, 0.3);
      transform: translateY(-2px);
      color: #ef4444;
    }
    
    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: rgba(0,0,0,0.2);
      border-radius: 15px;
      margin-bottom: 20px;
      scroll-behavior: smooth;
    }
    
    .messages::-webkit-scrollbar {
      width: 8px;
    }
    
    .messages::-webkit-scrollbar-track {
      background: rgba(0,0,0,0.2);
      border-radius: 10px;
    }
    
    .messages::-webkit-scrollbar-thumb {
      background: rgba(88, 166, 255, 0.3);
      border-radius: 10px;
    }
    
    .messages::-webkit-scrollbar-thumb:hover {
      background: rgba(88, 166, 255, 0.5);
    }
    
    .message {
      margin-bottom: 20px;
      animation: fadeIn 0.4s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .message.user {
      display: flex;
      justify-content: flex-end;
    }
    
    .message.assistant {
      display: flex;
      justify-content: flex-start;
    }
    
    .message-content {
      max-width: 75%;
      padding: 15px 20px;
      border-radius: 18px;
      position: relative;
      word-wrap: break-word;
      white-space: pre-wrap;
      line-height: 1.6;
    }
    
    .message.user .message-content {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .message.assistant .message-content {
      background: rgba(88, 166, 255, 0.15);
      color: #e6edf3;
      border: 1px solid rgba(88, 166, 255, 0.3);
      border-bottom-left-radius: 4px;
    }
    
    .message-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin: 0 12px;
      flex-shrink: 0;
    }
    
    .message.user .message-avatar {
      order: 2;
      background: linear-gradient(135deg, #238636, #2ea043);
    }
    
    .message.assistant .message-avatar {
      order: 1;
      background: linear-gradient(135deg, #58a6ff, #3b82f6);
    }
    
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #9ca3af;
    }
    
    .empty-state h4 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: #58a6ff;
    }
    
    .empty-state p {
      margin-bottom: 10px;
      font-size: 1.05rem;
    }
    
    .suggested-questions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 30px;
    }
    
    .suggested-question {
      background: rgba(88, 166, 255, 0.1);
      border: 1px solid rgba(88, 166, 255, 0.3);
      padding: 15px;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      font-size: 0.95rem;
    }
    
    .suggested-question:hover {
      background: rgba(88, 166, 255, 0.2);
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(88, 166, 255, 0.3);
    }
    
    .input-area {
      display: flex;
      gap: 15px;
      align-items: flex-end;
    }
    
    .input-wrapper {
      flex: 1;
      position: relative;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.1);
      color: #fff;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s;
      resize: none;
      min-height: 56px;
      max-height: 150px;
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
    
    .btn-send {
      background: linear-gradient(135deg, #238636, #2ea043);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 35px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 6px 20px rgba(35, 134, 54, 0.4);
      white-space: nowrap;
    }
    
    .btn-send:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(35, 134, 54, 0.6);
    }
    
    .btn-send:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .typing-indicator {
      display: none;
      padding: 15px 20px;
      background: rgba(88, 166, 255, 0.15);
      border: 1px solid rgba(88, 166, 255, 0.3);
      border-radius: 18px;
      border-bottom-left-radius: 4px;
      max-width: 100px;
    }
    
    .typing-indicator.active {
      display: block;
    }
    
    .typing-dots {
      display: flex;
      gap: 6px;
      align-items: center;
      justify-content: center;
    }
    
    .typing-dots span {
      width: 8px;
      height: 8px;
      background: #58a6ff;
      border-radius: 50%;
      animation: typingBounce 1.4s infinite ease-in-out;
    }
    
    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    
    @keyframes typingBounce {
      0%, 80%, 100% { transform: scale(0); }
      40% { transform: scale(1); }
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
        font-size: 2.2rem;
      }
      
      .chat-box {
        padding: 20px 15px;
        max-height: calc(100vh - 350px);
      }
      
      .message-content {
        max-width: 85%;
        padding: 12px 16px;
        font-size: 0.95rem;
      }
      
      .input-area {
        flex-direction: column;
      }
      
      .btn-send {
        width: 100%;
      }
      
      .suggested-questions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 25%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 55%; animation-delay: 9s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 85%; animation-delay: 15s;"></div>
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

  <div class="chat-container">
    <h2>🤖 AI Edző Asszisztens</h2>
    <p class="subtitle">Igazi mesterséges intelligencia - Kérdezz bármit!</p>

    <div class="chat-info">
      <p>🌟 <strong>Google Gemini AI</strong> - Valódi beszélgetés professzionális tanácsokkal!</p>
    </div>

    <div class="chat-box">
      <div class="chat-header">
        <h3>💬 Chat</h3>
        <?php if (!empty($messages)): ?>
          <a href="?clear_chat=1" class="btn-clear" onclick="return confirm('Biztosan törlöd a beszélgetés előzményeket?')">
            🗑️ Előzmények törlése
          </a>
        <?php endif; ?>
      </div>

      <div class="messages" id="messages">
        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <h4>👋 Szia! Én vagyok az AI Edződ!</h4>
            <p>Powered by Google Gemini AI - Írhatsz bármit természetes nyelven!</p>
            
            <div class="suggested-questions">
              <div class="suggested-question" onclick="askQuestion('Szia! Segítesz elkezdeni az edzést?')">
                🏃 Hogyan kezdjem?
              </div>
              <div class="suggested-question" onclick="askQuestion('Milyen edzéstervet ajánlasz nekem izomépítéshez?')">
                💪 Izomépítő terv
              </div>
              <div class="suggested-question" onclick="askQuestion('Szeretnék 10 kg-ot fogyni. Hogyan kezdjem?')">
                🔥 Fogyás terv
              </div>
              <div class="suggested-question" onclick="askQuestion('Hogyan csináljam helyesen a guggolást?')">
                🏋️ Gyakorlat technika
              </div>
              <div class="suggested-question" onclick="askQuestion('Mennyit kell ennem naponta?')">
                🍎 Táplálkozás
              </div>
              <div class="suggested-question" onclick="askQuestion('Nincs motivációm edzeni, segíts!')">
                ⚡ Motiváció
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <div class="message <?= htmlspecialchars($msg['role']) ?>">
              <?php if ($msg['role'] === 'assistant'): ?>
                <div class="message-avatar">🤖</div>
              <?php endif; ?>
              
              <div class="message-content">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
              </div>
              
              <?php if ($msg['role'] === 'user'): ?>
                <div class="message-avatar">👤</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="message assistant">
          <div class="message-avatar">🤖</div>
          <div class="typing-indicator" id="typingIndicator">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <form method="POST" class="input-area" onsubmit="return handleSubmit()">
        <div class="input-wrapper">
          <textarea 
            name="user_message" 
            id="messageInput"
            class="form-control" 
            placeholder="Írd be a kérdésedet..."
            rows="1"
            required
            onkeydown="handleKeyPress(event)"
            oninput="autoResize(this)"></textarea>
        </div>
        <button type="submit" class="btn-send" id="sendBtn">
          🚀 Küldés
        </button>
      </form>
    </div>
  </div>

  <script>
    // Auto-scroll to bottom on page load
    window.addEventListener('load', function() {
      const messages = document.getElementById('messages');
      messages.scrollTop = messages.scrollHeight;
    });

    // Auto-resize textarea
    function autoResize(textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
    }

    // Handle Enter key (Shift+Enter for new line, Enter to send)
    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.querySelector('.input-area').dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
      }
    }

    // Handle form submit with loading indicator
    function handleSubmit() {
      const input = document.getElementById('messageInput');
      const btn = document.getElementById('sendBtn');
      const typingIndicator = document.getElementById('typingIndicator');
      
      if (input.value.trim() === '') {
        return false;
      }
      
      // Show loading state
      btn.disabled = true;
      btn.textContent = '⏳ Gondolkodik...';
      typingIndicator.classList.add('active');
      
      // Scroll to bottom
      setTimeout(() => {
        const messages = document.getElementById('messages');
        messages.scrollTop = messages.scrollHeight;
      }, 100);
      
      return true;
    }

    // Suggested question click handler
    function askQuestion(question) {
      document.getElementById('messageInput').value = question;
      document.getElementById('messageInput').focus();
    }

    // Focus on input on page load
    document.getElementById('messageInput').focus();
  </script>

</body>
</html>