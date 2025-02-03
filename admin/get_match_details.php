<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => '无权限']);
    exit();
}

$match_id = $_GET['id'] ?? 0;

if (!$match_id) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit();
}

try {
    $db = getDB();
    $query = "
        SELECT 
            m.*,
            g.name as group_name,
            s1.name as student1_name,
            s2.name as student2_name,
            w.name as winner_name
        FROM matches m
        LEFT JOIN groups g ON m.group_id = g.id
        LEFT JOIN students s1 ON m.student1_id = s1.id
        LEFT JOIN students s2 ON m.student2_id = s2.id
        LEFT JOIN students w ON m.winner_id = w.id
        WHERE m.id = ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $match_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $match = $result->fetchArray(SQLITE3_ASSOC);
    
    if (!$match) {
        echo json_encode(['success' => false, 'message' => '未找到比赛记录']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'match' => $match
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '获取详情失败：' . $e->getMessage()
    ]);
} 