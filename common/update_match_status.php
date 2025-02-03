<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$matchId = $_POST['match_id'] ?? 0;
$status = $_POST['status'] ?? '';

if (!$matchId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('UPDATE matches SET status = :status WHERE id = :id');
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $matchId, SQLITE3_INTEGER);
    $success = $stmt->execute();
    echo json_encode(['success' => (bool)$success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 