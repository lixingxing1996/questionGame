<?php
require_once __DIR__ . '/common/config.php';

// 创建数据库目录（如果不存在）
$dbDir = __DIR__ . '/data';
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0777, true);
}

$db = new SQLite3(DB_PATH);

// 创建分组表
$db->exec('CREATE TABLE IF NOT EXISTS groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    alias TEXT
)');

// 创建问题表 - 更新结构
$db->exec('DROP TABLE IF EXISTS questions');
$db->exec('CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    question TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    correct_option CHAR(1) NOT NULL,
    points INTEGER DEFAULT 1,
    FOREIGN KEY (group_id) REFERENCES groups(id),
    CHECK (correct_option IN ("A", "B", "C"))
)');

// 创建用户表
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT DEFAULT "user"
)');

// 添加默认管理员账户
$stmt = $db->prepare('INSERT OR IGNORE INTO users (username, password, role) VALUES (:username, :password, :role)');
$stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
$stmt->bindValue(':password', password_hash('admin123', PASSWORD_DEFAULT), SQLITE3_TEXT);
$stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
$stmt->execute();

// 创建学生表
$db->exec('CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    points INTEGER DEFAULT 0,
    avatar TEXT DEFAULT "default.png",
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// 创建按键映射表
$db->exec('CREATE TABLE IF NOT EXISTS key_mappings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    position TEXT NOT NULL,  -- left_red, left_yellow, left_blue, left_green, right_red, right_yellow, right_blue, right_green
    key_code TEXT NOT NULL,  -- 对应的键盘按键
    UNIQUE(position)
)');

// 插入默认按键映射
$defaultMappings = [
    ['left_red', 'a'],
    ['left_yellow', 's'],
    ['left_blue', 'd'],
    ['left_green', 'f'],
    ['right_red', 'j'],
    ['right_yellow', 'k'],
    ['right_blue', 'l'],
    ['right_green', ';']
];

foreach ($defaultMappings as $mapping) {
    $stmt = $db->prepare('INSERT OR IGNORE INTO key_mappings (position, key_code) VALUES (:position, :key_code)');
    $stmt->bindValue(':position', $mapping[0], SQLITE3_TEXT);
    $stmt->bindValue(':key_code', $mapping[1], SQLITE3_TEXT);
    $stmt->execute();
}

// 创建比赛表
$db->exec('CREATE TABLE IF NOT EXISTS matches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mode TEXT NOT NULL,
    group_id INTEGER NOT NULL,
    student1_id INTEGER NOT NULL,
    student2_id INTEGER NOT NULL,
    student1_score INTEGER DEFAULT 0,
    student2_score INTEGER DEFAULT 0,
    winner_id INTEGER,
    status TEXT DEFAULT "pending",
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id),
    FOREIGN KEY (student1_id) REFERENCES students(id),
    FOREIGN KEY (student2_id) REFERENCES students(id),
    FOREIGN KEY (winner_id) REFERENCES students(id)
)');

// 创建答题记录表
$db->exec('CREATE TABLE IF NOT EXISTS answer_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    match_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    answer CHAR(1) NOT NULL,
    is_correct BOOLEAN NOT NULL,
    base_score INTEGER NOT NULL,
    time_bonus INTEGER NOT NULL,
    total_score INTEGER NOT NULL,
    response_time INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (question_id) REFERENCES questions(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
)');

// 创建触发器来自动更新比赛总分
$db->exec('
    CREATE TRIGGER IF NOT EXISTS update_match_scores
    AFTER INSERT ON answer_records
    BEGIN
        UPDATE matches 
        SET 
            student1_score = (
                SELECT COALESCE(SUM(total_score), 0)
                FROM answer_records
                WHERE match_id = NEW.match_id 
                AND student_id = (SELECT student1_id FROM matches WHERE id = NEW.match_id)
            ),
            student2_score = (
                SELECT COALESCE(SUM(total_score), 0)
                FROM answer_records
                WHERE match_id = NEW.match_id 
                AND student_id = (SELECT student2_id FROM matches WHERE id = NEW.match_id)
            )
        WHERE id = NEW.match_id;
    END
');

// 修改比赛题目表，添加更多字段以支持对战功能
$db->exec('DROP TABLE IF EXISTS match_questions');
$db->exec('CREATE TABLE IF NOT EXISTS match_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    match_id INTEGER NOT NULL,
    question_id INTEGER NOT NULL,
    question_order INTEGER NOT NULL,  -- 题目顺序
    student1_answer CHAR(1),  -- 学生1的答案
    student1_is_correct BOOLEAN,  -- 学生1是否答对
    student1_response_time INTEGER,  -- 学生1回答用时
    student2_answer CHAR(1),  -- 学生2的答案
    student2_is_correct BOOLEAN,  -- 学生2是否答对
    student2_response_time INTEGER,  -- 学生2回答用时
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
)');

echo "数据库初始化完成！\n";
echo "默认管理员账户：\n";
echo "用户名：admin\n";
echo "密码：admin123\n";

?> 