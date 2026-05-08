<?php
require_once 'config.php';

// Session beenden
session_unset();
session_destroy();

// Redirect zu Login
header('Location: login.php');
exit;
