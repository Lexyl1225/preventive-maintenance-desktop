<?php
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
try{
    if($method === 'GET'){
        // By default, exclude soft-deleted items. Use ?show_deleted=1 to include them.
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
        $sql = 'SELECT mu.*, mr.BranchCode, mr.BranchName, mr.task AS record_task, mr.date AS record_date FROM maintenance_uploads mu LEFT JOIN maintenance_records mr ON mu.record_id = mr.id ';
        if(!$showDeleted) $sql .= ' WHERE mu.deleted = 0 ';
        $sql .= ' ORDER BY mu.uploaded_at DESC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
        exit;
    }

    if($method === 'POST'){
        // JSON body with action:
        // single actions: { action: 'associate', id: X, record_id: Y }
        //                 { action: 'soft_delete', id: X }
        //                 { action: 'restore', id: X }
        // bulk actions:   { action: 'bulk_restore', ids: [..] }
        //                 { action: 'bulk_permanent_delete', ids: [..] }
        $data = json_decode(file_get_contents('php://input'), true);
        if(!is_array($data) || !isset($data['action'])){ http_response_code(400); echo json_encode(['error'=>'Invalid payload']); exit; }
        $action = $data['action'];
        if($action === 'associate'){
            if(!isset($data['id'])){ http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
            $id = intval($data['id']);
            $rid = isset($data['record_id']) && $data['record_id'] !== null ? intval($data['record_id']) : null;
            $stmt = $pdo->prepare('UPDATE maintenance_uploads SET record_id = ? WHERE id = ?');
            $stmt->execute([$rid, $id]);
            echo json_encode(['ok'=>true]);
            exit;
        } elseif($action === 'soft_delete'){
            if(!isset($data['id'])){ http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
            $id = intval($data['id']);
            $deletedBy = isset($_SERVER['HTTP_X_USER']) ? $_SERVER['HTTP_X_USER'] : (isset($_SERVER['HTTP_X_USER_NAME']) ? $_SERVER['HTTP_X_USER_NAME'] : null);
            $stmt = $pdo->prepare('UPDATE maintenance_uploads SET deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?');
            $stmt->execute([$deletedBy, $id]);
            echo json_encode(['ok'=>true]);
            exit;
        } elseif($action === 'restore'){
            if(!isset($data['id'])){ http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
            $id = intval($data['id']);
            $stmt = $pdo->prepare('UPDATE maintenance_uploads SET deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['ok'=>true]);
            exit;
        } elseif($action === 'bulk_restore' && isset($data['ids']) && is_array($data['ids'])){
            $ids = array_map('intval', $data['ids']);
            if(count($ids) === 0){ echo json_encode(['ok'=>true]); exit; }
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE maintenance_uploads SET deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id IN ($in)");
            $stmt->execute($ids);
            echo json_encode(['ok'=>true]); exit;
        } elseif($action === 'bulk_permanent_delete' && isset($data['ids']) && is_array($data['ids'])){
            $adminToken = getenv('EPM_ADMIN_TOKEN') ?: null;
            $provided = isset($_SERVER['HTTP_X_ADMIN_TOKEN']) ? $_SERVER['HTTP_X_ADMIN_TOKEN'] : null;
            if(!$adminToken || !$provided || $provided !== $adminToken){ http_response_code(403); echo json_encode(['error'=>'Forbidden: admin token required']); exit; }
            $ids = array_map('intval', $data['ids']);
            if(count($ids) === 0){ echo json_encode(['ok'=>true]); exit; }
            $place = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM maintenance_uploads WHERE id IN ($place)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
            $uploadsDir = realpath(__DIR__ . '/../uploads');
            $trashDir = __DIR__ . '/../uploads/trash'; if(!is_dir($trashDir)) @mkdir($trashDir,0755,true);
            foreach($rows as $row){
                $filePath = __DIR__ . '/../' . ltrim($row['file_path'], '/\\');
                $realFile = realpath($filePath);
                if($realFile && strpos($realFile, $uploadsDir) === 0 && file_exists($realFile)){
                    $base = basename($realFile);
                    $newName = time() . '_' . $row['id'] . '_' . $base;
                    $dest = realpath($trashDir) . DIRECTORY_SEPARATOR . $newName;
                    @rename($realFile, $dest);
                }
            }
            $del = $pdo->prepare("DELETE FROM maintenance_uploads WHERE id IN ($place)");
            $del->execute($ids);
            echo json_encode(['ok'=>true]); exit;
        }
        http_response_code(400); echo json_encode(['error'=>'Unknown action']); exit;
    }

    if($method === 'DELETE'){
        // Permanent delete — requires admin token in header X-Admin-Token matching EPM_ADMIN_TOKEN env var
        $adminToken = getenv('EPM_ADMIN_TOKEN') ?: null;
        $provided = isset($_SERVER['HTTP_X_ADMIN_TOKEN']) ? $_SERVER['HTTP_X_ADMIN_TOKEN'] : null;

        $id = null;
        if(isset($_GET['id'])){
            $id = intval($_GET['id']);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if(is_array($data) && isset($data['id'])) $id = intval($data['id']);
        }
        if(!$id){ http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

        if(!$adminToken || !$provided || $provided !== $adminToken){
            http_response_code(403); echo json_encode(['error'=>'Forbidden: admin token required for permanent delete']); exit;
        }

        // fetch row
        $stmt = $pdo->prepare('SELECT * FROM maintenance_uploads WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if(!$row){ http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

        // determine paths
        $uploadsDir = realpath(__DIR__ . '/../uploads');
        $trashDir = realpath(__DIR__ . '/../uploads/trash');
        if($trashDir === false) { $trashDir = __DIR__ . '/../uploads/trash'; if(!is_dir($trashDir)) @mkdir($trashDir,0755,true); }
        $filePath = __DIR__ . '/../' . ltrim($row['file_path'], '/\\');
        $realFile = realpath($filePath);

        // If file is already in the trash folder -> require admin token for permanent delete
        $inTrash = false;
        if($realFile && $uploadsDir !== false){
            $inTrash = (strpos($realFile, realpath($trashDir)) === 0);
        } elseif($realFile){
            // fallback check for 'uploads/trash' in path
            $inTrash = (stripos($realFile, DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'trash'.DIRECTORY_SEPARATOR) !== false);
        }

        if($inTrash){
            // permanent delete — require admin token
            if(!$adminToken || !$provided || $provided !== $adminToken){ http_response_code(403); echo json_encode(['error'=>'Forbidden: admin token required for permanent delete']); exit; }
            // delete file from trash if exists
            if($realFile && file_exists($realFile)){
                @unlink($realFile);
            }
            // remove DB record
            $del = $pdo->prepare('DELETE FROM maintenance_uploads WHERE id = ?');
            $del->execute([$id]);
            echo json_encode(['ok'=>true,'permanently_deleted'=>true]);
            exit;
        } else {
            // Not in trash: treat DELETE as a soft-delete (no admin token required). Mark deleted flag.
            $deletedBy = isset($_SERVER['HTTP_X_USER']) ? $_SERVER['HTTP_X_USER'] : (isset($_SERVER['HTTP_X_USER_NAME']) ? $_SERVER['HTTP_X_USER_NAME'] : null);
            $stmt = $pdo->prepare('UPDATE maintenance_uploads SET deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?');
            $stmt->execute([$deletedBy, $id]);
            echo json_encode(['ok'=>true,'soft_deleted'=>true]);
            exit;
        }
    }

    // Bulk actions
    if($method === 'POST'){
        // handled above single-action; duplicate-protect: read body again
    }

    // support bulk actions via a separate route: accept JSON { action: 'bulk_permanent_delete'|'bulk_restore', ids: [..] }
    // Note: we previously handled POST for single actions; to keep compatibility, read body here only if not already used.
    $raw = file_get_contents('php://input');
    $maybe = @json_decode($raw, true);
    if(is_array($maybe) && isset($maybe['action']) && isset($maybe['ids']) && is_array($maybe['ids'])){
        $action = $maybe['action'];
        $ids = array_map('intval', $maybe['ids']);
        if($action === 'bulk_restore'){
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE maintenance_uploads SET deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id IN ($in)");
            $stmt->execute($ids);
            echo json_encode(['ok'=>true]); exit;
        }
        if($action === 'bulk_permanent_delete'){
            $adminToken = getenv('EPM_ADMIN_TOKEN') ?: null;
            $provided = isset($_SERVER['HTTP_X_ADMIN_TOKEN']) ? $_SERVER['HTTP_X_ADMIN_TOKEN'] : null;
            if(!$adminToken || !$provided || $provided !== $adminToken){ http_response_code(403); echo json_encode(['error'=>'Forbidden: admin token required']); exit; }
            // fetch rows
            $place = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM maintenance_uploads WHERE id IN ($place)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll();
            $uploadsDir = realpath(__DIR__ . '/../uploads');
            $trashDir = __DIR__ . '/../uploads/trash'; if(!is_dir($trashDir)) @mkdir($trashDir,0755,true);
            foreach($rows as $row){
                $filePath = __DIR__ . '/../' . ltrim($row['file_path'], '/\\');
                $realFile = realpath($filePath);
                if($realFile && strpos($realFile, $uploadsDir) === 0 && file_exists($realFile)){
                    $base = basename($realFile);
                    $newName = time() . '_' . $row['id'] . '_' . $base;
                    $dest = realpath($trashDir) . DIRECTORY_SEPARATOR . $newName;
                    @rename($realFile, $dest);
                }
            }
            // delete DB rows
            $del = $pdo->prepare("DELETE FROM maintenance_uploads WHERE id IN ($place)");
            $del->execute($ids);
            echo json_encode(['ok'=>true]); exit;
        }
    }

    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
}catch(Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}

?>
