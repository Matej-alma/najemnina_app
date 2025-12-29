<?php
declare(strict_types=1);

function b64url_encode(string $data): string {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function b64url_decode(string $data): string {
  $remainder = strlen($data) % 4;
  if ($remainder) $data .= str_repeat('=', 4 - $remainder);
  return base64_decode(strtr($data, '-_', '+/')) ?: '';
}

function jwt_sign(array $payload, string $secret): string {
  $header = ['alg' => 'HS256', 'typ' => 'JWT'];
  $h = b64url_encode(json_encode($header));
  $p = b64url_encode(json_encode($payload));
  $sig = hash_hmac('sha256', "$h.$p", $secret, true);
  return "$h.$p." . b64url_encode($sig);
}

function jwt_verify(string $token, string $secret): array {
  $parts = explode('.', $token);
  if (count($parts) !== 3) return [];

  [$h, $p, $s] = $parts;
  $sig = b64url_decode($s);
  $expected = hash_hmac('sha256', "$h.$p", $secret, true);

  if (!hash_equals($expected, $sig)) return [];

  $payload = json_decode(b64url_decode($p), true);
  if (!is_array($payload)) return [];

  if (isset($payload['exp']) && time() >= (int)$payload['exp']) return [];

  return $payload;
}

function require_auth(array $config): array {
  $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
    json_response(401, ['error' => 'Missing Bearer token']);
  }

  $token = trim($m[1]);
  $payload = jwt_verify($token, $config['jwt']['secret']);

  if (!$payload || empty($payload['sub'])) {
    json_response(401, ['error' => 'Invalid or expired token']);
  }

  return $payload; // npr. ['sub'=>1, ...]
}
