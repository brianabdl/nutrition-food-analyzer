<?php
/**
 * Food Model
 * Handles all food-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class Food {
    private $db;
    private $table = 'foods';
    
    // Nutrient columns mapping
    private $nutrient_columns = [
        'Energy (kJ)' => 'energy_kj',
        'Protein (g)' => 'protein_g',
        'Fat (g)' => 'fat_g',
        'Carbohydrates (g)' => 'carbohydrates_g',
        'Dietary Fiber (g)' => 'dietary_fiber_g',
        'PUFA (g)' => 'pufa_g',
        'Cholesterol (mg)' => 'cholesterol_mg',
        'Vitamin A (mg)' => 'vitamin_a_mg',
        'Vitamin E (eq.) (mg)' => 'vitamin_e_mg',
        'Vitamin B1 (mg)' => 'vitamin_b1_mg',
        'Vitamin B2 (mg)' => 'vitamin_b2_mg',
        'Vitamin B6 (mg)' => 'vitamin_b6_mg',
        'Total Folic Acid (µg)' => 'total_folic_acid_ug',
        'Vitamin C (mg)' => 'vitamin_c_mg',
        'Sodium (mg)' => 'sodium_mg',
        'Potassium (mg)' => 'potassium_mg',
        'Calcium (mg)' => 'calcium_mg',
        'Magnesium (mg)' => 'magnesium_mg',
        'Phosphorus (mg)' => 'phosphorus_mg',
        'Iron (mg)' => 'iron_mg',
        'Zinc (mg)' => 'zinc_mg'
    ];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all foods with pagination
     */
    public function getAllFoods($limit = 10, $offset = 0, $search = '') {
        try {
            $cache_key = "foods_all_{$limit}_{$offset}_" . md5($search);
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($search)) {
                $sql .= " WHERE menu LIKE ?";
            }
            
            $sql .= " ORDER BY menu ASC LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            
            if (!empty($search)) {
                $search_param = '%' . $search . '%';
                $stmt->bind_param('sii', $search_param, $limit, $offset);
            } else {
                $stmt->bind_param('ii', $limit, $offset);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $foods = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Convert to frontend format
            $formatted = array_map([$this, 'formatFoodData'], $foods);
            
            $this->setCache($cache_key, $formatted);
            return $formatted;
            
        } catch (Exception $e) {
            error_log("Error getting foods: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of foods
     */
    public function getTotalCount($search = '') {
        try {
            $cache_key = "foods_count_" . md5($search);
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            
            if (!empty($search)) {
                $sql .= " WHERE menu LIKE ?";
                $stmt = $this->db->prepare($sql);
                $search_param = '%' . $search . '%';
                $stmt->bind_param('s', $search_param);
            } else {
                $stmt = $this->db->prepare($sql);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $row['count'];
            $stmt->close();
            
            $this->setCache($cache_key, $count);
            return $count;
            
        } catch (Exception $e) {
            error_log("Error getting count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get food by menu name
     */
    public function getFoodByName($menu) {
        try {
            $cache_key = "food_" . md5($menu);
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table} 
                WHERE LOWER(menu) = LOWER(?)
                LIMIT 1
            ");
            
            $stmt->bind_param('s', $menu);
            $stmt->execute();
            $result = $stmt->get_result();
            $food = $result->fetch_assoc();
            $stmt->close();
            
            if ($food) {
                $formatted = $this->formatFoodData($food);
                $this->setCache($cache_key, $formatted);
                return $formatted;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting food: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Search foods by name
     */
    public function searchFoods($query, $limit = 10) {
        try {
            $cache_key = "foods_search_" . md5($query) . "_{$limit}";
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE menu LIKE ?
                ORDER BY 
                    CASE 
                        WHEN LOWER(menu) = LOWER(?) THEN 1
                        WHEN LOWER(menu) LIKE LOWER(?) THEN 2
                        ELSE 3
                    END,
                    menu ASC
                LIMIT ?
            ");
            
            $query_param = '%' . $query . '%';
            $starts_param = $query . '%';
            $stmt->bind_param('sssi', $query_param, $query, $starts_param, $limit);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $foods = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            $formatted = array_map([$this, 'formatFoodData'], $foods);
            
            $this->setCache($cache_key, $formatted);
            return $formatted;
            
        } catch (Exception $e) {
            error_log("Error searching foods: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get multiple foods by names
     */
    public function getFoodsByNames($names) {
        try {
            if (empty($names)) {
                return [];
            }
            
            $placeholders = str_repeat('?,', count($names) - 1) . '?';
            
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE LOWER(menu) IN (" . $placeholders . ")
            ");
            
            $lowercaseNames = array_map('strtolower', $names);
            $types = str_repeat('s', count($lowercaseNames));
            $stmt->bind_param($types, ...$lowercaseNames);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $foods = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return array_map([$this, 'formatFoodData'], $foods);
            
        } catch (Exception $e) {
            error_log("Error getting multiple foods: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format food data from database to frontend format
     */
    private function formatFoodData($food) {
        $formatted = [
            'Menu' => $food['menu']
        ];
        
        // Map database columns to frontend format
        foreach ($this->nutrient_columns as $frontend_name => $db_column) {
            $formatted[$frontend_name] = $food[$db_column];
        }
        
        return $formatted;
    }
    
    /**
     * Get data from cache
     */
    private function getCache($key) {
        if (!CACHE_ENABLED) {
            return false;
        }
        
        $cache_file = CACHE_PATH . '/' . md5($key) . '.cache';
        
        if (file_exists($cache_file)) {
            $cache_data = unserialize(file_get_contents($cache_file));
            
            if ($cache_data['expires'] > time()) {
                return $cache_data['data'];
            }
            
            // Cache expired, delete it
            unlink($cache_file);
        }
        
        return false;
    }
    
    /**
     * Set data to cache
     */
    private function setCache($key, $data) {
        if (!CACHE_ENABLED) {
            return;
        }
        
        $cache_file = CACHE_PATH . '/' . md5($key) . '.cache';
        
        $cache_data = [
            'data' => $data,
            'expires' => time() + CACHE_DURATION
        ];
        
        file_put_contents($cache_file, serialize($cache_data));
    }
    
    /**
     * Clear all cache
     */
    public function clearCache() {
        if (!is_dir(CACHE_PATH)) {
            return;
        }
        
        $files = glob(CACHE_PATH . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
