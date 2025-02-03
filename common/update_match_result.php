<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$matchId = $_POST['match_id'] ?? 0;
$winnerId = $_POST['winner_id'] ?? 0;

if (!$matchId || !$winnerId) {
    die(json_encode(['success' => false]));
}

try {
    $db = getDB();
    $stmt = $db->prepare('UPDATE matches SET winner_id = :winner_id WHERE id = :id');
    $stmt->bindValue(':id', $matchId, SQLITE3_INTEGER);
    $stmt->bindValue(':winner_id', $winnerId, SQLITE3_INTEGER);
    $success = $stmt->execute();

    echo json_encode(['success' => (bool)$success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 