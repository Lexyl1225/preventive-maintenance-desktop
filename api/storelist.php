<?php
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json');

// ── Auto-create table if it doesn't exist ──────────────────────────────────
(function () use ($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_list_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fileName VARCHAR(255) DEFAULT NULL,
            snapshot JSON DEFAULT NULL,
            totalMain INT DEFAULT 0,
            totalSat INT DEFAULT 0,
            totalRedemption INT DEFAULT 0,
            totalAreas INT DEFAULT 0,
            savedAt DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (\Throwable $e) {
        error_log('storelist.php auto-create error: ' . $e->getMessage());
    }
})();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM store_list_records WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch();
            if ($row && isset($row['snapshot']) && $row['snapshot'] !== null) {
                $decoded = json_decode($row['snapshot'], true);
                $row['snapshot'] = $decoded !== null ? $decoded : $row['snapshot'];
            }
            echo json_encode($row ?: []);
        } else {
            $stmt = $pdo->query('SELECT * FROM store_list_records ORDER BY savedAt DESC, id DESC');
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                if (isset($r['snapshot']) && $r['snapshot'] !== null) {
                    $decoded = json_decode($r['snapshot'], true);
                    $r['snapshot'] = $decoded !== null ? $decoded : $r['snapshot'];
                }
            }
            unset($r);
            echo json_encode($rows);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            exit;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO store_list_records (fileName, snapshot, totalMain, totalSat, totalRedemption, totalAreas, savedAt)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['fileName'] ?? null,
            isset($data['snapshot']) ? json_encode($data['snapshot']) : null,
            (int)($data['totalMain'] ?? 0),
            (int)($data['totalSat'] ?? 0),
            (int)($data['totalRedemption'] ?? 0),
            (int)($data['totalAreas'] ?? 0),
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : date('Y-m-d H:i:s'),
        ]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare(
            'UPDATE store_list_records SET fileName=?, snapshot=?, totalMain=?, totalSat=?, totalRedemption=?, totalAreas=?, savedAt=? WHERE id=?'
        );
        $stmt->execute([
            $data['fileName'] ?? null,
            isset($data['snapshot']) ? json_encode($data['snapshot']) : null,
            (int)($data['totalMain'] ?? 0),
            (int)($data['totalSat'] ?? 0),
            (int)($data['totalRedemption'] ?? 0),
            (int)($data['totalAreas'] ?? 0),
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : null,
            $id,
        ]);
        echo json_encode(['updated' => true]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id parameter']);
            exit;
        }
        $stmt = $pdo->prepare('DELETE FROM store_list_records WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        echo json_encode(['deleted' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
