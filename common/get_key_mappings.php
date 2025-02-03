<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$db = getDB();
$result = $db->query('SELECT position, key_code FROM key_mappings');

$mappings = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $mappings[$row['position']] = $row['key_code'];
}

header('Content-Type: application/json');
echo json_encode($mappings);
?> 