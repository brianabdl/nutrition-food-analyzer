<?php
/**
 * Active Users API
 * Returns list of currently logged in users for real-time display
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once MODELS_PATH . '/User.php';

try {
    $userModel = new User();
    
    // Update current user's activity if logged in
    if (isLoggedIn()) {
        $userModel->updateSessionActivity(session_id());
    }
    
    // Clean up expired sessions
    $userModel->cleanupExpiredSessions();
    
    // Get active users
    $activeUsers = $userModel->getActiveUsers();
    
    // Format response
    $response = [
        'success' => true,
        'data' => [
            'count' => count($activeUsers),
            'users' => $activeUsers,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Active users API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch active users'
    ]);
}
