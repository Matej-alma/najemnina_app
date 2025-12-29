<?php
require 'api.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $res = api_request('POST', '/auth/login', [
    'login' => $_POST['login'] ?? '',
    'password' => $_POST['password'] ?? ''
  ]);

  if (!empty($res['token'])) {
    $_SESSION['token'] = $res['token'];
    header('Location: dashboard.php');
    exit;
  }

  $error = $res['error'] ?? 'Napaka pri prijavi';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="center-page">
    <div class="app narrow">
      <div class="card login">
        <div class="logo-wrap">
          <img class="logo" src="assets/logo.png" alt="Moja Najemnina">
        </div>

        <h2>Prijava</h2>

        <?php if (!empty($error)): ?>
          <p style="color:var(--danger); margin:0 0 10px;">
            <?= htmlspecialchars($error) ?>
          </p>
        <?php endif; ?>

        <form method="post">
          <input name="login" placeholder="Uporabniško ime" required>
          <input type="password" name="password" placeholder="Geslo" required>
          <button>Prijava</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
