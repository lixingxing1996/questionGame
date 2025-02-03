<?php
// 将原config.php的内容移动到这里 

// 检查数据库目录是否存在
$dbDir = __DIR__ . '/../data';
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

// 定义数据库路径
define('DB_PATH', $dbDir . '/game.db');

// 创建数据库连接
try {
    $db = new SQLite3(DB_PATH);
    $db->enableExceptions(true);
} catch (Exception $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 启动会话
session_start();

// 获取数据库连接
function getDB() {
    global $db;
    if (!$db) {
        $db = new SQLite3(DB_PATH);
        $db->enableExceptions(true);
    }
    return $db;
} 