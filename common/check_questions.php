<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// 检查题目表
try {
    $stmt = $db->prepare('SELECT group_id, COUNT(*) as count FROM questions GROUP BY group_id');
    $result = $stmt->execute();
    $results = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $results[] = $row;
    }
    
    echo "题目统计：\n";
    foreach ($results as $row) {
        echo "分组ID: {$row['group_id']}, 题目数量: {$row['count']}\n";
    }
    
    // 检查特定分组
    $groupId = $_GET['group_id'] ?? 1; // 允许通过URL参数指定分组ID
    $stmt = $db->prepare('SELECT * FROM questions WHERE group_id = :group_id');
    $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $questions = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $questions[] = $row;
    }
    
    echo "\n分组 $groupId 的题目：\n";
    foreach ($questions as $q) {
        echo "ID: {$q['id']}, 题目: {$q['question']}\n";
    }
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
} 