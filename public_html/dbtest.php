<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$appRoot = __DIR__ . '/../najemina_api'; // pazi: najemina_api (brez drugega n)

$configPath = $appRoot . '/src/config.php';
$dbPath     = $appRoot . '/src/db.php';

if (!file_exists($configPath)) {
  http_response_code(500);
  echo json_encode(['error' => 'Missing config.php', 'path' => $configPath], JSON_UNESCAPED_UNICODE);
  exit;
}
if (!file_exists($dbPath)) {
  http_response_code(500);
  echo json_encode(['error' => 'Missing db.php', 'path' => $dbPath], JSON_UNESCAPED_UNICODE);
  exit;
}

$config = require $configPath;
require $dbPath;

try {
  $pdo = db($config);

  // osnovni query
  $pdo->query('SELECT 1')->fetch();

// preveri tabele (information_schema - deluje na MySQL/MariaDB)
  $tables = [];
  $dbName = (string)($config['db']['name'] ?? '');

  $st = $pdo->prepare("
    SELECT COUNT(*) AS cnt
    FROM information_schema.tables
    WHERE table_schema = ? AND table_name = ?
  ");

  foreach (['users','apartments','tenants','expenses'] as $t) {
    $st->execute([$dbName, $t]);
    $cnt = (int)($st->fetch()['cnt'] ?? 0);
    $tables[$t] = $cnt > 0;
  }
  
  echo json_encode([
    'ok' => true,
    'db_host' => $config['db']['host'] ?? null,
    'db_name' => $config['db']['name'] ?? null,
    'tables' => $tables
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'DB error',
    'message' => $e->getMessage(),
  ], JSON_UNESCAPED_UNICODE);
}
