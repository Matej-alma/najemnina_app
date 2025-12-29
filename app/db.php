<?php
function db(array $cfg): PDO {
  $db = $cfg['db'];
  $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
  return new PDO($dsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
}
