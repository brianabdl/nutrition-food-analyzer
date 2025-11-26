<?php
/**
 * Database Configuration
 * Handles database connections and initialization using MySQLi
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // MySQL Configuration (use environment variables or defaults)
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    
    private function __construct() {
        // Get database config from environment or use defaults
        $this->host = getenv('DB_HOST') ?: 'mysql';
        $this->username = getenv('DB_USER') ?: 'nutrition_user';
        $this->password = getenv('DB_PASSWORD') ?: 'nutrition_password';
        $this->database = getenv('DB_NAME') ?: 'nutrition_db';
        $this->port = getenv('DB_PORT') ?: 3306;
        
        try {
            // Create MySQLi connection
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port
            );
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to utf8mb4
            $this->connection->set_charset("utf8mb4");
            
            // Initialize database tables if they don't exist
            $this->initializeTables();
            
        } catch(Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Initialize database tables from CSV files
     */
    private function initializeTables() {
        // Check if tables already exist
        $result = $this->connection->query(
            "SHOW TABLES LIKE 'foods'"
        );
        
        if ($result && $result->num_rows > 0) {
            return; // Tables already exist
        }
        
        // Create foods table
        $sql = "CREATE TABLE IF NOT EXISTS foods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            menu VARCHAR(255) UNIQUE NOT NULL,
            energy_kj DECIMAL(10,2),
            protein_g DECIMAL(10,2),
            fat_g DECIMAL(10,2),
            carbohydrates_g DECIMAL(10,2),
            dietary_fiber_g DECIMAL(10,2),
            pufa_g DECIMAL(10,2),
            cholesterol_mg DECIMAL(10,2),
            vitamin_a_mg DECIMAL(10,2),
            vitamin_e_mg DECIMAL(10,2),
            vitamin_b1_mg DECIMAL(10,2),
            vitamin_b2_mg DECIMAL(10,2),
            vitamin_b6_mg DECIMAL(10,2),
            total_folic_acid_ug DECIMAL(10,2),
            vitamin_c_mg DECIMAL(10,2),
            sodium_mg DECIMAL(10,2),
            potassium_mg DECIMAL(10,2),
            calcium_mg DECIMAL(10,2),
            magnesium_mg DECIMAL(10,2),
            phosphorus_mg DECIMAL(10,2),
            iron_mg DECIMAL(10,2),
            zinc_mg DECIMAL(10,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_menu (menu)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$this->connection->query($sql)) {
            error_log("Error creating foods table: " . $this->connection->error);
        }
        
        // Create standards table
        $sql = "CREATE TABLE IF NOT EXISTS standards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nutrisi VARCHAR(255) UNIQUE NOT NULL,
            minimum DECIMAL(10,2),
            maximum DECIMAL(10,2),
            rekomendasi_harian TEXT,
            fungsi_zat TEXT,
            dampak_kelebihan TEXT,
            dampak_kekurangan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (!$this->connection->query($sql)) {
            error_log("Error creating standards table: " . $this->connection->error);
        }
        
        // Import data from CSV files
        $this->importFromCSV();
    }
    
    /**
     * Import data from CSV files
     */
    private function importFromCSV() {
        $foods_csv = __DIR__ . '/../foods.csv';
        $standards_csv = __DIR__ . '/../standard-nutrition.csv';
        
        // Import foods data
        if (file_exists($foods_csv)) {
            $this->importFoodsCSV($foods_csv);
        }
        
        // Import standards data
        if (file_exists($standards_csv)) {
            $this->importStandardsCSV($standards_csv);
        }
    }
    
    /**
     * Import foods from CSV
     */
    private function importFoodsCSV($file_path) {
        $handle = fopen($file_path, 'r');
        if (!$handle) return;
        
        // Read header
        $headers = fgetcsv($handle);
        if (!$headers) return;
        
        // Prepare insert statement
        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO foods (
                menu, energy_kj, protein_g, fat_g, carbohydrates_g, 
                dietary_fiber_g, pufa_g, cholesterol_mg, vitamin_a_mg, 
                vitamin_e_mg, vitamin_b1_mg, vitamin_b2_mg, vitamin_b6_mg, 
                total_folic_acid_ug, vitamin_c_mg, sodium_mg, potassium_mg, 
                calcium_mg, magnesium_mg, phosphorus_mg, iron_mg, zinc_mg
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->connection->error);
            fclose($handle);
            return;
        }
        
        // Import each row
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 23) continue;
            
            $stmt->bind_param("sddddddddddddddddddddd",
                $row[1],  // menu
                $this->parseFloat($row[2]),   // energy_kj
                $this->parseFloat($row[3]),   // protein_g
                $this->parseFloat($row[4]),   // fat_g
                $this->parseFloat($row[5]),   // carbohydrates_g
                $this->parseFloat($row[6]),   // dietary_fiber_g
                $this->parseFloat($row[7]),   // pufa_g
                $this->parseFloat($row[8]),   // cholesterol_mg
                $this->parseFloat($row[9]),   // vitamin_a_mg
                $this->parseFloat($row[10]),  // vitamin_e_mg
                $this->parseFloat($row[11]),  // vitamin_b1_mg
                $this->parseFloat($row[12]),  // vitamin_b2_mg
                $this->parseFloat($row[13]),  // vitamin_b6_mg
                $this->parseFloat($row[14]),  // total_folic_acid_ug
                $this->parseFloat($row[15]),  // vitamin_c_mg
                $this->parseFloat($row[16]),  // sodium_mg
                $this->parseFloat($row[17]),  // potassium_mg
                $this->parseFloat($row[18]),  // calcium_mg
                $this->parseFloat($row[19]),  // magnesium_mg
                $this->parseFloat($row[20]),  // phosphorus_mg
                $this->parseFloat($row[21]),  // iron_mg
                $this->parseFloat($row[22])   // zinc_mg
            );
            
            if (!$stmt->execute()) {
                error_log("Error inserting food: " . $stmt->error);
            }
        }
        
        $stmt->close();
        fclose($handle);
    }
    
    /**
     * Import standards from CSV
     */
    private function importStandardsCSV($file_path) {
        $handle = fopen($file_path, 'r');
        if (!$handle) return;
        
        // Read header
        $headers = fgetcsv($handle);
        if (!$headers) return;
        
        // Prepare insert statement
        $stmt = $this->connection->prepare("
            INSERT IGNORE INTO standards (
                nutrisi, minimum, maximum, rekomendasi_harian, 
                fungsi_zat, dampak_kelebihan, dampak_kekurangan
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Error preparing statement: " . $this->connection->error);
            fclose($handle);
            return;
        }
        
        // Import each row
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 8) continue;
            
            $nutrisi = $row[1];
            $minimum = $this->parseFloat($row[2]);
            $maximum = $this->parseFloat($row[3]);
            $rekomendasi = $row[4] ?? null;
            $fungsi = $row[5] ?? null;
            $dampak_kelebihan = $row[6] ?? null;
            $dampak_kekurangan = $row[7] ?? null;
            
            $stmt->bind_param("sddssss",
                $nutrisi,
                $minimum,
                $maximum,
                $rekomendasi,
                $fungsi,
                $dampak_kelebihan,
                $dampak_kekurangan
            );
            
            if (!$stmt->execute()) {
                error_log("Error inserting standard: " . $stmt->error);
            }
        }
        
        $stmt->close();
        fclose($handle);
    }
    
    /**
     * Parse float value from CSV
     */
    private function parseFloat($value) {
        $value = trim($value);
        if ($value === '' || $value === null) {
            return null;
        }
        return is_numeric($value) ? floatval($value) : null;
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Destructor - close connection
     */
    public function __destruct() {
        $this->close();
    }
}
