<?php
/**
 * Main Index Page (PHP Version)
 * Server-side rendering with direct database queries
 */

require_once __DIR__ . '/config/config.php';
require_once MODELS_PATH . '/Food.php';

// Initialize Food model
$foodModel = new Food();

// Get pagination and search parameters
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$itemsPerPage = ITEMS_PER_PAGE;

// Get foods data from database
$offset = ($currentPage - 1) * $itemsPerPage;
$foods = $foodModel->getAllFoods($itemsPerPage, $offset, $searchTerm);
$totalCount = $foodModel->getTotalCount($searchTerm);
$totalPages = ceil($totalCount / $itemsPerPage);

// Calculate pagination info
$showingStart = $totalCount > 0 ? $offset + 1 : 0;
$showingEnd = min($offset + $itemsPerPage, $totalCount);

// Page configuration
$page_title = 'Food Nutrition Analyzer';
$header_title = 'Food Nutrition Analyzer';
$header_subtitle = 'Compare food nutrients with standard health recommendations';
$header_icon = 'apple-alt';
$show_about_link = true;

include INCLUDES_PATH . '/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Search and Filter Section -->
            <section class="search-section">
                <form method="GET" action="index.php" class="search-container">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="searchInput" 
                               placeholder="Search for foods..." 
                               class="search-input" 
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="controls">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <?php if (!empty($searchTerm)): ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                        <?php endif; ?>
                        <div class="results-count">
                            <span id="resultsCount"><?php echo $totalCount; ?></span> foods found
                        </div>
                    </div>
                </form>
            </section>

            <!-- Food Selection Toolbar -->
            <section class="comparison-toolbar" id="comparisonToolbar" style="display: none;">
                <div class="toolbar-container">
                    <div class="selection-info">
                        <i class="fas fa-check-square"></i>
                        <span id="selectedCount">0</span> foods selected
                    </div>
                    <div class="toolbar-actions">
                        <button id="compareSelected" class="btn btn-primary">
                            <i class="fas fa-balance-scale"></i>
                            Compare Selected
                        </button>
                        <button id="clearSelection" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Clear Selection
                        </button>
                    </div>
                </div>
            </section>

            <!-- Food Data Table Section -->
            <section class="table-section">
                <div class="table-info-bar">
                    <span class="table-info-text">
                        <i class="fas fa-table"></i>
                        Page <?php echo $currentPage; ?> of <?php echo max(1, $totalPages); ?> • 
                        <?php echo $itemsPerPage; ?> items per page
                    </span>
                </div>
                
                <?php if (count($foods) > 0): ?>
                <div class="table-container">
                    <table id="foodTable" class="food-table">
                        <thead>
                            <tr>
                                <th class="select-column">
                                    <input type="checkbox" id="selectAll" title="Select All">
                                </th>
                                <th>Food Name</th>
                                <th>Energy (kJ)</th>
                                <th>Protein (g)</th>
                                <th>Fat (g)</th>
                                <th>Carbohydrates (g)</th>
                                <th class="action-column">Action</th>
                            </tr>
                        </thead>
                        <tbody id="foodTableBody">
                            <?php foreach ($foods as $index => $food):?>
                            <tr data-food='<?php echo htmlspecialchars(json_encode($food), ENT_QUOTES, 'UTF-8'); ?>'>
                                <td class="select-column">
                                    <input type="checkbox" class="food-checkbox" 
                                           data-food-name="<?php echo htmlspecialchars($food['Menu']); ?>">
                                </td>
                                <td>
                                    <div class="food-name" title="<?php echo htmlspecialchars($food['Menu']); ?>">
                                        <?php echo htmlspecialchars($foodModel->formatFoodName($food['Menu'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="nutrient-value">
                                        <?php echo $food['Energy (kJ)'] !== null ? number_format($food['Energy (kJ)'], 2) : '-'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="nutrient-value">
                                        <?php echo $food['Protein (g)'] !== null ? number_format($food['Protein (g)'], 2) : '-'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="nutrient-value">
                                        <?php echo $food['Fat (g)'] !== null ? number_format($food['Fat (g)'], 2) : '-'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="nutrient-value">
                                        <?php echo $food['Carbohydrates (g)'] !== null ? number_format($food['Carbohydrates (g)'], 2) : '-'; ?>
                                    </span>
                                </td>
                                <td class="action-column">
                                    <a href="nutrition.php?food=<?php echo urlencode($food['Menu']); ?>" 
                                       class="btn btn-primary btn-analyze">
                                        <i class="fas fa-chart-bar"></i>
                                        Analyze
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <!-- No Results Message -->
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No foods found</h3>
                    <p>Try adjusting your search terms or clearing the search filter.</p>
                </div>
                <?php endif; ?>

            <!-- Pagination Section -->
            <?php if ($totalPages > 1): ?>
            <section class="pagination-section" id="paginationSection">
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span>Showing <?php echo $showingStart; ?> to <?php echo $showingEnd; ?> of <?php echo $totalCount; ?> foods</span>
                    </div>
                    <div class="pagination-controls">
                        <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                           class="btn btn-secondary pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                            Previous
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary pagination-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                            Previous
                        </button>
                        <?php endif; ?>
                        
                        <div class="page-numbers">
                            <?php
                            $maxVisiblePages = 5;
                            $startPage = max(1, $currentPage - floor($maxVisiblePages / 2));
                            $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);
                            
                            if ($endPage - $startPage < $maxVisiblePages - 1) {
                                $startPage = max(1, $endPage - $maxVisiblePages + 1);
                            }
                            
                            // First page
                            if ($startPage > 1) {
                                echo '<a href="?page=1' . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '') . '" class="page-number">1</a>';
                                if ($startPage > 2) {
                                    echo '<span class="page-ellipsis">...</span>';
                                }
                            }
                            
                            // Page numbers
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i == $currentPage) ? 'active' : '';
                                echo '<a href="?page=' . $i . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '') . '" class="page-number ' . $activeClass . '">' . $i . '</a>';
                            }
                            
                            // Last page
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<span class="page-ellipsis">...</span>';
                                }
                                echo '<a href="?page=' . $totalPages . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '') . '" class="page-number">' . $totalPages . '</a>';
                            }
                            ?>
                        </div>
                        
                        <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                           class="btn btn-secondary pagination-btn">
                            Next
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary pagination-btn" disabled>
                            Next
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Scroll to Top Button -->
            <button id="scrollToTop" class="scroll-to-top" style="display: none;">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
    </main>

    <!-- JavaScript - Simplified for client-side interactions only -->
    <script>
        const MAX_COMPARISON_ITEMS = <?php echo MAX_COMPARISON_ITEMS; ?>;
        let selectedFoods = [];
        
        // Handle food selection
        $(document).ready(function() {
            // Handle individual checkbox
            $('.food-checkbox').on('change', function() {
                updateSelection();
            });
            
            // Handle select all
            $('#selectAll').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.food-checkbox').prop('checked', isChecked);
                updateSelection();
            });
            
            // Update selection display
            function updateSelection() {
                selectedFoods = [];
                $('.food-checkbox:checked').each(function() {
                    const foodName = $(this).data('food-name');
                    if (foodName) {
                        selectedFoods.push(foodName);
                    }
                });
                
                $('#selectedCount').text(selectedFoods.length);
                
                if (selectedFoods.length > 0) {
                    $('#comparisonToolbar').show();
                } else {
                    $('#comparisonToolbar').hide();
                }
                
                // Limit selection
                if (selectedFoods.length >= MAX_COMPARISON_ITEMS) {
                    $('.food-checkbox:not(:checked)').prop('disabled', true);
                } else {
                    $('.food-checkbox').prop('disabled', false);
                }
            }
            
            // Clear selection
            $('#clearSelection').on('click', function() {
                $('.food-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateSelection();
            });
            
            // Compare selected
            $('#compareSelected').on('click', function() {
                if (selectedFoods.length < 2) {
                    alert('Please select at least 2 foods to compare');
                    return;
                }
                const foodNames = selectedFoods.map(encodeURIComponent).join(',');
                window.location.href = 'comparison.php?foods=' + foodNames;
            });
        });
    </script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
