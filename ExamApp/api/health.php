<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/utils.php';

corsAndJsonHeaders();
handleOptions();

try {
    db()->query('SELECT 1');
    jsonOut(['ok' => true, 'service' => 'exam-api', 'db' => 'up']);
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'service' => 'exam-api', 'db' => 'down', 'error' => $e->getMessage()], 500);
}

