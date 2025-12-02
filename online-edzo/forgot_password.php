<?php
session_start();
require_once 'db.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'Add meg az email címedet.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Új ideiglenes jelszó
            $newPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789'), 0, 10);
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
            $update->execute([$newHash, $email]);

            $message = "Az új ideiglenes jelszavad: <strong>{$newPassword}</strong><br>Bejelentkezés után megváltoztathatod.";
        } else {
            $error = 'Nincs ilyen email cím a rendszerben.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Elfelejtett jelszó - Online Edző</title>
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
    .forgot-card {
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
      text-align: center;
      border-radius: 10px;
      padding: 10px;
    }
    .alert-success {
      background-color: rgba(0,255,0,0.15);
      border: 1px solid rgba(0,255,0,0.3);
      color: #7fffd4;
    }
    .alert-danger {
      background-color: rgba(255,0,0,0.2);
      border: 1px solid rgba(255,0,0,0.3);
      color: #ff6b6b;
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

  <div class="forgot-card">
    <h2>Elfelejtett jelszó</h2>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email cím</label>
        <input type="email" name="email" class="form-control" placeholder="pelda@valami.hu" required>
      </div>
      <button type="submit" class="btn-main">Új jelszó kérése</button>
    </form>

    <div class="extra-links">
      <p><a href="login.php">⬅ Vissza a bejelentkezéshez</a></p>
    </div>
  </div>

</body>
</html>
