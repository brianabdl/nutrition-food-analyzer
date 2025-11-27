<?php
/**
 * AJAX Search API Endpoint
 * Returns food search results as JSON for live search
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once MODELS_PATH . '/Food.php';

try {
    // Get search parameters
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $itemsPerPage = ITEMS_PER_PAGE;
    
    // Initialize Food model
    $foodModel = new Food();
    
    // Get foods data
    $offset = ($page - 1) * $itemsPerPage;
    $foods = $foodModel->getAllFoods($itemsPerPage, $offset, $searchTerm);
    $totalCount = $foodModel->getTotalCount($searchTerm);
    $totalPages = ceil($totalCount / $itemsPerPage);
    
    // Calculate pagination info
    $showingStart = $totalCount > 0 ? $offset + 1 : 0;
    $showingEnd = min($offset + $itemsPerPage, $totalCount);
    
    // Format response
    $response = [
        'success' => true,
        'data' => [
            'foods' => $foods,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalCount' => $totalCount,
                'itemsPerPage' => $itemsPerPage,
                'showingStart' => $showingStart,
                'showingEnd' => $showingEnd
            ],
            'searchTerm' => $searchTerm
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Search API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch food data'
    ]);
}
