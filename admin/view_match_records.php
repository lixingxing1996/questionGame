<?php
require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/functions.php';

// 获取比赛ID
$matchId = $_GET['match_id'] ?? 0;

if (!$matchId) {
    die('请提供比赛ID');
}

try {
    $db = getDB();
    
    // 获取比赛基本信息
    $stmt = $db->prepare('
        SELECT m.*, 
               s1.name as student1_name,
               s2.name as student2_name,
               g.name as group_name
        FROM matches m
        JOIN students s1 ON m.student1_id = s1.id
        JOIN students s2 ON m.student2_id = s2.id
        JOIN groups g ON m.group_id = g.id
        WHERE m.id = :match_id
    ');
    $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $match = $result->fetchArray(SQLITE3_ASSOC);
    
    // 获取详细答题记录
    $stmt = $db->prepare('
        SELECT 
            mq.question_order,
            q.question,
            q.correct_option,
            mq.student1_answer,
            mq.student1_is_correct,
            mq.student1_response_time,
            mq.student2_answer,
            mq.student2_is_correct,
            mq.student2_response_time,
            ar.total_score,
            ar.time_bonus
        FROM match_questions mq
        JOIN questions q ON mq.question_id = q.id
        LEFT JOIN answer_records ar ON mq.match_id = ar.match_id 
            AND mq.question_id = ar.question_id
        WHERE mq.match_id = :match_id
        ORDER BY mq.question_order
    ');
    $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $records = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $records[] = $row;
    }
} catch (Exception $e) {
    die('获取记录失败：' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>比赛记录详情</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">比赛记录详情</h1>
        
        <!-- 比赛基本信息 -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">基本信息</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><strong>比赛ID：</strong><?php echo $match['id']; ?></p>
                    <p><strong>题库：</strong><?php echo $match['group_name']; ?></p>
                    <p><strong>状态：</strong><?php echo $match['status']; ?></p>
                </div>
                <div>
                    <p><strong>学生1：</strong><?php echo $match['student1_name']; ?> (得分：<?php echo $match['student1_score']; ?>)</p>
                    <p><strong>学生2：</strong><?php echo $match['student2_name']; ?> (得分：<?php echo $match['student2_score']; ?>)</p>
                    <p><strong>获胜者：</strong><?php echo $match['winner_id'] ? ($match['winner_id'] == $match['student1_id'] ? $match['student1_name'] : $match['student2_name']) : '未结束'; ?></p>
                </div>
            </div>
        </div>
        
        <!-- 答题记录表格 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题号</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题目</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">正确答案</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2"><?php echo $match['student1_name']; ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2"><?php echo $match['student2_name']; ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $record['question_order']; ?></td>
                        <td class="px-6 py-4"><?php echo $record['question']; ?></td>
                        <td class="px-6 py-4"><?php echo $record['correct_option']; ?></td>
                        <td class="px-6 py-4">
                            答案：<?php echo $record['student1_answer'] ?: '-'; ?><br>
                            用时：<?php echo $record['student1_response_time'] ? ($record['student1_response_time']/1000).'秒' : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($record['student1_is_correct']): ?>
                                <span class="text-green-600">✓ 正确</span>
                                <?php if ($record['total_score']): ?>
                                    <br>得分：<?php echo $record['total_score']; ?>
                                    <?php if ($record['time_bonus']): ?>
                                        <br>(+<?php echo $record['time_bonus']; ?>时间奖励)
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif ($record['student1_answer']): ?>
                                <span class="text-red-600">✗ 错误</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            答案：<?php echo $record['student2_answer'] ?: '-'; ?><br>
                            用时：<?php echo $record['student2_response_time'] ? ($record['student2_response_time']/1000).'秒' : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($record['student2_is_correct']): ?>
                                <span class="text-green-600">✓ 正确</span>
                                <?php if ($record['total_score']): ?>
                                    <br>得分：<?php echo $record['total_score']; ?>
                                    <?php if ($record['time_bonus']): ?>
                                        <br>(+<?php echo $record['time_bonus']; ?>时间奖励)
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif ($record['student2_answer']): ?>
                                <span class="text-red-600">✗ 错误</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 