<?php
require_once('../common/config.php');
require_once('../common/functions.php');

if (!is_admin()) {
    die(json_encode(['success' => false, 'message' => '未授权的操作']));
}

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$alias = $_POST['alias'] ?? '';

if (!$id || empty($name)) {
    die(json_encode(['success' => false, 'message' => '参数错误']));
}

$success = updateGroup($id, $name, $alias);
echo json_encode(['success' => $success]); 