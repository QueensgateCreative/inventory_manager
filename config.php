<?php
// config.php

require_once 'includes/session.php';

if (!defined('APP_DEBUG')) define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once __DIR__ . '/db.php';

session_start();