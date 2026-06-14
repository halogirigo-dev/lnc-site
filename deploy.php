<?php
set_time_limit(0);
ignore_user_abort(true);
error_reporting(0);

define('WEBHOOK_SECRET', '05c6dd13084db225b514e3c71fa555a86567c38d57ffa352e73a323b7e0b89de');

$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected  = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Forbidden');
}

$data = json_decode($payload, true);
if (($data['ref'] ?? '') !== 'refs/heads/main') {
    http_response_code(200);
    exit('Ignored: not main branch');
}

$repo_path = escapeshellarg(realpath(__DIR__));
$git       = trim(shell_exec('which git') ?? '/usr/bin/git');

$out  = shell_exec("cd {$repo_path} && {$git} fetch origin main 2>&1") ?? '';
$out .= shell_exec("cd {$repo_path} && {$git} reset --hard origin/main 2>&1") ?? '';

http_response_code(200);
header('Content-Type: text/plain');
echo "OK\n" . $out;
