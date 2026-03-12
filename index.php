<?php
// Optional: prove this script ran when doc root is project root (log to storage/logs)
$logDir = __DIR__.'/storage/logs';
if (is_dir($logDir) || @mkdir($logDir, 0755, true)) {
    @file_put_contents($logDir.'/crash.log', date('c').' ENTRY-ROOT '.($_SERVER['REQUEST_METHOD']??'').' '.($_SERVER['REQUEST_URI']??'')."\n", FILE_APPEND);
}
/**
 * Entry point when the server's document root is the project root (not public/).
 * Forwards to Laravel's front controller.
 */
require __DIR__.'/public/index.php';
