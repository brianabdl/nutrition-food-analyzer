<?php
/**
 * Standard Nutrition Model
 * Handles nutrition standards database operations
 */

require_once __DIR__ . '/../config/database.php';

class Standard {
    private $db;
    private $table = 'standards';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all nutrition standards
     */
    public function getAllStandards() {
        try {
            $cache_key = "standards_all";
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $result = $this->db->query("SELECT * FROM {$this->table} ORDER BY nutrisi");
            $standards = $result->fetch_all(MYSQLI_ASSOC);
            
            $formatted = array_map([$this, 'formatStandardData'], $standards);
            
            $this->setCache($cache_key, $formatted);
            return $formatted;
            
        } catch (Exception $e) {
            error_log("Error getting standards: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get standard by nutrient name
     */
    public function getStandardByNutrient($nutrient) {
        try {
            $cache_key = "standard_" . md5($nutrient);
            $cached = $this->getCache($cache_key);
            if ($cached !== false) {
                return $cached;
            }
            
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE nutrisi = ?
                LIMIT 1
            ");
            
            $stmt->bind_param('s', $nutrient);
            $stmt->execute();
            $result = $stmt->get_result();
            $standard = $result->fetch_assoc();
            $stmt->close();
            
            if ($standard) {
                $formatted = $this->formatStandardData($standard);
                $this->setCache($cache_key, $formatted);
                return $formatted;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting standard: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Format standard data from database to frontend format
     */
    private function formatStandardData($standard) {
        return [
            'Nutrisi' => $standard['nutrisi'],
            'Minimum' => $standard['minimum'],
            'Maximum' => $standard['maximum'],
            'Rekomendasi Harian Anak (1-5 tahun)' => $standard['rekomendasi_harian'],
            'Fungsi Zat' => $standard['fungsi_zat'],
            'Dampak Kelebihan' => $standard['dampak_kelebihan'],
            'Dampak Kekurangan' => $standard['dampak_kekurangan']
        ];
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
}
