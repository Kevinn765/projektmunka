<?php
session_start();
require_once 'db.php';

$errors = [];

// Admin bejelentkezés kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Add meg az email címet és a jelszót.';
    }

    if (empty($errors)) {
        // Admin felhasználó keresése
        $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Ellenőrizzük, hogy ez az admin email
            if ($user['email'] === 'onlineedzo2025@gmail.com') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = true; // Admin flag
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $errors[] = 'Ez nem admin fiók!';
            }
        } else {
            $errors[] = 'Hibás email vagy jelszó.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Bejelentkezés - OnlineEdző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700;900&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #1a0000, #0d0000, #2d0000);
      color: #e6edf3;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .login-card {
      background: rgba(255,255,255,0.05);
      border: 2px solid rgba(239, 68, 68, 0.3);
      backdrop-filter: blur(12px);
      padding: 50px;
      border-radius: 25px;
      width: 100%;
      max-width: 450px;
      box-shadow: 0 0 50px rgba(239, 68, 68, 0.4);
      animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
      from {
        box-shadow: 0 0 30px rgba(239, 68, 68, 0.3);
      }
      to {
        box-shadow: 0 0 50px rgba(239, 68, 68, 0.6);
      }
    }
    
    .admin-badge {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .admin-icon {
      font-size: 4rem;
      margin-bottom: 20px;
      filter: drop-shadow(0 0 20px rgba(239, 68, 68, 0.8));
    }
    
    h2 {
      text-align: center;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
      font-weight: 900;
      font-size: 2.5rem;
      letter-spacing: -1px;
    }
    
    .subtitle {
      text-align: center;
      color: #9ca3af;
      margin-bottom: 40px;
      font-size: 1rem;
    }
    
    .form-label {
      color: #fca5a5;
      font-weight: 600;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }
    
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fff;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
      transition: all 0.3s;
    }
    
    .form-control:focus {
      box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
      background: rgba(255,255,255,0.12);
      border-color: #ef4444;
      color: #fff;
      outline: none;
    }
    
    .form-control::placeholder {
      color: #9ca3af;
    }
    
    .btn-admin {
      width: 100%;
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      border: none;
      padding: 16px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1.1rem;
      transition: all 0.3s;
      margin-top: 25px;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
    }
    
    .btn-admin:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 35px rgba(239, 68, 68, 0.6);
    }
    
    .alert {
      background-color: rgba(255,68,68,0.2);
      border: 2px solid rgba(255,68,68,0.5);
      color: #fca5a5;
      text-align: center;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 25px;
      font-weight: 600;
    }
    
    .back-link {
      text-align: center;
      margin-top: 25px;
    }
    
    .back-link a {
      color: #9ca3af;
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.3s;
    }
    
    .back-link a:hover {
      color: #ef4444;
    }
    
    .security-notice {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 12px;
      padding: 15px;
      margin-top: 25px;
      text-align: center;
      font-size: 0.85rem;
      color: #fca5a5;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <div class="admin-badge">
      <div class="admin-icon">🔐</div>
    </div>
    
    <h2>ADMIN</h2>
    <p class="subtitle">Korlátlan hozzáférés</p>

    <?php if (!empty($errors)): ?>
      <div class="alert">
        ⚠️ <?= htmlspecialchars(implode('<br>', $errors)) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-4">
        <label class="form-label">📧 Admin Email</label>
        <input 
          type="email" 
          name="email" 
          class="form-control" 
          placeholder="onlineedzo2025@gmail.com" 
          required 
          autofocus>
      </div>
      
      <div class="mb-4">
        <label class="form-label">🔑 Jelszó</label>
        <input 
          type="password" 
          name="password" 
          class="form-control" 
          placeholder="••••••••" 
          required>
      </div>
      
      <button type="submit" class="btn-admin">
        🚀 Admin Belépés
      </button>
    </form>

    <div class="security-notice">
      🛡️ Csak admin felhasználóknak!<br>
      Minden művelet naplózva van.
    </div>
    
    <div class="back-link">
      <a href="login.php">← Vissza a normál bejelentkezéshez</a>
    </div>
  </div>

</body>
</html>