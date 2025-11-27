<?php
/**
 * User Model
 * Handles user authentication and management
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $table = 'users';
    private $sessionsTable = 'user_sessions';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->initializeTables();
    }
    
    /**
     * Initialize users and sessions tables
     */
    private function initializeTables() {
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nim VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_nim (nim)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
        
        // Create user sessions table for real-time tracking
        $sql = "CREATE TABLE IF NOT EXISTS {$this->sessionsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            nim VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            session_id VARCHAR(255) UNIQUE NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active TINYINT(1) DEFAULT 1,
            FOREIGN KEY (user_id) REFERENCES {$this->table}(id) ON DELETE CASCADE,
            INDEX idx_session (session_id),
            INDEX idx_active (is_active),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->query($sql);
        
        // Create demo user if not exists
        $this->createDemoUsers();
    }
    
    /**
     * Create demo users for testing
     */
    private function createDemoUsers() {
        $demoUsers = [
            ['nim' => '123456789', 'name' => 'Demo User 1', 'password' => 'password123'],
            ['nim' => '987654321', 'name' => 'Demo User 2', 'password' => 'password123'],
            ['nim' => '111222333', 'name' => 'Admin User', 'password' => 'admin123']
        ];
        
        foreach ($demoUsers as $user) {
            $checkStmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE nim = ?");
            $checkStmt->bind_param('s', $user['nim']);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    INSERT INTO {$this->table} (nim, name, password)
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param('sss', $user['nim'], $user['name'], $hashedPassword);
                $stmt->execute();
                $stmt->close();
            }
            $checkStmt->close();
        }
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($nim, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, nim, name, password 
                FROM {$this->table} 
                WHERE nim = ?
                LIMIT 1
            ");
            
            $stmt->bind_param('s', $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if ($user && password_verify($password, $user['password'])) {
                return [
                    'id' => $user['id'],
                    'nim' => $user['nim'],
                    'name' => $user['name']
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Register new user
     */
    public function register($nim, $name, $password) {
        try {
            // Check if NIM already exists
            $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE nim = ?");
            $stmt->bind_param('s', $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $stmt->close();
                return ['success' => false, 'message' => 'NIM already registered'];
            }
            $stmt->close();
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (nim, name, password)
                VALUES (?, ?, ?)
            ");
            
            $stmt->bind_param('sss', $nim, $name, $hashedPassword);
            
            if ($stmt->execute()) {
                $userId = $stmt->insert_id;
                $stmt->close();
                return [
                    'success' => true,
                    'user' => [
                        'id' => $userId,
                        'nim' => $nim,
                        'name' => $name
                    ]
                ];
            }
            
            $stmt->close();
            return ['success' => false, 'message' => 'Failed to register user'];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }
    
    /**
     * Create user session for tracking
     */
    public function createSession($userId, $nim, $name) {
        try {
            $sessionId = session_id();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Deactivate old sessions for this user
            $stmt = $this->db->prepare("
                UPDATE {$this->sessionsTable} 
                SET is_active = 0 
                WHERE user_id = ?
            ");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
            
            // Create new session
            $stmt = $this->db->prepare("
                INSERT INTO {$this->sessionsTable} 
                (user_id, nim, name, session_id, ip_address, user_agent, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                    login_time = CURRENT_TIMESTAMP,
                    last_activity = CURRENT_TIMESTAMP,
                    is_active = 1
            ");
            
            $stmt->bind_param('isssss', $userId, $nim, $name, $sessionId, $ipAddress, $userAgent);
            $stmt->execute();
            $stmt->close();
            
            return true;
            
        } catch (Exception $e) {
            error_log("Create session error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update session activity
     */
    public function updateSessionActivity($sessionId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->sessionsTable} 
                SET last_activity = CURRENT_TIMESTAMP 
                WHERE session_id = ? AND is_active = 1
            ");
            
            $stmt->bind_param('s', $sessionId);
            $stmt->execute();
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Update session error: " . $e->getMessage());
        }
    }
    
    /**
     * Destroy user session
     */
    public function destroySession($sessionId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->sessionsTable} 
                SET is_active = 0 
                WHERE session_id = ?
            ");
            
            $stmt->bind_param('s', $sessionId);
            $stmt->execute();
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Destroy session error: " . $e->getMessage());
        }
    }
    
    /**
     * Get all active users (logged in within last 5 minutes)
     */
    public function getActiveUsers() {
        try {
            $result = $this->db->query("
                SELECT 
                    nim,
                    name,
                    login_time,
                    last_activity,
                    TIMESTAMPDIFF(SECOND, last_activity, NOW()) as idle_seconds
                FROM {$this->sessionsTable}
                WHERE is_active = 1 
                  AND TIMESTAMPDIFF(MINUTE, last_activity, NOW()) < 30
                ORDER BY last_activity DESC
            ");
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            return $users;
            
        } catch (Exception $e) {
            error_log("Get active users error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean up expired sessions (older than 30 minutes)
     */
    public function cleanupExpiredSessions() {
        try {
            $this->db->query("
                UPDATE {$this->sessionsTable} 
                SET is_active = 0 
                WHERE is_active = 1 
                  AND TIMESTAMPDIFF(MINUTE, last_activity, NOW()) > 30
            ");
        } catch (Exception $e) {
            error_log("Cleanup sessions error: " . $e->getMessage());
        }
    }
}
