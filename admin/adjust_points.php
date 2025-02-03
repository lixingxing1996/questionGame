<?php
require_once('../common/config.php');
require_once('../common/functions.php');

if (!is_admin()) {
    die(json_encode(['success' => false]));
}

$studentId = $_POST['student_id'] ?? 0;
$amount = $_POST['amount'] ?? 0;

if (!$studentId || !$amount) {
    die(json_encode(['success' => false]));
}

$success = adjustStudentPoints($studentId, $amount);
echo json_encode(['success' => $success]); 