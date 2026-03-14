<?php
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM pm_checklist WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch();
            if ($row && isset($row['categories']) && $row['categories'] !== null) {
                $decoded = json_decode($row['categories'], true);
                $row['categories'] = $decoded === null ? [] : $decoded;
            }
            echo json_encode($row ?: []);
        } else {
            $stmt = $pdo->query('SELECT * FROM pm_checklist ORDER BY savedAt DESC, id DESC');
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                if (isset($r['categories']) && $r['categories'] !== null) {
                    $decoded = json_decode($r['categories'], true);
                    $r['categories'] = $decoded === null ? [] : $decoded;
                }
            }
            echo json_encode($rows);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO pm_checklist
            (branchName,branchCode,location,date,conductedBy,refNumber,
             sigConducted,sigWitnessed,sigStoreManager,sigStoreRep,overallRecommendations,categories,savedAt)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['branchName'] ?? null,
            $data['branchCode'] ?? null,
            $data['location'] ?? null,
            $data['date'] ?? null,
            $data['conductedBy'] ?? null,
            $data['refNumber'] ?? null,
            $data['sigConducted'] ?? null,
            $data['sigWitnessed'] ?? null,
            $data['sigStoreManager'] ?? null,
            $data['sigStoreRep'] ?? null,
            $data['overallRecommendations'] ?? null,
            isset($data['categories']) ? json_encode($data['categories']) : null,
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : null
        ]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;
    case 'PUT':
        // id is expected in the query string (?id=123). Body is JSON.
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('UPDATE pm_checklist SET
            branchName=?,branchCode=?,location=?,date=?,conductedBy=?,refNumber=?,
            sigConducted=?,sigWitnessed=?,sigStoreManager=?,sigStoreRep=?,overallRecommendations=?,categories=?,savedAt=?
            WHERE id=?');
        $stmt->execute([
            $data['branchName'] ?? null,
            $data['branchCode'] ?? null,
            $data['location'] ?? null,
            $data['date'] ?? null,
            $data['conductedBy'] ?? null,
            $data['refNumber'] ?? null,
            $data['sigConducted'] ?? null,
            $data['sigWitnessed'] ?? null,
            $data['sigStoreManager'] ?? null,
            $data['sigStoreRep'] ?? null,
            $data['overallRecommendations'] ?? null,
            isset($data['categories']) ? json_encode($data['categories']) : null,
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : null,
            $id
        ]);
        echo json_encode(['updated' => true]);
        break;
    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $stmt = $pdo->prepare('DELETE FROM pm_checklist WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        echo json_encode(['deleted' => true]);
        break;
    default:
        http_response_code(405);
}