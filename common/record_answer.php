<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// 获取POST数据
$matchId = $_POST['match_id'] ?? 0;
$questionId = $_POST['question_id'] ?? 0;
$studentId = $_POST['student_id'] ?? 0;
$answer = $_POST['answer'] ?? '';
$isCorrect = $_POST['is_correct'] ?? 0;
$responseTime = $_POST['response_time'] ?? 0;
$baseScore = $_POST['base_score'] ?? 0;
$timeBonus = $_POST['time_bonus'] ?? 0;
$totalScore = $baseScore + $timeBonus;

if (!$matchId || !$questionId || !$studentId) {
    http_response_code(400);
    error_log("Missing parameters: match_id=$matchId, question_id=$questionId, student_id=$studentId");
    exit('Missing required parameters');
}

try {
    $db = getDB();
    // 记录请求参数用于调试
    error_log("Recording answer - Match: $matchId, Question: $questionId, Student: $studentId, Answer: $answer, Score: $totalScore");
    
    $db->exec('BEGIN');
    
    // 检查表是否存在
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='answer_records'");
    if (!$tables->fetchArray()) {
        error_log("Error: answer_records table does not exist!");
        throw new Exception("Database table 'answer_records' not found");
    }
    
    // 获取当前比赛信息以确定是学生1还是学生2
    $stmt = $db->prepare('SELECT student1_id FROM matches WHERE id = :match_id');
    $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $match = $result->fetchArray(SQLITE3_ASSOC);
    $isStudent1 = ($studentId == $match['student1_id']);
    
    // 更新match_questions表
    $stmt = $db->prepare('
        UPDATE match_questions 
        SET 
            student' . ($isStudent1 ? '1' : '2') . '_answer = :answer,
            student' . ($isStudent1 ? '1' : '2') . '_is_correct = :is_correct,
            student' . ($isStudent1 ? '1' : '2') . '_response_time = :response_time
        WHERE match_id = :match_id AND question_id = :question_id
    ');
    
    $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
    $stmt->bindValue(':question_id', $questionId, SQLITE3_INTEGER);
    $stmt->bindValue(':answer', $answer, SQLITE3_TEXT);
    $stmt->bindValue(':is_correct', $isCorrect, SQLITE3_INTEGER);
    $stmt->bindValue(':response_time', $responseTime, SQLITE3_INTEGER);
    $stmt->execute();
    
    $success = true;
    
    // 只有答对的时候才记录分数
    if ($isCorrect) {
        // 记录到answer_records表
        $stmt = $db->prepare('
            INSERT INTO answer_records 
            (match_id, question_id, student_id, answer, is_correct, base_score, time_bonus, total_score, response_time)
            VALUES (:match_id, :question_id, :student_id, :answer, :is_correct, :base_score, :time_bonus, :total_score, :response_time)
        ');
        
        // 记录绑定的参数值
        error_log("Recording correct answer with score - Match: $matchId, Student: $studentId, Score: $totalScore");
    
        $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
        $stmt->bindValue(':question_id', $questionId, SQLITE3_INTEGER);
        $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
        $stmt->bindValue(':answer', $answer, SQLITE3_TEXT);
        $stmt->bindValue(':is_correct', $isCorrect, SQLITE3_INTEGER);
        $stmt->bindValue(':base_score', $baseScore, SQLITE3_INTEGER);
        $stmt->bindValue(':time_bonus', $timeBonus, SQLITE3_INTEGER);
        $stmt->bindValue(':total_score', $totalScore, SQLITE3_INTEGER);
        $stmt->bindValue(':response_time', $responseTime, SQLITE3_INTEGER);
        
        $success = $stmt->execute();
    }
    
    if (!$success) {
        error_log("SQL Error: " . $db->lastErrorMsg());
    }
    
    $db->exec('COMMIT');
    
    echo json_encode(['success' => (bool)$success]);
} catch (Exception $e) {
    $db->exec('ROLLBACK');
    error_log("Error recording answer: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 