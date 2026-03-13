<?php
// Полностью убираем сложные настройки
define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASS', '');
date_default_timezone_set('Europe/Moscow');

// Убираем все ini_set и session_start() отсюда