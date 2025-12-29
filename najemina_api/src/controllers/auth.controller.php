<?php
declare(strict_types=1);

function auth_register(PDO $pdo, array $config): void {
  $body = read_json_body();

  $first = trim((string)($body['first_name'] ?? ''));
  $last  = trim((string)($body['last_name'] ?? ''));
  $email = trim((string)($body['email'] ?? ''));
  $usern = trim((string)($body['username'] ?? ''));
  $pass  = (string)($body['password'] ?? '');

  if ($first === '' || $last === '' || $email === '' || $usern === '' || $pass === '') {
    json_response(422, ['error' => 'Missing fields']);
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(422, ['error' => 'Invalid email']);
  }
  if (strlen($pass) < 6) {
    json_response(422, ['error' => 'Password too short (min 6)']);
  }

  $st = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
  $st->execute([$email, $usern]);
  if ($st->fetch()) {
    json_response(409, ['error' => 'Email or username already exists']);
  }

  $hash = password_hash($pass, PASSWORD_DEFAULT);

  $ins = $pdo->prepare('
    INSERT INTO users (first_name, last_name, email, username, password_hash)
    VALUES (?, ?, ?, ?, ?)
  ');
  $ins->execute([$first, $last, $email, $usern, $hash]);

  json_response(201, ['message' => 'User created']);
}

function auth_login(PDO $pdo, array $config): void {
  $body = read_json_body();
  $login = trim((string)($body['login'] ?? '')); // email ali username
  $pass  = (string)($body['password'] ?? '');

  if ($login === '' || $pass === '') {
    json_response(422, ['error' => 'Missing login/password']);
  }

  $st = $pdo->prepare('
    SELECT id, password_hash, first_name, last_name, email, username
    FROM users
    WHERE email = ? OR username = ?
    LIMIT 1
  ');
  $st->execute([$login, $login]);
  $u = $st->fetch();

  if (!$u || !password_verify($pass, $u['password_hash'])) {
    json_response(401, ['error' => 'Invalid credentials']);
  }

  $now = time();
  $payload = [
    'sub' => (int)$u['id'],
    'iat' => $now,
    'exp' => $now + (int)$config['jwt']['ttl_seconds'],
    'iss' => (string)$config['jwt']['issuer'],
  ];

  $token = jwt_sign($payload, $config['jwt']['secret']);

  json_response(200, [
    'token' => $token,
    'user' => [
      'id' => (int)$u['id'],
      'first_name' => $u['first_name'],
      'last_name' => $u['last_name'],
      'email' => $u['email'],
      'username' => $u['username'],
    ],
  ]);
}

function auth_me(PDO $pdo, array $config, array $jwt): void {
  $userId = (int)$jwt['sub'];

  $st = $pdo->prepare('SELECT id, first_name, last_name, email, username, created_at FROM users WHERE id = ?');
  $st->execute([$userId]);
  $u = $st->fetch();

  if (!$u) json_response(404, ['error' => 'User not found']);

  json_response(200, ['user' => $u]);
}
