<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$appRoot = __DIR__ . '/../najemina_api'; // <-- točno tako (ena n)

$config = require $appRoot . '/src/config.php';
require $appRoot . '/src/db.php';

try {
  $pdo = db($config);

  $first = 'Matej';
  $last  = 'Horvat';
  $email = 'matej' . time() . '@example.com';
  $usern = 'matej' . time();
  $pass  = 'test1234';
  $hash  = password_hash($pass, PASSWORD_DEFAULT);

  // test: ali tabela users res dela (insert)
  $ins = $pdo->prepare('
    INSERT INTO users (first_name, last_name, email, username, password_hash)
    VALUES (?, ?, ?, ?, ?)
  ');
  $ins->execute([$first, $last, $email, $usern, $hash]);

  echo json_encode([
    'ok' => true,
    'inserted_user_id' => (int)$pdo->lastInsertId(),
    'email' => $email,
    'username' => $usern
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'type' => get_class($e),
    'message' => $e->getMessage(),
  ], JSON_UNESCAPED_UNICODE);
}
