<?php
/**
 * Session Management
 * Handles user session and authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['nim']);
}

/**
 * Require login (redirect to login page if not logged in)
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        $redirectUrl = ROOT_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Get current user info
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nim' => $_SESSION['nim'] ?? null,
        'name' => $_SESSION['name'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null
    ];
}

/**
 * Set user session
 */
function setUserSession($userId, $nim, $name)
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['nim'] = $nim;
    $_SESSION['name'] = $name;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

/**
 * Update last activity
 */
function updateLastActivity()
{
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Destroy user session (logout)
 */
function destroyUserSession()
{
    // Clear all session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();
}

/**
 * Check if session is expired (30 minutes of inactivity)
 */
function isSessionExpired()
{
    if (!isLoggedIn()) {
        return true;
    }

    $timeout = 1800; // 30 minutes
    $lastActivity = $_SESSION['last_activity'] ?? 0;

    if (time() - $lastActivity > $timeout) {
        return true;
    }

    return false;
}

// Auto-update last activity on every request (only if already logged in)
if (isLoggedIn()) {
    if (isSessionExpired()) {
        destroyUserSession();
    } else {
        updateLastActivity();
    }
}
