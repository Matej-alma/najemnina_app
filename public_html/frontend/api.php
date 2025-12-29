<?php
session_start();

define('API_BASE', 'https://najemnina.telebajsek.si/index.php/api');

function api_request(string $method, string $endpoint, array $data = null) {
  $ch = curl_init(API_BASE . $endpoint);

  $headers = ['Content-Type: application/json'];
  if (!empty($_SESSION['token'])) {
    $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
  }

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => $method,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_POSTFIELDS     => $data ? json_encode($data) : null
  ]);

  $res = curl_exec($ch);
  curl_close($ch);

  return json_decode($res, true);
}
