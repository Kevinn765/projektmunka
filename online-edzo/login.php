<?php
session_start();
require_once 'db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Add meg az emailt és a jelszót.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
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
  <title>Bejelentkezés - Online Edző</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0d1117, #161b22, #1b263b);
      color: #e6edf3;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      backdrop-filter: blur(12px);
      padding: 40px;
      border-radius: 20px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    h2 {
      text-align: center;
      color: #58a6ff;
      margin-bottom: 30px;
      font-weight: 600;
    }
    .form-control {
      background: rgba(255,255,255,0.08);
      border: none;
      color: #fff;
      border-radius: 10px;
      padding: 12px;
    }
    .form-control:focus {
      box-shadow: 0 0 5px #238636;
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    .form-control::placeholder {
      color: #9ca3af;
    }
    .btn-main {
      width: 100%;
      background-color: #238636;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 10px;
      font-weight: 600;
      transition: 0.2s;
    }
    .btn-main:hover {
      background-color: #2ea043;
    }
    .alert {
      background-color: rgba(255,0,0,0.2);
      border: 1px solid rgba(255,0,0,0.3);
      color: #ff6b6b;
      text-align: center;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 20px;
    }
    .extra-links {
      text-align: center;
      margin-top: 15px;
    }
    .extra-links a {
      color: #9bbcff;
      text-decoration: none;
    }
    .extra-links a:hover {
      color: #58a6ff;
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2>Bejelentkezés</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">📧 Email cím</label>
        <input type="email" name="email" class="form-control" placeholder="pelda@email.hu" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">🔑 Jelszó</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-main">🚀 Bejelentkezés</button>
    </form>

    <div class="extra-links">
      <p>Még nincs fiókod? <a href="register.php">Regisztrálj itt!</a></p>
      <p>Elfelejtetted a jelszavad? <a href="forgot_password.php">Kattints ide!</a></p>    
    </div>
  </div>

</body>
</html>