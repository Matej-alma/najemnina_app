<?php
function b64u_enc(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64u_dec(string $s): string {
  $r = strlen($s) % 4; if ($r) $s .= str_repeat('=', 4 - $r);
  return base64_decode(strtr($s, '-_', '+/')) ?: '';
}

function jwt_sign(array $payload, string $secret): string {
  $h = b64u_enc(json_encode(['alg'=>'HS256','typ'=>'JWT']));
  $p = b64u_enc(json_encode($payload));
  $sig = hash_hmac('sha256', "$h.$p", $secret, true);
  return "$h.$p." . b64u_enc($sig);
}

function jwt_verify(string $token, string $secret): array {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return [];
  [$h,$p,$s] = $parts;
  $sig = b64u_dec($s);
  $exp = hash_hmac('sha256', "$h.$p", $secret, true);
  if (!hash_equals($exp, $sig)) return [];
  $pl = json_decode(b64u_dec($p), true);
  if (!is_array($pl)) return [];
  if (isset($pl['exp']) && time() >= (int)$pl['exp']) return [];
  return $pl;
}
