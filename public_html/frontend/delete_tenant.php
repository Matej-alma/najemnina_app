<?php
require 'api.php';
if (empty($_SESSION['token'])) { header('Location: login.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$apt = (int)($_GET['apt'] ?? 0);
if ($id > 0) api_request('DELETE', "/tenants/$id");
header('Location: apartment.php?id=' . $apt);
