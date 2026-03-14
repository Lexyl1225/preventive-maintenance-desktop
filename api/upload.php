<?php
require_once __DIR__ . '/../db-config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if(empty($_FILES) || !isset($_FILES['file'])){
        echo json_encode(['ok'=>false,'error'=>'No file uploaded']);
        exit;
    }

    $file = $_FILES['file'];
    if($file['error'] !== UPLOAD_ERR_OK){
        echo json_encode(['ok'=>false,'error'=>'Upload error code: '.$file['error']]);
        exit;
    }

    $allowedExt = ['jpg','jpeg','png','gif','mp4','mov','webm','avi','mkv'];
    $origName = $file['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if(!in_array($ext, $allowedExt)){
        echo json_encode(['ok'=>false,'error'=>'Invalid file type']);
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $safeBase = time() . '_' . bin2hex(random_bytes(6));
    $targetName = $safeBase . '.' . $ext;
    $targetPath = $uploadDir . '/' . $targetName;

    if(!move_uploaded_file($file['tmp_name'], $targetPath)){
        echo json_encode(['ok'=>false,'error'=>'Failed to move uploaded file']);
        exit;
    }

    $relPath = 'uploads/' . $targetName;
    $record_meta = null;
    if(isset($_POST['record_meta'])){
        $record_meta = $_POST['record_meta'];
    }

    // try to extract record_id if provided
    $recordId = null;
    if(isset($_POST['record_id'])){
        $rid = trim((string)$_POST['record_id']);
        if($rid !== '') $recordId = intval($rid);
    } else {
        // try to parse record_meta if it's JSON
        if(is_string($record_meta)){
            $maybe = @json_decode($record_meta, true);
            if(is_array($maybe) && isset($maybe['id'])) $recordId = intval($maybe['id']);
        }
    }

    // store metadata in DB (best-effort)
    try{
        $stmt = $pdo->prepare("INSERT INTO maintenance_uploads (record_id, file_name, file_path, file_type, file_size, uploaded_at, uploader) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $uploader = is_string($record_meta) ? $record_meta : null;
        $stmt->execute([ $recordId, $origName, $relPath, $file['type'], $file['size'], $uploader ]);
        $insertId = $pdo->lastInsertId();
    }catch(Exception $e){
        echo json_encode(['ok'=>true,'path'=>$relPath,'db_error'=>$e->getMessage()]);
        exit;
    }

    echo json_encode(['ok'=>true,'path'=>$relPath,'id'=>$insertId,'record_id'=>$recordId]);
    exit;

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=> $ex->getMessage()]);
    exit;
}

?>
