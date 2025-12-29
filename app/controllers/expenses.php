<?php
function expenses_list(PDO $pdo, int $userId, int $apartmentId): void {
  $st = $pdo->prepare('SELECT id FROM apartments WHERE id=? AND user_id=? LIMIT 1');
  $st->execute([$apartmentId,$userId]);
  if (!$st->fetch()) json_out(404, ['error'=>'Apartment not found']);

  $from = $_GET['from'] ?? null;
  $to   = $_GET['to'] ?? null;
  $type = $_GET['type'] ?? null;

  $sql = 'SELECT * FROM expenses WHERE apartment_id=?';
  $params = [$apartmentId];

  if ($from) { $sql .= ' AND expense_date >= ?'; $params[] = $from; }
  if ($to)   { $sql .= ' AND expense_date <= ?'; $params[] = $to; }
  if ($type) { $sql .= ' AND type = ?';          $params[] = $type; }

  $sql .= ' ORDER BY expense_date DESC, id DESC';
  $q = $pdo->prepare($sql);
  $q->execute($params);

  json_out(200, ['items'=>$q->fetchAll()]);
}

function expenses_get(PDO $pdo, int $userId, int $expenseId): void {
  $st = $pdo->prepare('
    SELECT e.*
    FROM expenses e
    JOIN apartments a ON a.id=e.apartment_id
    WHERE e.id=? AND a.user_id=?
    LIMIT 1
  ');
  $st->execute([$expenseId,$userId]);
  $row = $st->fetch();
  if (!$row) json_out(404, ['error'=>'Expense not found']);
  json_out(200, ['item'=>$row]);
}

function expenses_create(PDO $pdo, int $userId, int $apartmentId): void {
  $st = $pdo->prepare('SELECT id FROM apartments WHERE id=? AND user_id=? LIMIT 1');
  $st->execute([$apartmentId,$userId]);
  if (!$st->fetch()) json_out(404, ['error'=>'Apartment not found']);

  $b = body();
  $date = trim((string)($b['expense_date'] ?? ''));
  $type = trim((string)($b['type'] ?? ''));
  $amount = $b['amount'] ?? null;
  $desc = isset($b['description']) ? trim((string)$b['description']) : null;

  if ($date===''||$type===''||$amount===null) json_out(422, ['error'=>'Missing fields: expense_date,type,amount']);
  if (!is_numeric($amount) || (float)$amount <= 0) json_out(422, ['error'=>'amount must be > 0']);

  $ins = $pdo->prepare('INSERT INTO expenses(apartment_id,expense_date,type,amount,description) VALUES (?,?,?,?,?)');
  $ins->execute([$apartmentId,$date,$type,(float)$amount,$desc ?: null]);

  json_out(201, ['id'=>(int)$pdo->lastInsertId()]);
}

function expenses_update(PDO $pdo, int $userId, int $expenseId): void {
  $chk = $pdo->prepare('
    SELECT e.id
    FROM expenses e
    JOIN apartments a ON a.id=e.apartment_id
    WHERE e.id=? AND a.user_id=?
    LIMIT 1
  ');
  $chk->execute([$expenseId,$userId]);
  if (!$chk->fetch()) json_out(404, ['error'=>'Expense not found']);

  $b = body();
  $date = trim((string)($b['expense_date'] ?? ''));
  $type = trim((string)($b['type'] ?? ''));
  $amount = $b['amount'] ?? null;
  $desc = isset($b['description']) ? trim((string)$b['description']) : null;

  if ($date===''||$type===''||$amount===null) json_out(422, ['error'=>'Missing fields: expense_date,type,amount']);
  if (!is_numeric($amount) || (float)$amount <= 0) json_out(422, ['error'=>'amount must be > 0']);

  $up = $pdo->prepare('UPDATE expenses SET expense_date=?,type=?,amount=?,description=? WHERE id=?');
  $up->execute([$date,$type,(float)$amount,$desc ?: null,$expenseId]);

  json_out(200, ['message'=>'Updated']);
}

function expenses_delete(PDO $pdo, int $userId, int $expenseId): void {
  $st = $pdo->prepare('
    DELETE e FROM expenses e
    JOIN apartments a ON a.id=e.apartment_id
    WHERE e.id=? AND a.user_id=?
  ');
  $st->execute([$expenseId,$userId]);
  if ($st->rowCount()===0) json_out(404, ['error'=>'Expense not found']);
  json_out(200, ['message'=>'Deleted']);
}
