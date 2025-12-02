<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $errors[] = 'Minden mező kitöltése kötelező.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Érvénytelen email cím.';
    } elseif ($password !== $confirm) {
        $errors[] = 'A jelszavak nem egyeznek.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Ez az email már regisztrálva van.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $hash]);

// Automatikus bejelentkezés
$new_user_id = $pdo->lastInsertId();
$_SESSION['user_id'] = $new_user_id;
$_SESSION['username'] = $username;

// Átirányítás onboarding-ra
header('Location: onboarding.php');
exit;
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Regisztráció - Online Edző</title>
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
    .register-card {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      backdrop-filter: blur(12px);
      padding: 40px;
      border-radius: 20px;
      width: 100%;
      max-width: 420px;
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
    .form-control::placeholder {
      color: #aaa;
    }
    .form-control:focus {
      box-shadow: 0 0 5px #238636;
      background: rgba(255,255,255,0.1);
      color: #fff;
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
    }
    .alert-success {
      background-color: rgba(0,255,0,0.15);
      border: 1px solid rgba(0,255,0,0.3);
      color: #7fffd4;
      text-align: center;
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

  <div class="register-card">
    <h2>Regisztráció</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert"><?= htmlspecialchars(implode('<br>', $errors)) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Felhasználónév</label>
        <input type="text" name="username" class="form-control" required placeholder="Pl. FitJani">
      </div>
      <div class="mb-3">
        <label class="form-label">Email cím</label>
        <input type="email" name="email" class="form-control" required placeholder="pelda@valami.hu">
      </div>
      <div class="mb-3">
        <label class="form-label">Jelszó</label>
        <input type="password" name="password" class="form-control" required placeholder="********">
      </div>
      <div class="mb-3">
        <label class="form-label">Jelszó megerősítése</label>
        <input type="password" name="confirm" class="form-control" required placeholder="********">
      </div>
      <button type="submit" class="btn-main">Regisztráció</button>
    </form>

    <div class="extra-links">
      <p>Már van fiókod? <a href="login.php">Jelentkezz be!</a></p>
    </div>
  </div>

</body>
</html>
