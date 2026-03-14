<?php
// migrate.php
// This script reads the Firebase export JSON and inserts data into MySQL
// Run: php migrate.php

require_once __DIR__ . '/db-config.php';

$exportFile = __DIR__ . '/preventive-maintenance-2b1b7-default-rtdb-export.json';
if (!file_exists($exportFile)) {
    echo "Export file not found: $exportFile\n";
    exit(1);
}

$json = json_decode(file_get_contents($exportFile), true);
if ($json === null) {
    echo "Failed to decode JSON\n";
    exit(1);
}

$pdo->beginTransaction();
try {
    // maintenance records
    if (isset($json['epm_records_v1']['records']) && is_array($json['epm_records_v1']['records'])) {
        echo "Importing maintenance records...\n";
        $stmt = $pdo->prepare('INSERT INTO maintenance_records
            (BranchCode,BranchName,location,equipment,task,status,performedBy,verifiedBy,date,nextDue,notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($json['epm_records_v1']['records'] as $r) {
            $stmt->execute([
                $r['BranchCode'] ?? null,
                $r['BranchName'] ?? null,
                $r['location'] ?? null,
                $r['equipment'] ?? null,
                $r['task'] ?? null,
                $r['status'] ?? null,
                $r['performedBy'] ?? null,
                $r['verifiedBy'] ?? null,
                isset($r['date']) ? date('Y-m-d', strtotime($r['date'])) : null,
                isset($r['nextDue']) ? date('Y-m-d', strtotime($r['nextDue'])) : null,
                $r['notes'] ?? null
            ]);
        }
        echo "  done (" . count($json['epm_records_v1']['records']) . " rows)\n";
    }

    // users (if present)
    if (isset($json['users']) && is_array($json['users'])) {
        echo "Importing users...\n";
        $stmt = $pdo->prepare('INSERT INTO users
            (identifier,full_name,role,user_uid,password_hash,salt,created_at,last_signed_in)
            VALUES (?,?,?,?,?,?,?,?)');
        foreach ($json['users'] as $u) {
            $stmt->execute([
                $u['identifier'] ?? null,
                $u['full_name'] ?? null,
                $u['role'] ?? 'user',
                $u['user_uid'] ?? null,
                $u['passwordHash'] ?? null,
                $u['salt'] ?? null,
                isset($u['created']) ? date('Y-m-d H:i:s', strtotime($u['created'])) : null,
                isset($u['signed_in']) ? date('Y-m-d H:i:s', strtotime($u['signed_in'])) : null
            ]);
        }
        echo "  done (" . count($json['users']) . " rows)\n";
    }

    // load balancing
    if (isset($json['epm_load_balancing_v1']['records']) && is_array($json['epm_load_balancing_v1']['records'])) {
        echo "Importing load balancing records...\n";
        $stmt = $pdo->prepare('INSERT INTO load_balancing_records
            (branch,company,location,panel,mcb,mainV,wires,conductedBy,witnessBy,certifiedBy,
             witnessTenant,circType,date,time,refId,remarks,vll,vln,vph,nameplate,meas_ia,meas_ib,
             meas_ic,meas_van,meas_vbn,meas_vcn,pf_a,pf_b,pf_c,phaseA,phaseB,phaseC,totalLoad,loads,fileName,savedAt)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($json['epm_load_balancing_v1']['records'] as $r) {
            // normalize date/time so MySQL accepts them (accepts DD/MM/YYYY exports)
            $lb_date = null;
            if (isset($r['date']) && $r['date'] !== '') {
                $ts = strtotime(str_replace('/', '-', $r['date']));
                if ($ts !== false) {
                    $lb_date = date('Y-m-d', $ts);
                }
            }
            $lb_time = null;
            if (isset($r['time']) && $r['time'] !== '') {
                $ts2 = strtotime($r['time']);
                if ($ts2 !== false) {
                    $lb_time = date('H:i:s', $ts2);
                }
            }

            // coerce numeric fields: convert empty strings to null
            $num = function($key) use ($r) {
                if (!isset($r[$key]) || $r[$key] === '') return null;
                return is_numeric($r[$key]) ? $r[$key] : null;
            };

            $stmt->execute([
                $r['branch'] ?? null,
                $r['company'] ?? null,
                $r['location'] ?? null,
                $r['panel'] ?? null,
                $num('mcb'),
                $num('mainV'),
                $num('wires'),
                $r['conductedBy'] ?? null,
                $r['witnessBy'] ?? null,
                $r['certifiedBy'] ?? null,
                $r['witnessTenant'] ?? null,
                $r['circType'] ?? null,
                $lb_date,
                $lb_time,
                $r['refId'] ?? null,
                $r['remarks'] ?? null,
                $num('vll'),
                $num('vln'),
                $num('vph'),
                $num('nameplate'),
                $num('meas_ia'),
                $num('meas_ib'),
                $num('meas_ic'),
                $num('meas_van'),
                $num('meas_vbn'),
                $num('meas_vcn'),
                $num('pf_a'),
                $num('pf_b'),
                $num('pf_c'),
                $num('phaseA'),
                $num('phaseB'),
                $num('phaseC'),
                $num('totalLoad'),
                isset($r['loads']) ? json_encode($r['loads']) : null,
                $r['fileName'] ?? null,
                isset($r['savedAt']) ? date('Y-m-d H:i:s', strtotime($r['savedAt'])) : null
            ]);
        }
        echo "  done (" . count($json['epm_load_balancing_v1']['records']) . " rows)\n";
    }

    // pm checklist
    if (isset($json['epm_pm_checklist_v1']['records']) && is_array($json['epm_pm_checklist_v1']['records'])) {
        echo "Importing PM checklist records...\n";
        $stmt = $pdo->prepare('INSERT INTO pm_checklist
            (branchName,branchCode,location,date,conductedBy,refNumber,
             sigConducted,sigWitnessed,sigStoreManager,sigStoreRep,overallRecommendations,categories,savedAt)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($json['epm_pm_checklist_v1']['records'] as $r) {
            // normalize pm checklist date (accept DD/MM/YYYY)
            $pm_date = null;
            if (isset($r['date']) && $r['date'] !== '') {
                $ts = strtotime(str_replace('/', '-', $r['date']));
                if ($ts !== false) {
                    $pm_date = date('Y-m-d', $ts);
                }
            }

            $stmt->execute([
                $r['branchName'] ?? null,
                $r['branchCode'] ?? null,
                $r['location'] ?? null,
                $pm_date,
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
        echo "  done (" . count($json['epm_pm_checklist_v1']['records']) . " rows)\n";
    }

    // megger tests
    if (isset($json['megger_test_files_v1']['records']) && is_array($json['megger_test_files_v1']['records'])) {
        echo "Importing megger test files...\n";
        $stmt = $pdo->prepare('INSERT INTO megger_tests (name,savedAt,data) VALUES (?,?,?)');
        foreach ($json['megger_test_files_v1']['records'] as $r) {
            $stmt->execute([
                $r['name'] ?? null,
                isset($r['savedAt']) ? date('Y-m-d H:i:s', strtotime($r['savedAt'])) : null,
                isset($r['data']) ? json_encode($r['data']) : null
            ]);
        }
        echo "  done (" . count($json['megger_test_files_v1']['records']) . " rows)\n";
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
