<?php
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM maintenance_records WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: []);
        } else {
            $stmt = $pdo->query('SELECT * FROM maintenance_records ORDER BY date DESC, id DESC');
            echo json_encode($stmt->fetchAll());
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare(
            'INSERT INTO maintenance_records 
             (BranchCode,BranchName,location,equipment,task,status,performedBy,verifiedBy,date,nextDue,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['BranchCode'] ?? null,
            $data['BranchName'] ?? null,
            $data['location'] ?? null,
            $data['equipment'] ?? null,
            $data['task'] ?? null,
            $data['status'] ?? null,
            $data['performedBy'] ?? null,
            $data['verifiedBy'] ?? null,
            isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : null,
            isset($data['nextDue']) ? date('Y-m-d', strtotime($data['nextDue'])) : null,
            $data['notes'] ?? null
        ]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $put);
        $id = $put['id'] ?? null;
        if (!$id) { http_response_code(400); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare(
            'UPDATE maintenance_records SET
               BranchCode=?,BranchName=?,location=?,equipment=?,task=?,status=?,
               performedBy=?,verifiedBy=?,date=?,nextDue=?,notes=? 
             WHERE id=?'
        );
        $stmt->execute([
            $data['BranchCode'] ?? null,
            $data['BranchName'] ?? null,
            $data['location'] ?? null,
            $data['equipment'] ?? null,
            $data['task'] ?? null,
            $data['status'] ?? null,
            $data['performedBy'] ?? null,
            $data['verifiedBy'] ?? null,
            isset($data['date']) ? date('Y-m-d', strtotime($data['date'])) : null,
            isset($data['nextDue']) ? date('Y-m-d', strtotime($data['nextDue'])) : null,
            $data['notes'] ?? null,
            $id
        ]);
        echo json_encode(['updated' => true]);
        break;
    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $stmt = $pdo->prepare('DELETE FROM maintenance_records WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        echo json_encode(['deleted' => true]);
        break;
    default:
        http_response_code(405);
}
