<?php
// First line: prove this script ran (root entry point)
@file_put_contents(__DIR__.'/public/crash.log', date('c').' ENTRY-ROOT '.($_SERVER['REQUEST_METHOD']??'').' '.($_SERVER['REQUEST_URI']??'')."\n", FILE_APPEND);
/**
 * Entry point when the server's document root is the project root (not public/).
 * Forwards to Laravel's front controller.
 */
require __DIR__.'/public/index.php';
