<?php
require_once __DIR__ . '/config.php';

// 添加分组
function addGroup($name, $alias = '') {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO groups (name, alias) VALUES (:name, :alias)');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':alias', $alias, SQLITE3_TEXT);
    return $stmt->execute();
}

// 添加问题
function addQuestion($groupId, $question, $optionA, $optionB, $optionC, $correctOption, $points = 1) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO questions (group_id, question, option_a, option_b, option_c, correct_option, points) 
                         VALUES (:group_id, :question, :option_a, :option_b, :option_c, :correct_option, :points)');
    $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
    $stmt->bindValue(':question', $question, SQLITE3_TEXT);
    $stmt->bindValue(':option_a', $optionA, SQLITE3_TEXT);
    $stmt->bindValue(':option_b', $optionB, SQLITE3_TEXT);
    $stmt->bindValue(':option_c', $optionC, SQLITE3_TEXT);
    $stmt->bindValue(':correct_option', $correctOption, SQLITE3_TEXT);
    $stmt->bindValue(':points', $points, SQLITE3_INTEGER);
    return $stmt->execute();
}

// 获取所有分组
function getGroups() {
    $db = getDB();
    $results = $db->query('SELECT * FROM groups ORDER BY name');
    $groups = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $groups[] = $row;
    }
    return $groups;
}

// 获取特定分组的问题
function getQuestionsByGroup($groupId) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM questions WHERE group_id = :group_id');
    $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $questions = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $questions[] = $row;
    }
    return $questions;
}

// 根据ID获取分组信息
function getGroupById($id) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM groups WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

/**
 * 检查当前用户是否为管理员
 * @return boolean
 */
function is_admin() {
    // 根据您的用户系统实现具体的管理员判断逻辑
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    return false;
}

// 获取分组总数
function countGroups() {
    $db = getDB();
    $result = $db->query('SELECT COUNT(*) as count FROM groups');
    return $result->fetchArray(SQLITE3_ASSOC)['count'];
}

// 获取问题总数
function countQuestions() {
    $db = getDB();
    $result = $db->query('SELECT COUNT(*) as count FROM questions');
    return $result->fetchArray(SQLITE3_ASSOC)['count'];
}

// 获取学生总数
function countStudents() {
    $db = getDB();
    $result = $db->query('SELECT COUNT(*) as count FROM students');
    return $result->fetchArray(SQLITE3_ASSOC)['count'];
}

// 添加学生
function addStudent($name, $avatar) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO students (name, avatar) VALUES (:name, :avatar)');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':avatar', $avatar, SQLITE3_TEXT);
    return $stmt->execute();
}

// 获取所有学生
function getStudents() {
    $db = getDB();
    $results = $db->query('SELECT * FROM students ORDER BY created_at DESC');
    $students = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $students[] = $row;
    }
    return $students;
}

// 调整学生积分
function adjustStudentPoints($studentId, $amount) {
    $db = getDB();
    $stmt = $db->prepare('UPDATE students SET points = points + :amount WHERE id = :id');
    $stmt->bindValue(':amount', $amount, SQLITE3_INTEGER);
    $stmt->bindValue(':id', $studentId, SQLITE3_INTEGER);
    return $stmt->execute();
}

// 获取按键映射
function getKeyMappings() {
    $db = getDB();
    $results = $db->query('SELECT position, key_code FROM key_mappings');
    $mappings = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $mappings[$row['position']] = $row['key_code'];
    }
    return $mappings;
}

// 更新按键映射
function updateKeyMapping($position, $keyCode) {
    $db = getDB();
    $stmt = $db->prepare('UPDATE key_mappings SET key_code = :key_code WHERE position = :position');
    $stmt->bindValue(':key_code', $keyCode, SQLITE3_TEXT);
    $stmt->bindValue(':position', $position, SQLITE3_TEXT);
    return $stmt->execute();
}

// 记录学生答题
function recordAnswer($studentId, $questionId, $answer, $isCorrect, $pointsEarned) {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO answer_records (student_id, question_id, answer, is_correct, points_earned) 
                         VALUES (:student_id, :question_id, :answer, :is_correct, :points_earned)');
    $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
    $stmt->bindValue(':question_id', $questionId, SQLITE3_INTEGER);
    $stmt->bindValue(':answer', $answer, SQLITE3_TEXT);
    $stmt->bindValue(':is_correct', $isCorrect ? 1 : 0, SQLITE3_INTEGER);
    $stmt->bindValue(':points_earned', $pointsEarned, SQLITE3_INTEGER);
    return $stmt->execute();
}

