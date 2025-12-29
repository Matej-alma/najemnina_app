<?php
function json_out(int $code, array $data): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function body(): array {
  $raw = file_get_contents('php://input') ?: '';
  $j = json_decode($raw, true);
  if (is_array($j)) return $j;
  if (!empty($_POST) && is_array($_POST)) return $_POST;
  return [];
}
