<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$ref = trim($_GET['ref'] ?? '');

if (!preg_match('/^LNC-\d{4}-[A-Z0-9]{5}$/', $ref)) {
  echo json_encode(['status' => 'unknown']);
  exit;
}

$db = lnc_db();
if (!$db) {
  echo json_encode(['status' => 'unknown']);
  exit;
}

$row = lnc_get_booking($ref);
if (!$row) {
  echo json_encode(['status' => 'unknown']);
  exit;
}

echo json_encode(['status' => $row['status']]);
