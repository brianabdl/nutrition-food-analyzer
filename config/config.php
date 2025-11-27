<?php
/**
 * Application Configuration
 */

// File Paths (must be defined first)
define('ROOT_PATH', dirname(__DIR__));

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/error.log');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Application Settings
define('APP_NAME', 'Food Nutrition Analyzer');
define('APP_VERSION', '2.0');
define('BASE_URL', '/');
define('ROOT_URL', ''); // Empty for root directory, or '/subdirectory' if in subdirectory
define('CONFIG_PATH', ROOT_PATH . '/config');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CACHE_PATH', ROOT_PATH . '/cache');

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 3600); // 1 hour in seconds

// Pagination Settings
define('ITEMS_PER_PAGE', 10);
define('MAX_COMPARISON_ITEMS', 5);

// API Settings
define('API_VERSION', 'v1');
define('API_RESPONSE_FORMAT', 'json');

// Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
ini_set('session.cookie_path', '/');
ini_set('session.cookie_samesite', 'Lax'); // CSRF protection

// CORS Settings (for AJAX requests)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Create necessary directories
$dirs = [CACHE_PATH, dirname(ROOT_PATH . '/data/nutrition.db')];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Ensure writable permissions
@chmod(CACHE_PATH, 0777);
@chmod(dirname(ROOT_PATH . '/data/nutrition.db'), 0777);
