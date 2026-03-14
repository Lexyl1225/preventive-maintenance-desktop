<?php
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM megger_tests WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch() ?: []);
        } else {
            $stmt = $pdo->query('SELECT * FROM megger_tests ORDER BY savedAt DESC, id DESC');
            echo json_encode($stmt->fetchAll());
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('INSERT INTO megger_tests (name,savedAt,data) VALUES (?,?,?)');
        $stmt->execute([
            $data['name'] ?? null,
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : null,
            isset($data['data']) ? json_encode($data['data']) : null
        ]);
        echo json_encode(['id' => $pdo->lastInsertId()]);
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $put);
        $id = $put['id'] ?? null;
        if (!$id) { http_response_code(400); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare('UPDATE megger_tests SET name=?,savedAt=?,data=? WHERE id=?');
        $stmt->execute([
            $data['name'] ?? null,
            isset($data['savedAt']) ? date('Y-m-d H:i:s', strtotime($data['savedAt'])) : null,
            isset($data['data']) ? json_encode($data['data']) : null,
            $id
        ]);
        echo json_encode(['updated' => true]);
        break;
    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); exit; }
        $stmt = $pdo->prepare('DELETE FROM megger_tests WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        echo json_encode(['deleted' => true]);
        break;
    default:
        http_response_code(405);
}