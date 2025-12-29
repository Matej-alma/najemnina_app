<?php
// Always return something on fatal errors
register_shutdown_function(function () {
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR], true)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>'Fatal error','message'=>$e['message'],'file'=>$e['file'],'line'=>$e['line']], JSON_UNESCAPED_UNICODE);
    exit;
  }
});

$root = __DIR__ . '/../app';

require $root . '/config.php';
$cfg = require $root . '/config.php';

require $root . '/db.php';
require $root . '/response.php';
require $root . '/jwt.php';
require $root . '/auth.php';

require $root . '/controllers/auth.php';
require $root . '/controllers/apartments.php';
require $root . '/controllers/tenants.php';
require $root . '/controllers/expenses.php';

// CORS
header('Access-Control-Allow-Origin: ' . ($cfg['cors']['origin'] ?? '*'));
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

$pdo = db($cfg);

// path parsing supports /index.php/api/...
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if (strpos($path, '/index.php') === 0) $path = substr($path, strlen('/index.php'));
$path = rtrim($path, '/'); if ($path==='') $path='/';

// simple matcher
if (!function_exists('route_match')) {
  function route_match(string $re, string $path, array &$m = []): bool {
    if (preg_match($re, $path, $mm)) { $m = $mm; return true; }
    return false;
  }
}

// routes (public)
if ($method==='POST' && $path==='/api/auth/register') auth_register($pdo,$cfg);
if ($method==='POST' && $path==='/api/auth/login') auth_login($pdo,$cfg);

// routes (protected)
if (strpos($path, '/api/') === 0) {
  $jwt = require_user($cfg);
  $userId = (int)$jwt['sub'];

  if ($method==='GET' && $path==='/api/auth/me') auth_me($pdo,$cfg,$jwt);

  // apartments
  if ($method==='GET'  && $path==='/api/apartments') apartments_list($pdo,$userId);
  if ($method==='POST' && $path==='/api/apartments') apartments_create($pdo,$userId);

  $m=[];
  if (route_match('#^/api/apartments/(\d+)$#',$path,$m)) {
    $id=(int)$m[1];
    if ($method==='GET') apartments_get($pdo,$userId,$id);
    if ($method==='PUT') apartments_update($pdo,$userId,$id);
    if ($method==='DELETE') apartments_delete($pdo,$userId,$id);
  }

  // tenants by apartment
  if (route_match('#^/api/apartments/(\d+)/tenants$#',$path,$m)) {
    $aid=(int)$m[1];
    if ($method==='GET') tenants_list($pdo,$userId,$aid);
    if ($method==='POST') tenants_create($pdo,$userId,$aid);
  }

  // tenant by id
  if (route_match('#^/api/tenants/(\d+)$#',$path,$m)) {
    $tid=(int)$m[1];
    if ($method==='GET') tenants_get($pdo,$userId,$tid);
    if ($method==='PUT') tenants_update($pdo,$userId,$tid);
    if ($method==='DELETE') tenants_delete($pdo,$userId,$tid);
  }

  // expenses by apartment
  if (route_match('#^/api/apartments/(\d+)/expenses$#',$path,$m)) {
    $aid=(int)$m[1];
    if ($method==='GET') expenses_list($pdo,$userId,$aid);
    if ($method==='POST') expenses_create($pdo,$userId,$aid);
  }

  // expense by id
  if (route_match('#^/api/expenses/(\d+)$#',$path,$m)) {
    $eid=(int)$m[1];
    if ($method==='GET') expenses_get($pdo,$userId,$eid);
    if ($method==='PUT') expenses_update($pdo,$userId,$eid);
    if ($method==='DELETE') expenses_delete($pdo,$userId,$eid);
  }
}

json_out(404, ['error'=>'Not found','path'=>$path,'method'=>$method]);
