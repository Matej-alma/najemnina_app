<?php
require 'api.php';
if (empty($_SESSION['token'])) { header('Location: login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) api_request('DELETE', "/apartments/$id");
header('Location: dashboard.php');
