<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/utils.php';

corsAndJsonHeaders();
handleOptions();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $pdo = db();
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'error' => 'Database connection failed', 'detail' => $e->getMessage()], 500);
}

function decodeStateValue(array $state, string $key): array
{
    if (!array_key_exists($key, $state)) return [];
    $raw = $state[$key];
    if (!is_string($raw) || $raw === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function syncStudents(PDO $pdo, array $state): void
{
    $students = decodeStateValue($state, 'ep_stu');
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM students');
        $stmt = $pdo->prepare(
            'INSERT INTO students (app_id, enroll, name, password, exam_code, section, year, branch)
             VALUES (:app_id, :enroll, :name, :password, :exam_code, :section, :year, :branch)'
        );
        foreach ($students as $s) {
            if (!is_array($s)) continue;
            $enroll = (string)($s['enroll'] ?? '');
            if ($enroll === '') continue;
            $stmt->execute([
                'app_id'   => isset($s['id']) && is_numeric($s['id']) ? (int)$s['id'] : null,
                'enroll'   => $enroll,
                'name'     => (string)($s['name'] ?? ''),
                'password' => (string)($s['password'] ?? ''),
                'exam_code'=> (string)($s['examCode'] ?? ''),
                'section'  => (string)($s['section'] ?? ''),
                'year'     => (string)($s['year'] ?? ''),
                'branch'   => (string)($s['branch'] ?? ''),
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function syncFaculty(PDO $pdo, array $state): void
{
    $faculty = decodeStateValue($state, 'ep_fac');
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM faculty');
        $stmt = $pdo->prepare(
            'INSERT INTO faculty (id, name, password, role, code, depts_json, subjects_json)
             VALUES (:id, :name, :password, :role, :code, :depts_json, :subjects_json)'
        );
        foreach ($faculty as $f) {
            if (!is_array($f)) continue;
            $id = (string)($f['id'] ?? '');
            if ($id === '') continue;
            $stmt->execute([
                'id'           => $id,
                'name'         => (string)($f['name'] ?? ''),
                'password'     => (string)($f['password'] ?? ''),
                'role'         => (string)($f['role'] ?? 'Faculty'),
                'code'         => isset($f['code']) ? (string)$f['code'] : null,
                'depts_json'   => json_encode($f['depts'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'subjects_json'=> json_encode($f['subjects'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function syncQuestions(PDO $pdo, array $state): void
{
    $bank = decodeStateValue($state, 'ep_qbank');
    if (!is_array($bank)) $bank = [];
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM questions');
        $stmt = $pdo->prepare(
            'INSERT INTO questions (dept, subject, q_index, question_text, options_json, correct_index, chapter)
             VALUES (:dept, :subject, :q_index, :question_text, :options_json, :correct_index, :chapter)'
        );
        foreach ($bank as $key => $qs) {
            if (!is_string($key) || !is_array($qs)) continue;
            $parts = explode('::', $key, 2);
            if (count($parts) !== 2) continue;
            [$dept, $subject] = $parts;
            $dept = (string)$dept;
            $subject = (string)$subject;
            if ($dept === '' || $subject === '') continue;
            foreach ($qs as $i => $q) {
                if (!is_array($q)) continue;
                $text = (string)($q['text'] ?? '');
                if ($text === '') continue;
                $stmt->execute([
                    'dept'         => $dept,
                    'subject'      => $subject,
                    'q_index'      => is_numeric($i) ? (int)$i : 0,
                    'question_text'=> $text,
                    'options_json' => json_encode($q['options'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'correct_index'=> isset($q['correct']) && is_numeric($q['correct']) ? (int)$q['correct'] : 0,
                    'chapter'      => (string)($q['chapter'] ?? ''),
                ]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT state_json, updated_at FROM app_state WHERE id = 1');
    $stmt->execute();
    $row = $stmt->fetch();
    if (!$row) {
        jsonOut(['ok' => true, 'state' => new stdClass(), 'updatedAt' => null]);
    }
    $state = json_decode((string)$row['state_json'], true);
    if (!is_array($state)) $state = [];
    jsonOut(['ok' => true, 'state' => $state, 'updatedAt' => $row['updated_at'] ?? null]);
}

if ($method === 'POST' || $method === 'PUT') {
    $body = jsonInput();
    $state = $body['state'] ?? null;
    if (!is_array($state)) {
        jsonOut(['ok' => false, 'error' => 'Invalid payload. Expected {"state": {...}}'], 422);
    }
    $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        jsonOut(['ok' => false, 'error' => 'Could not encode state JSON'], 500);
    }

    $sql = 'INSERT INTO app_state (id, state_json) VALUES (1, :state_json)
            ON DUPLICATE KEY UPDATE state_json = VALUES(state_json)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['state_json' => $json]);
    syncStudents($pdo, $state);
    syncFaculty($pdo, $state);
    syncQuestions($pdo, $state);
    jsonOut(['ok' => true]);
}

jsonOut(['ok' => false, 'error' => 'Method not allowed'], 405);
