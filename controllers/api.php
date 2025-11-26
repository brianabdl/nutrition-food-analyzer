<?php
/**
 * API Controller
 * Handles AJAX requests and returns JSON responses
 */

require_once __DIR__ . '/../config/config.php';
require_once MODELS_PATH . '/Food.php';
require_once MODELS_PATH . '/Standard.php';

class ApiController {
    private $foodModel;
    private $standardModel;
    
    public function __construct() {
        $this->foodModel = new Food();
        $this->standardModel = new Standard();
    }
    
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_foods':
                $this->getFoods();
                break;
                
            case 'search_foods':
                $this->searchFoods();
                break;
                
            case 'get_food':
                $this->getFood();
                break;
                
            case 'get_multiple_foods':
                $this->getMultipleFoods();
                break;
                
            case 'get_standards':
                $this->getStandards();
                break;
                
            case 'get_nutrition_analysis':
                $this->getNutritionAnalysis();
                break;
                
            case 'get_comparison':
                $this->getComparison();
                break;
                
            default:
                $this->sendError('Invalid action', 400);
                break;
        }
    }
    
    /**
     * Get paginated list of foods
     */
    private function getFoods() {
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $search = $_GET['search'] ?? '';
        
        $offset = ($page - 1) * $limit;
        
        $foods = $this->foodModel->getAllFoods($limit, $offset, $search);
        $total = $this->foodModel->getTotalCount($search);
        $totalPages = ceil($total / $limit);
        
        $this->sendSuccess([
            'foods' => $foods,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ]);
    }
    
    /**
     * Search foods
     */
    private function searchFoods() {
        $query = $_GET['q'] ?? '';
        $limit = intval($_GET['limit'] ?? 10);
        
        if (empty($query)) {
            $this->sendError('Search query is required', 400);
            return;
        }
        
        $foods = $this->foodModel->searchFoods($query, $limit);
        
        $this->sendSuccess([
            'foods' => $foods,
            'count' => count($foods)
        ]);
    }
    
    /**
     * Get single food by name
     */
    private function getFood() {
        $name = $_GET['name'] ?? '';
        
        if (empty($name)) {
            $this->sendError('Food name is required', 400);
            return;
        }
        
        $food = $this->foodModel->getFoodByName($name);
        
        if (!$food) {
            $this->sendError('Food not found', 404);
            return;
        }
        
        $this->sendSuccess(['food' => $food]);
    }
    
    /**
     * Get multiple foods by names
     */
    private function getMultipleFoods() {
        $names = $_GET['names'] ?? '';
        
        if (empty($names)) {
            $this->sendError('Food names are required', 400);
            return;
        }
        
        $namesArray = explode(',', $names);
        $namesArray = array_map('trim', $namesArray);
        
        $foods = $this->foodModel->getFoodsByNames($namesArray);
        
        $this->sendSuccess([
            'foods' => $foods,
            'count' => count($foods)
        ]);
    }
    
    /**
     * Get all nutrition standards
     */
    private function getStandards() {
        $standards = $this->standardModel->getAllStandards();
        
        $this->sendSuccess([
            'standards' => $standards,
            'count' => count($standards)
        ]);
    }
    
    /**
     * Get nutrition analysis for a food
     */
    private function getNutritionAnalysis() {
        $name = $_GET['name'] ?? '';
        
        if (empty($name)) {
            $this->sendError('Food name is required', 400);
            return;
        }
        
        $food = $this->foodModel->getFoodByName($name);
        
        if (!$food) {
            $this->sendError('Food not found', 404);
            return;
        }
        
        $standards = $this->standardModel->getAllStandards();
        
        // Generate comparison data
        $comparisons = [];
        
        foreach ($standards as $standard) {
            $nutrient = $standard['Nutrisi'];
            $foodValue = $food[$nutrient] ?? null;
            
            $comparison = [
                'nutrient' => $nutrient,
                'food_value' => $foodValue,
                'standard' => $standard,
                'status' => 'no-standard',
                'status_text' => 'No Standard',
                'in_range' => false
            ];
            
            if ($standard['Minimum'] !== null || $standard['Maximum'] !== null) {
                $min = $standard['Minimum'];
                $max = $standard['Maximum'];
                $value = floatval($foodValue ?? 0);
                
                if ($min !== null && $max !== null) {
                    if ($value >= $min && $value <= $max) {
                        $comparison['status'] = 'normal';
                        $comparison['status_text'] = 'Normal Range';
                        $comparison['in_range'] = true;
                    } elseif ($value > $max) {
                        $comparison['status'] = 'excess';
                        $comparison['status_text'] = 'Excess';
                    } else {
                        $comparison['status'] = 'deficiency';
                        $comparison['status_text'] = 'Deficient';
                    }
                } elseif ($min !== null) {
                    if ($value >= $min) {
                        $comparison['status'] = 'normal';
                        $comparison['status_text'] = 'Normal Range';
                        $comparison['in_range'] = true;
                    } else {
                        $comparison['status'] = 'deficiency';
                        $comparison['status_text'] = 'Deficient';
                    }
                } elseif ($max !== null) {
                    if ($value <= $max) {
                        $comparison['status'] = 'normal';
                        $comparison['status_text'] = 'Normal Range';
                        $comparison['in_range'] = true;
                    } else {
                        $comparison['status'] = 'excess';
                        $comparison['status_text'] = 'Excess';
                    }
                }
            }
            
            $comparisons[] = $comparison;
        }
        
        // Calculate statistics
        $totalNutrients = count($comparisons);
        $safeNutrients = count(array_filter($comparisons, function($c) {
            return $c['in_range'];
        }));
        $safetyPercentage = $totalNutrients > 0 
            ? round(($safeNutrients / $totalNutrients) * 100) 
            : 0;
        
        $this->sendSuccess([
            'food' => $food,
            'comparisons' => $comparisons,
            'statistics' => [
                'total_nutrients' => $totalNutrients,
                'safe_nutrients' => $safeNutrients,
                'safety_percentage' => $safetyPercentage
            ]
        ]);
    }
    
    /**
     * Get comparison data for multiple foods
     */
    private function getComparison() {
        $names = $_GET['names'] ?? '';
        
        if (empty($names)) {
            $this->sendError('Food names are required', 400);
            return;
        }
        
        $namesArray = explode(',', $names);
        $namesArray = array_map('trim', $namesArray);
        
        if (count($namesArray) < 2) {
            $this->sendError('At least 2 foods are required for comparison', 400);
            return;
        }
        
        if (count($namesArray) > MAX_COMPARISON_ITEMS) {
            $this->sendError('Maximum ' . MAX_COMPARISON_ITEMS . ' foods can be compared', 400);
            return;
        }
        
        $foods = $this->foodModel->getFoodsByNames($namesArray);
        $standards = $this->standardModel->getAllStandards();
        
        if (count($foods) < 2) {
            $this->sendError('Could not find enough foods for comparison', 404);
            return;
        }
        
        $this->sendSuccess([
            'foods' => $foods,
            'standards' => $standards,
            'count' => count($foods)
        ]);
    }
    
    /**
     * Send success response
     */
    private function sendSuccess($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send error response
     */
    private function sendError($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Handle request if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === 'api.php') {
    $controller = new ApiController();
    $controller->handleRequest();
}
