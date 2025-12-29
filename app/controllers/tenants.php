<?php
function apt_owned(PDO $pdo, int $apartmentId, int $userId): bool {
  $st = $pdo->prepare('SELECT id FROM apartments WHERE id=? AND user_id=? LIMIT 1');
  $st->execute([$apartmentId,$userId]);
  return (bool)$st->fetch();
}

function tenants_list(PDO $pdo, int $userId, int $apartmentId): void {
  if (!apt_owned($pdo,$apartmentId,$userId)) json_out(404, ['error'=>'Apartment not found']);

  $active = $_GET['active'] ?? null;
  if ($active === null) {
    $st = $pdo->prepare('SELECT * FROM tenants WHERE apartment_id=? ORDER BY id DESC');
    $st->execute([$apartmentId]);
  } else {
    $is = ((string)$active === '1') ? 1 : 0;
    $st = $pdo->prepare('SELECT * FROM tenants WHERE apartment_id=? AND is_active=? ORDER BY id DESC');
    $st->execute([$apartmentId,$is]);
  }
  json_out(200, ['items'=>$st->fetchAll()]);
}

function tenants_get(PDO $pdo, int $userId, int $tenantId): void {
  $st = $pdo->prepare('
    SELECT t.*
    FROM tenants t
    JOIN apartments a ON a.id=t.apartment_id
    WHERE t.id=? AND a.user_id=?
    LIMIT 1
  ');
  $st->execute([$tenantId,$userId]);
  $row = $st->fetch();
  if (!$row) json_out(404, ['error'=>'Tenant not found']);
  json_out(200, ['item'=>$row]);
}

function tenants_create(PDO $pdo, int $userId, int $apartmentId): void {
  if (!apt_owned($pdo,$apartmentId,$userId)) json_out(404, ['error'=>'Apartment not found']);

  $b = body();
  $first = trim((string)($b['first_name'] ?? ''));
  $last  = trim((string)($b['last_name'] ?? ''));
  $email = isset($b['email']) ? trim((string)$b['email']) : null;
  $phone = isset($b['phone']) ? trim((string)$b['phone']) : null;
  $in    = isset($b['move_in_date']) ? trim((string)$b['move_in_date']) : null;
  $out   = isset($b['move_out_date']) ? trim((string)$b['move_out_date']) : null;
  $active = array_key_exists('is_active',$b) ? (int)((bool)$b['is_active']) : 1;

  if ($first===''||$last==='') json_out(422, ['error'=>'Missing fields: first_name,last_name']);
  if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(422, ['error'=>'Invalid email']);
  if ($in && $out && $out < $in) json_out(422, ['error'=>'move_out_date must be >= move_in_date']);

  $st = $pdo->prepare('
    INSERT INTO tenants(apartment_id,first_name,last_name,email,phone,move_in_date,move_out_date,is_active)
    VALUES (?,?,?,?,?,?,?,?)
  ');
  $st->execute([$apartmentId,$first,$last,$email ?: null,$phone ?: null,$in ?: null,$out ?: null,$active]);

  json_out(201, ['id'=>(int)$pdo->lastInsertId()]);
}

function tenants_update(PDO $pdo, int $userId, int $tenantId): void {
  // ownership check
  $chk = $pdo->prepare('
    SELECT t.id
    FROM tenants t
    JOIN apartments a ON a.id=t.apartment_id
    WHERE t.id=? AND a.user_id=?
    LIMIT 1
  ');
  $chk->execute([$tenantId,$userId]);
  if (!$chk->fetch()) json_out(404, ['error'=>'Tenant not found']);

  $b = body();
  $first = trim((string)($b['first_name'] ?? ''));
  $last  = trim((string)($b['last_name'] ?? ''));
  $email = isset($b['email']) ? trim((string)$b['email']) : null;
  $phone = isset($b['phone']) ? trim((string)$b['phone']) : null;
  $in    = isset($b['move_in_date']) ? trim((string)$b['move_in_date']) : null;
  $out   = isset($b['move_out_date']) ? trim((string)$b['move_out_date']) : null;
  $active = array_key_exists('is_active',$b) ? (int)((bool)$b['is_active']) : 1;

  if ($first===''||$last==='') json_out(422, ['error'=>'Missing fields: first_name,last_name']);
  if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(422, ['error'=>'Invalid email']);
  if ($in && $out && $out < $in) json_out(422, ['error'=>'move_out_date must be >= move_in_date']);

  $st = $pdo->prepare('
    UPDATE tenants
    SET first_name=?,last_name=?,email=?,phone=?,move_in_date=?,move_out_date=?,is_active=?
    WHERE id=?
  ');
  $st->execute([$first,$last,$email ?: null,$phone ?: null,$in ?: null,$out ?: null,$active,$tenantId]);

  json_out(200, ['message'=>'Updated']);
}

function tenants_delete(PDO $pdo, int $userId, int $tenantId): void {
  $st = $pdo->prepare('
    DELETE t FROM tenants t
    JOIN apartments a ON a.id=t.apartment_id
    WHERE t.id=? AND a.user_id=?
  ');
  $st->execute([$tenantId,$userId]);
  if ($st->rowCount()===0) json_out(404, ['error'=>'Tenant not found']);
  json_out(200, ['message'=>'Deleted']);
}
