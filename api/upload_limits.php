<?php
// Return server PHP upload limits in bytes and human-readable form
require_once __DIR__ . '/../db-config.php';
header('Content-Type: application/json; charset=utf-8');

function parse_shorthand_bytes($val){
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $num = (int)$val;
    if($last === 'g') $num *= 1024 * 1024 * 1024;
    elseif($last === 'm') $num *= 1024 * 1024;
    elseif($last === 'k') $num *= 1024;
    return $num;
}

$u = ini_get('upload_max_filesize');
$p = ini_get('post_max_size');
$m = ini_get('memory_limit');

$data = [
    'upload_max_filesize' => $u,
    'post_max_size' => $p,
    'memory_limit' => $m,
    'upload_max_filesize_bytes' => parse_shorthand_bytes($u),
    'post_max_size_bytes' => parse_shorthand_bytes($p),
    'memory_limit_bytes' => parse_shorthand_bytes($m),
];

echo json_encode($data);
exit;

?>
