<?php
function require_user(array $cfg): array {
  $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/^Bearer\s+(.+)$/i', $h, $m)) {
    json_out(401, ['error'=>'Missing Bearer token']);
  }
  $pl = jwt_verify(trim($m[1]), $cfg['jwt']['secret']);
  if (!$pl || empty($pl['sub'])) json_out(401, ['error'=>'Invalid or expired token']);
  return $pl;
}
