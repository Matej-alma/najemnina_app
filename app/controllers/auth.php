<?php
function auth_register(PDO $pdo, array $cfg): void {
  $b = body();
  $first = trim((string)($b['first_name'] ?? ''));
  $last  = trim((string)($b['last_name'] ?? ''));
  $email = trim((string)($b['email'] ?? ''));
  $user  = trim((string)($b['username'] ?? ''));
  $pass  = (string)($b['password'] ?? '');

  if ($first===''||$last===''||$email===''||$user===''||$pass==='') json_out(422,['error'=>'Missing fields']);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(422,['error'=>'Invalid email']);
  if (strlen($pass) < 6) json_out(422,['error'=>'Password too short']);

  $st = $pdo->prepare('SELECT id FROM users WHERE email=? OR username=? LIMIT 1');
  $st->execute([$email,$user]);
  if ($st->fetch()) json_out(409,['error'=>'Email or username already exists']);

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $ins = $pdo->prepare('INSERT INTO users(first_name,last_name,email,username,password_hash) VALUES (?,?,?,?,?)');
  $ins->execute([$first,$last,$email,$user,$hash]);

  json_out(201,['message'=>'User created']);
}

function auth_login(PDO $pdo, array $cfg): void {
  $b = body();
  $login = trim((string)($b['login'] ?? ''));
  $pass  = (string)($b['password'] ?? '');
  if ($login===''||$pass==='') json_out(422,['error'=>'Missing login/password']);

  $st = $pdo->prepare('SELECT id,password_hash,first_name,last_name,email,username FROM users WHERE email=? OR username=? LIMIT 1');
  $st->execute([$login,$login]);
  $u = $st->fetch();
  if (!$u || !password_verify($pass, $u['password_hash'])) json_out(401,['error'=>'Invalid credentials']);

  $now = time();
  $payload = ['sub'=>(int)$u['id'],'iat'=>$now,'exp'=>$now+(int)$cfg['jwt']['ttl'],'iss'=>$cfg['jwt']['issuer']];
  $token = jwt_sign($payload, $cfg['jwt']['secret']);

  json_out(200,['token'=>$token,'user'=>[
    'id'=>(int)$u['id'],'first_name'=>$u['first_name'],'last_name'=>$u['last_name'],
    'email'=>$u['email'],'username'=>$u['username']
  ]]);
}

function auth_me(PDO $pdo, array $cfg, array $jwt): void {
  $id = (int)$jwt['sub'];
  $st = $pdo->prepare('SELECT id,first_name,last_name,email,username,created_at FROM users WHERE id=?');
  $st->execute([$id]);
  $u = $st->fetch();
  if (!$u) json_out(404,['error'=>'User not found']);
  json_out(200,['user'=>$u]);
}
