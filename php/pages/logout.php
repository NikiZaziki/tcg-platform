<?php
/**
 * Logout Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AuthService.php';

$auth = new AuthService();

// Session beenden
$auth->logout();

// Redirect zu Login
redirect('/pages/login.php');
