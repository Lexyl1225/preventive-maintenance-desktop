<?php
// Simple endpoint to overwrite a collection with a full array of records.
// Used by client code as a fallback when individual APIs are unsuitable.

require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json');

// Helper to safely convert various date/time strings to formatted dates.
function safe_format_date($format, $value){
    if (!isset($value)) return null;
    $v = is_string($value) ? trim($value) : $value;
    if ($v === '' || $v === null) return null;
    $ts = strtotime($v);
    if ($ts === false || $ts === -1) return null;
    return date($format, $ts);
}

$collection = $_GET['collection'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

try {
    switch ($collection) {
        case 'epm_records_v1':
            // Upsert to preserve IDs so maintenance_uploads.record_id links stay valid.
            $incomingIds = [];
            $insertStmt = $pdo->prepare('INSERT INTO maintenance_records (BranchCode,BranchName,location,equipment,task,status,performedBy,verifiedBy,date,nextDue,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $updateStmt = $pdo->prepare('UPDATE maintenance_records SET BranchCode=?,BranchName=?,location=?,equipment=?,task=?,status=?,performedBy=?,verifiedBy=?,date=?,nextDue=?,notes=? WHERE id=?');

            foreach ($data as $r) {
                $params = [
                    $r['BranchCode'] ?? null,
                    $r['BranchName'] ?? null,
                    $r['location'] ?? null,
                    $r['equipment'] ?? null,
                    $r['task'] ?? null,
                    $r['status'] ?? null,
                    $r['performedBy'] ?? null,
                    $r['verifiedBy'] ?? null,
                    safe_format_date('Y-m-d', $r['date']),
                    safe_format_date('Y-m-d', $r['nextDue']),
                    $r['notes'] ?? null
                ];
                if (isset($r['id']) && $r['id'] !== null) {
                    $id = intval($r['id']);
                    $chk = $pdo->prepare('SELECT id FROM maintenance_records WHERE id = ?');
                    $chk->execute([$id]);
                    if ($chk->fetch()) {
                        $updateStmt->execute(array_merge($params, [$id]));
                    } else {
                        $insertStmt->execute($params);
                        $id = intval($pdo->lastInsertId());
                    }
                    $incomingIds[] = $id;
                } else {
                    $insertStmt->execute($params);
                    $incomingIds[] = intval($pdo->lastInsertId());
                }
            }

            // Remove records no longer in the payload
            if (count($incomingIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($incomingIds), '?'));
                $delStmt = $pdo->prepare("DELETE FROM maintenance_records WHERE id NOT IN ($placeholders)");
                $delStmt->execute($incomingIds);
            } else {
                $pdo->exec('DELETE FROM maintenance_records');
            }
            break;
        case 'epm_load_balancing_v1':
            $pdo->exec('DELETE FROM load_balancing_records');
            $stmt = $pdo->prepare('INSERT INTO load_balancing_records (branch,company,location,panel,mcb,mainV,wires,conductedBy,witnessBy,certifiedBy,witnessTenant,circType,date,time,refId,remarks,vll,vln,vph,nameplate,meas_ia,meas_ib,meas_ic,meas_van,meas_vbn,meas_vcn,pf_a,pf_b,pf_c,phaseA,phaseB,phaseC,totalLoad,loads,fileName,savedAt) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            foreach ($data as $r) {
                $stmt->execute([
                    $r['branch'] ?? null,
                    $r['company'] ?? null,
                    $r['location'] ?? null,
                    $r['panel'] ?? null,
                    $r['mcb'] ?? null,
                    $r['mainV'] ?? null,
                    $r['wires'] ?? null,
                    $r['conductedBy'] ?? null,
                    $r['witnessBy'] ?? null,
                    $r['certifiedBy'] ?? null,
                    $r['witnessTenant'] ?? null,
                    $r['circType'] ?? null,
                    $r['date'] ?? null,
                    $r['time'] ?? null,
                    $r['refId'] ?? null,
                    $r['remarks'] ?? null,
                    $r['vll'] ?? null,
                    $r['vln'] ?? null,
                    $r['vph'] ?? null,
                    $r['nameplate'] ?? null,
                    $r['meas_ia'] ?? null,
                    $r['meas_ib'] ?? null,
                    $r['meas_ic'] ?? null,
                    $r['meas_van'] ?? null,
                    $r['meas_vbn'] ?? null,
                    $r['meas_vcn'] ?? null,
                    $r['pf_a'] ?? null,
                    $r['pf_b'] ?? null,
                    $r['pf_c'] ?? null,
                    $r['phaseA'] ?? null,
                    $r['phaseB'] ?? null,
                    $r['phaseC'] ?? null,
                    $r['totalLoad'] ?? null,
                    isset($r['loads']) ? json_encode($r['loads']) : null,
                    $r['fileName'] ?? null,
                    isset($r['savedAt']) ? date('Y-m-d H:i:s', strtotime($r['savedAt'])) : null
                ]);
            }
            break;
        case 'megger_test_files_v1':
            $pdo->exec('DELETE FROM megger_tests');
            $stmt = $pdo->prepare('INSERT INTO megger_tests (name,savedAt,data) VALUES (?,?,?)');
            foreach ($data as $r) {
                $stmt->execute([
                    $r['name'] ?? null,
                    isset($r['savedAt']) ? date('Y-m-d H:i:s', strtotime($r['savedAt'])) : null,
                    isset($r['data']) ? json_encode($r['data']) : null
                ]);
            }
            break;
        case 'epm_pm_checklist_v1':
            $pdo->exec('DELETE FROM pm_checklist');
            $stmt = $pdo->prepare('INSERT INTO pm_checklist (branchName,branchCode,location,date,conductedBy,refNumber,sigConducted,sigWitnessed,sigStoreManager,sigStoreRep,overallRecommendations,categories,savedAt) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            foreach ($data as $r) {
                $stmt->execute([
                    $r['branchName'] ?? null,
                    $r['branchCode'] ?? null,
                    $r['location'] ?? null,
                    $r['date'] ?? null,
                    $r['conductedBy'] ?? null,
                    $r['refNumber'] ?? null,
                    $r['sigConducted'] ?? null,
                    $r['sigWitnessed'] ?? null,
                    $r['sigStoreManager'] ?? null,
                    $r['sigStoreRep'] ?? null,
                    $r['overallRecommendations'] ?? null,
                    isset($r['categories']) ? json_encode($r['categories']) : null,
                    isset($r['savedAt']) ? date('Y-m-d H:i:s', strtotime($r['savedAt'])) : null
                ]);
            }
            break;
        default:
            // unsupported
            http_response_code(400);
            echo json_encode(['error'=>'Unknown collection']);
            exit;
    }
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
