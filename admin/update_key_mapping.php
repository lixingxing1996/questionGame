<?php
require_once('../common/config.php');
require_once('../common/functions.php');

if (!is_admin()) {
    die(json_encode(['success' => false]));
}

$position = $_POST['position'] ?? '';
$keyCode = $_POST['key_code'] ?? '';

if (empty($position) || empty($keyCode)) {
    die(json_encode(['success' => false]));
}

$success = updateKeyMapping($position, $keyCode);
echo json_encode(['success' => $success]); 