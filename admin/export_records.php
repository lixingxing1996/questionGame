<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

$studentId = $_GET['student_id'] ?? 0;
if (!$studentId) {
    header('Location: manage_students.php');
    exit();
}

// 获取学生信息
$student = getStudentById($studentId);
if (!$student) {
    header('Location: manage_students.php');
    exit();
}

// 获取答题记录
$records = getStudentAnswerRecords($studentId);

// 设置CSV文件头
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $student['name'] . '_答题记录_' . date('Y-m-d') . '.csv"');

// 创建一个写入到输出流的文件句柄
$output = fopen('php://output', 'w');

// 写入UTF-8 BOM，解决中文乱码
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 写入CSV头部
fputcsv($output, [
    '时间',
    '分组',
    '题目',
    '学生答案',
    '正确答案',
    '是否正确',
    '得分',
    '选项A',
    '选项B',
    '选项C'
]);

// 写入数据
foreach ($records as $record) {
    fputcsv($output, [
        date('Y-m-d H:i:s', strtotime($record['created_at'])),
        $record['group_name'],
        $record['question'],
        $record['answer'],
        $record['correct_option'],
        $record['is_correct'] ? '正确' : '错误',
        $record['points_earned'],
        $record['option_a'],
        $record['option_b'],
        $record['option_c']
    ]);
}

fclose($output);
exit(); 