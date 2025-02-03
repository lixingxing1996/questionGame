<?php
require_once('../common/config.php');
require_once('../common/functions.php');

header('Content-Type: application/json');

// 检查管理员权限
if (!is_admin()) {
    die(json_encode(['success' => false, 'message' => '未授权的操作']));
}

// 检查是否收到POST数据
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => '请求方法错误']));
}

$matchId = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
if (!$matchId) {
    die(json_encode(['success' => false, 'message' => '比赛ID无效']));
}

try {
    $db = getDB();
    
    // 先检查比赛是否存在
    $checkStmt = $db->prepare('SELECT id FROM matches WHERE id = :id');
    $checkStmt->bindValue(':id', $matchId, SQLITE3_INTEGER);
    $result = $checkStmt->execute();
    
    if (!$result->fetchArray()) {
        die(json_encode(['success' => false, 'message' => '比赛记录不存在']));
    }
    
    // 删除比赛记录
    $deleteStmt = $db->prepare('DELETE FROM matches WHERE id = :id');
    $deleteStmt->bindValue(':id', $matchId, SQLITE3_INTEGER);
    $result = $deleteStmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => '删除成功']);
    } else {
        throw new Exception($db->lastErrorMsg());
    }
    
} catch (Exception $e) {
    error_log("Delete match error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => '删除失败：' . $e->getMessage()
    ]);
} 