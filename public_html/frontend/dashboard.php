<?php
require 'api.php';

if (empty($_SESSION['token'])) {
  header('Location: login.php');
  exit;
}

$error = null;

// CREATE apartment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_apartment') {
  $res = api_request('POST', '/apartments', [
    'title' => $_POST['title'] ?? '',
    'address' => $_POST['address'] ?? '',
    'city' => $_POST['city'] ?? '',
    'note' => $_POST['note'] ?? ''
  ]);

  if (!empty($res['id'])) {
    header('Location: apartment.php?id=' . urlencode($res['id']));
    exit;
  }
  $error = $res['error'] ?? 'Napaka pri dodajanju stanovanja';
}

$apartments = api_request('GET', '/apartments');
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
  <h1>Moja stanovanja</h1>

  <?php if (!empty($error)): ?>
    <div class="card soft">
      <p style="color:var(--danger);margin:0"><?= htmlspecialchars($error) ?></p>
    </div>
  <?php endif; ?>

  <div class="card soft">
    <h2>+ Dodaj stanovanje</h2>
    <form method="post">
      <input type="hidden" name="action" value="create_apartment">
      <input name="title" placeholder="Naziv (npr. Stanovanje Center)" required>
      <input name="address" placeholder="Naslov" required>
      <input name="city" placeholder="Mesto" required>
      <input name="note" placeholder="Opomba (neobvezno)">
      <button>Dodaj</button>
    </form>
  </div>

  <?php foreach (($apartments['items'] ?? []) as $a): ?>
    <a href="apartment.php?id=<?= (int)$a['id'] ?>" style="text-decoration:none;color:inherit">
      <div class="card">
        <strong><?= htmlspecialchars($a['title']) ?></strong>
        <div style="margin-top:6px;color:var(--muted);font-size:14px">
          <?= htmlspecialchars($a['address']) ?>, <?= htmlspecialchars($a['city']) ?>
        </div>
        <?php if (!empty($a['note'])): ?>
          <div style="margin-top:8px;color:var(--muted);font-size:13px">
            <?= htmlspecialchars($a['note']) ?>
          </div>
        <?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>

  <div class="nav">
    <a href="logout.php" style="flex:1">
      <button class="secondary">Odjava</button>
    </a>
  </div>
</div>
</body>
</html>
