<?php
$DB_HOST = 'localhost';
$DB_NAME = 'shmasstest';
$DB_USER = 'root';
$DB_PASS = '';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
