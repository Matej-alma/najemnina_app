<?php
require 'api.php';

if (empty($_SESSION['token'])) {
  header('Location: login.php');
  exit;
}

$apartmentId = (int)($_GET['id'] ?? 0);
if ($apartmentId <= 0) { header('Location: dashboard.php'); exit; }

$error = null;

// Add tenant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_tenant') {
  $res = api_request('POST', "/apartments/$apartmentId/tenants", [
    'first_name' => $_POST['first_name'] ?? '',
    'last_name'  => $_POST['last_name'] ?? '',
    'email'      => $_POST['email'] ?? '',
    'phone'      => $_POST['phone'] ?? '',
    'move_in_date' => $_POST['move_in_date'] ?? '',
    'is_active'  => true
  ]);
  if (empty($res['id'])) $error = $res['error'] ?? 'Napaka pri dodajanju najemnika';
}

// Add expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_expense') {
  $res = api_request('POST', "/apartments/$apartmentId/expenses", [
    'expense_date' => $_POST['expense_date'] ?? '',
    'type' => $_POST['type'] ?? '',
    'amount' => $_POST['amount'] ?? '',
    'description' => $_POST['description'] ?? ''
  ]);
  if (empty($res['id'])) $error = $res['error'] ?? 'Napaka pri dodajanju stroška';
}

$apartment = api_request('GET', "/apartments/$apartmentId");
$tenants   = api_request('GET', "/apartments/$apartmentId/tenants");
$expenses  = api_request('GET', "/apartments/$apartmentId/expenses");

$item = $apartment['item'] ?? null;
if (!$item) { header('Location: dashboard.php'); exit; }

function h($s){ return htmlspecialchars((string)$s); }
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">

  <div class="nav">
    <a href="dashboard.php" style="flex:1"><button style="background:#111">← Nazaj</button></a>
    <a href="delete_apartment.php?id=<?= $apartmentId ?>" style="flex:1"><button style="background:#dc2626">Izbriši</button></a>
  </div>

  <div class="card">
    <h2><?= h($item['title']) ?></h2>
    <div><?= h($item['address']) ?>, <?= h($item['city']) ?></div>
    <?php if (!empty($item['note'])): ?>
      <div style="margin-top:8px;color:#555"><?= h($item['note']) ?></div>
    <?php endif; ?>
  </div>

  <?php if (!empty($error)): ?>
    <div class="card"><p style="color:red;margin:0"><?= h($error) ?></p></div>
  <?php endif; ?>

  <!-- TENANTS -->
  <div class="card">
    <h2>Najemniki</h2>

    <?php foreach (($tenants['items'] ?? []) as $t): ?>
      <div style="padding:10px;border:1px solid #eee;border-radius:10px;margin-bottom:10px">
        <strong><?= h($t['first_name']) ?> <?= h($t['last_name']) ?></strong><br>
        <span style="color:#555"><?= h($t['email']) ?> <?= h($t['phone']) ?></span><br>
        <a href="delete_tenant.php?id=<?= (int)$t['id'] ?>&apt=<?= $apartmentId ?>" style="color:#dc2626;text-decoration:none">Izbriši</a>
      </div>
    <?php endforeach; ?>

    <h3 style="margin:12px 0 8px">+ Dodaj najemnika</h3>
    <form method="post">
      <input type="hidden" name="action" value="add_tenant">
      <input name="first_name" placeholder="Ime" required>
      <input name="last_name" placeholder="Priimek" required>
      <input name="email" placeholder="Email (neobvezno)">
      <input name="phone" placeholder="Telefon (neobvezno)">
      <input name="move_in_date" placeholder="Datum vselitve (YYYY-MM-DD)">
      <button>Dodaj najemnika</button>
    </form>
  </div>

  <!-- EXPENSES -->
  <div class="card">
    <h2>Stroški</h2>

    <?php foreach (($expenses['items'] ?? []) as $e): ?>
      <div style="padding:10px;border:1px solid #eee;border-radius:10px;margin-bottom:10px">
        <strong><?= h($e['type']) ?> – <?= h($e['amount']) ?> €</strong><br>
        <span style="color:#555"><?= h($e['expense_date']) ?> · <?= h($e['description']) ?></span><br>
        <a href="delete_expense.php?id=<?= (int)$e['id'] ?>&apt=<?= $apartmentId ?>" style="color:#dc2626;text-decoration:none">Izbriši</a>
      </div>
    <?php endforeach; ?>

    <h3 style="margin:12px 0 8px">+ Dodaj strošek</h3>
    <form method="post">
      <input type="hidden" name="action" value="add_expense">
      <input name="expense_date" placeholder="Datum (YYYY-MM-DD)" required>
      <input name="type" placeholder="Tip (npr. electricity, water...)" required>
      <input name="amount" placeholder="Znesek (npr. 55.20)" required>
      <input name="description" placeholder="Opis (neobvezno)">
      <button>Dodaj strošek</button>
    </form>
  </div>

</div>
</body>
</html>