// 获取学生的答题记录
function getStudentAnswerRecords($studentId) {
    $db = getDB();
    $stmt = $db->prepare('
        SELECT 
            ar.*,
            q.question,
            q.option_a,
            q.option_b,
            q.option_c,
            q.correct_option,
            g.name as group_name
        FROM answer_records ar
        JOIN questions q ON ar.question_id = q.id
        JOIN groups g ON q.group_id = g.id
        WHERE ar.student_id = :student_id
        ORDER BY ar.created_at DESC
    ');
    $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $records = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $records[] = $row;
    }
    return $records;
}

// 获取学生的答题统计
function getStudentAnswerStats($studentId) {
    $db = getDB();
    $stmt = $db->prepare('
        SELECT 
            COUNT(*) as total_answers,
            SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
            SUM(points_earned) as total_points
        FROM answer_records
        WHERE student_id = :student_id
    ');
    $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

// 更新分组信息
function updateGroup($id, $name, $alias) {
    $db = getDB();
    $stmt = $db->prepare('UPDATE groups SET name = :name, alias = :alias WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':alias', $alias, SQLITE3_TEXT);
    return $stmt->execute();
}

// 根据ID获取学生信息
function getStudentById($id) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

// 获取随机题目
function getRandomQuestions($groupId, $count) {
    $db = getDB();
    
    try {
        // 获取指定分组的所有题目
        $stmt = $db->prepare('
            SELECT id, question, option_a, option_b, option_c, correct_option, points 
            FROM questions 
            WHERE group_id = :group_id
            ORDER BY RANDOM()
            LIMIT :count
        ');
        
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
        
        $result = $stmt->execute();
        $questions = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $questions[] = $row;
        }
        
        // 调试输出
        error_log('getRandomQuestions result for group_id ' . $groupId . ': ' . print_r($questions, true));
        
        if (empty($questions)) {
            error_log("No questions found for group_id: $groupId");
            // 检查数据库中是否有题目
            $checkStmt = $db->prepare('SELECT COUNT(*) as count FROM questions WHERE group_id = :group_id');
            $checkStmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
            $countResult = $checkStmt->execute();
            $count = $countResult->fetchArray(SQLITE3_ASSOC)['count'];
            error_log("Total questions in database for group_id $groupId: $count");
        }
        
        return $questions;
    } catch (Exception $e) {
        error_log('Error in getRandomQuestions: ' . $e->getMessage());
        return [];
    }
}

// 创建新比赛
function createMatch($groupId, $student1Id, $student2Id) {
    $db = getDB();
    try {
        $db->exec('BEGIN');
        
        $stmt = $db->prepare('INSERT INTO matches (mode, group_id, student1_id, student2_id) 
                            VALUES ("双人竞赛", :group_id, :student1_id, :student2_id)');
        $stmt->bindValue(':group_id', $groupId, SQLITE3_INTEGER);
        $stmt->bindValue(':student1_id', $student1Id, SQLITE3_INTEGER);
        $stmt->bindValue(':student2_id', $student2Id, SQLITE3_INTEGER);
        $stmt->execute();
        
        $matchId = $db->lastInsertRowID();
        
        // 获取随机题目并记录到match_questions表
        $questions = getRandomQuestions($groupId, 10);
        foreach ($questions as $index => $question) {
            $stmt = $db->prepare('
                INSERT INTO match_questions 
                (match_id, question_id, question_order) 
                VALUES (:match_id, :question_id, :question_order)
            ');
            $stmt->bindValue(':match_id', $matchId, SQLITE3_INTEGER);
            $stmt->bindValue(':question_id', $question['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':question_order', $index + 1, SQLITE3_INTEGER);
            $stmt->execute();
        }
        
        $db->exec('COMMIT');
        return $matchId;
    } catch (Exception $e) {
        $db->exec('ROLLBACK');
        error_log('Error creating match: ' . $e->getMessage());
        throw $e;
    }
}

// ... 原functions.php的其他内容保持不变 ... 