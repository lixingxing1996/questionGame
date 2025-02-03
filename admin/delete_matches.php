<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => '无权限']);
    exit();
}

// 获取要删除的比赛ID数组
$match_ids = json_decode($_POST['match_ids'] ?? '[]');

if (empty($match_ids)) {
    echo json_encode(['success' => false, 'message' => '未选择要删除的记录']);
    exit();
}

try {
    $db = getDB();
    $db->exec('BEGIN');
    
    $placeholders = str_repeat('?,', count($match_ids) - 1) . '?';
    $query = "DELETE FROM matches WHERE id IN ($placeholders)";
    
    $stmt = $db->prepare($query);
    $index = 1;
    foreach ($match_ids as $id) {
        $stmt->bindValue($index++, $id, SQLITE3_INTEGER);
    }
    
    $stmt->execute();
    $db->exec('COMMIT');
    
    echo json_encode([
        'success' => true,
        'message' => '成功删除 ' . count($match_ids) . ' 条记录'
    ]);
} catch (Exception $e) {
    $db->exec('ROLLBACK');
    echo json_encode([
        'success' => false,
        'message' => '删除失败：' . $e->getMessage()
    ]);
} 