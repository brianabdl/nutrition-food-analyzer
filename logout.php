<?php
/**
 * Logout Page
 * Destroy user session and redirect to login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/session.php';
require_once MODELS_PATH . '/User.php';

// Destroy session in database
if (isLoggedIn()) {
    $userModel = new User();
    $userModel->destroySession(session_id());
}

// Destroy PHP session
destroyUserSession();

// Redirect to login page
header('Location: login.php');
exit;
