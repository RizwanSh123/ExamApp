<?php
declare(strict_types=1);

// Copy this file to config.local.php and update values for local testing.
// In production (Cloud Run), prefer environment variables.

return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: 'exam_portal',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    // For Cloud SQL Unix socket: /cloudsql/PROJECT:REGION:INSTANCE
    'db_socket' => getenv('DB_SOCKET') ?: '',
];

