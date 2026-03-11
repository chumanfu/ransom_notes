<?php
/**
 * One-off debug script. Request https://your-site.com/test-debug.php
 * If you see "OK" and public/crash.log contains "test run", PHP and public/ are working.
 * DELETE this file after debugging.
 */
$crashLog = __DIR__ . '/crash.log';
$msg = 'test run at ' . date('c') . ' -- request: ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
@file_put_contents($crashLog, $msg, FILE_APPEND);
header('Content-Type: text/plain');
echo 'OK';
