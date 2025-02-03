<?php
require_once 'common/config.php';    // 确保这个文件包含了数据库连接
require_once 'common/functions.php';

// 添加测试分组
addGroup('英语单词', 'English Words');

// 获取分组ID
$groups = getGroups();
$groupId = $groups[0]['id'] ?? 1;

// 移除旧的测试代码，直接使用新的题目数据
$questions = [
    [
        'question' => 'What is the meaning of "abandon"?',
        'option_a' => '放弃，遗弃',
        'option_b' => '能力，才能',
        'option_c' => '接受，同意',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "brilliant"?',
        'option_a' => '困难的',
        'option_b' => '聪明的，杰出的',
        'option_c' => '普通的',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "confidence"?',
        'option_a' => '困惑',
        'option_b' => '害羞',
        'option_c' => '信心，自信',
        'correct_option' => 'C',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "determine"?',
        'option_a' => '决定，确定',
        'option_b' => '延迟',
        'option_c' => '删除',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "efficient"?',
        'option_a' => '懒惰的',
        'option_b' => '高效的',
        'option_c' => '困难的',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "vocabulary"?',
        'option_a' => '声音',
        'option_b' => '视频',
        'option_c' => '词汇',
        'correct_option' => 'C',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "achievement"?',
        'option_a' => '成就，成绩',
        'option_b' => '意外',
        'option_c' => '建议',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "necessary"?',
        'option_a' => '可能的',
        'option_b' => '必要的',
        'option_c' => '不重要的',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "opportunity"?',
        'option_a' => '操作',
        'option_b' => '机会',
        'option_c' => '选择',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "experience"?',
        'option_a' => '经验',
        'option_b' => '实验',
        'option_c' => '期待',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "knowledge"?',
        'option_a' => '知识',
        'option_b' => '理解',
        'option_c' => '学习',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "improve"?',
        'option_a' => '导入',
        'option_b' => '提高',
        'option_c' => '实施',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "success"?',
        'option_a' => '继承',
        'option_b' => '过程',
        'option_c' => '成功',
        'correct_option' => 'C',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "challenge"?',
        'option_a' => '挑战',
        'option_b' => '机会',
        'option_c' => '改变',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "creative"?',
        'option_a' => '创新的',
        'option_b' => '创造性的',
        'option_c' => '创意的',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "development"?',
        'option_a' => '开发',
        'option_b' => '发展',
        'option_c' => '开展',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "environment"?',
        'option_a' => '环境',
        'option_b' => '周围',
        'option_c' => '自然',
        'correct_option' => 'A',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "technology"?',
        'option_a' => '科学',
        'option_b' => '技能',
        'option_c' => '技术',
        'correct_option' => 'C',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "education"?',
        'option_a' => '学习',
        'option_b' => '教育',
        'option_c' => '培训',
        'correct_option' => 'B',
        'points' => 10
    ],
    [
        'question' => 'What is the meaning of "communication"?',
        'option_a' => '交流',
        'option_b' => '沟通',
        'option_c' => '交际',
        'correct_option' => 'A',
        'points' => 10
    ]
];

try {
    // 获取数据库连接
    $db = getDB();
    
    // 准备SQL语句
    $stmt = $db->prepare('INSERT INTO questions (group_id, question, option_a, option_b, option_c, correct_option, points) 
                         VALUES (:group_id, :question, :option_a, :option_b, :option_c, :correct_option, :points)');

    // 插入题目
    foreach ($questions as $q) {
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':question', $q['question'], SQLITE3_TEXT);
        $stmt->bindValue(':option_a', $q['option_a'], SQLITE3_TEXT);
        $stmt->bindValue(':option_b', $q['option_b'], SQLITE3_TEXT);
        $stmt->bindValue(':option_c', $q['option_c'], SQLITE3_TEXT);
        $stmt->bindValue(':correct_option', $q['correct_option'], SQLITE3_TEXT);
        $stmt->bindValue(':points', $q['points'], SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        if ($result) {
            echo "Successfully inserted question: " . htmlspecialchars($q['question']) . "<br>";
        } else {
            echo "Failed to insert question: " . htmlspecialchars($q['question']) . "<br>";
        }
    }

    echo "All questions have been processed.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 