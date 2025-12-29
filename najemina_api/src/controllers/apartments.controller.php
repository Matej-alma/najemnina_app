<?php
declare(strict_types=1);

function apartments_list(PDO $pdo, int $userId): void {
  $st = $pdo->prepare('SELECT id, title, address, city, note, created_at
                       FROM apartments WHERE user_id = ? ORDER BY id DESC');
  $st->execute([$userId]);
  json_response(200, ['items' => $st->fetchAll()]);
}

function apartments_get(PDO $pdo, int $userId, int $id): void {
  $st = $pdo->prepare('SELECT id, title, address, city, note, created_at
                       FROM apartments WHERE id = ? AND user_id = ? LIMIT 1');
  $st->execute([$id, $userId]);
  $row = $st->fetch();
  if (!$row) json_response(404, ['error' => 'Apartment not found']);
  json_response(200, ['item' => $row]);
}

function apartments_create(PDO $pdo, int $userId): void {
  $b = read_json_body();
  $title = trim((string)($b['title'] ?? ''));
  $address = trim((string)($b['address'] ?? ''));
  $city = trim((string)($b['city'] ?? ''));
  $note = isset($b['note']) ? trim((string)$b['note']) : null;

  if ($title === '' || $address === '' || $city === '') {
    json_response(422, ['error' => 'Missing fields: title, address, city']);
  }

  $st = $pdo->prepare('INSERT INTO apartments (user_id, title, address, city, note) VALUES (?, ?, ?, ?, ?)');
  $st->execute([$userId, $title, $address, $city, $note]);

  json_response(201, ['id' => (int)$pdo->lastInsertId()]);
}

function apartments_update(PDO $pdo, int $userId, int $id): void {
  // preveri lastništvo
  $chk = $pdo->prepare('SELECT id FROM apartments WHERE id = ? AND user_id = ? LIMIT 1');
  $chk->execute([$id, $userId]);
  if (!$chk->fetch()) json_response(404, ['error' => 'Apartment not found']);

  $b = read_json_body();
  $title = trim((string)($b['title'] ?? ''));
  $address = trim((string)($b['address'] ?? ''));
  $city = trim((string)($b['city'] ?? ''));
  $note = array_key_exists('note', $b) ? (trim((string)$b['note']) ?: null) : null;

  if ($title === '' || $address === '' || $city === '') {
    json_response(422, ['error' => 'Missing fields: title, address, city']);
  }

  $st = $pdo->prepare('UPDATE apartments SET title=?, address=?, city=?, note=? WHERE id=? AND user_id=?');
  $st->execute([$title, $address, $city, $note, $id, $userId]);

  json_response(200, ['message' => 'Updated']);
}

function apartments_delete(PDO $pdo, int $userId, int $id): void {
  $st = $pdo->prepare('DELETE FROM apartments WHERE id = ? AND user_id = ?');
  $st->execute([$id, $userId]);
  if ($st->rowCount() === 0) json_response(404, ['error' => 'Apartment not found']);
  json_response(200, ['message' => 'Deleted']);
}
