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
        let searchTimeout = null;
        let currentPage = <?php echo $currentPage; ?>;
        
        // Handle food selection
        $(document).ready(function() {
            // Live Search - Auto-update table while typing
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = $(this).val().trim();
                
                // Debounce: wait 500ms after user stops typing
                searchTimeout = setTimeout(function() {
                    currentPage = 1; // Reset to first page on new search
                    updateFoodTable(searchValue, currentPage);
                }, 500);
            });
            
            // Prevent form submission, use AJAX instead
            $('.search-container').on('submit', function(e) {
                e.preventDefault();
                const searchValue = $('#searchInput').val().trim();
                updateFoodTable(searchValue, 1);
            });
            
            // Handle individual checkbox
            $(document).on('change', '.food-checkbox', function() {
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
            
            // Handle pagination clicks dynamically
            $(document).on('click', '.page-number:not(.active)', function(e) {
                e.preventDefault();
                const page = parseInt($(this).text()) || 1;
                const searchValue = $('#searchInput').val().trim();
                updateFoodTable(searchValue, page);
            });
            
            $(document).on('click', '.pagination-btn:not([disabled])', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (!href) return;
                
                const urlParams = new URLSearchParams(href.split('?')[1]);
                const page = parseInt(urlParams.get('page')) || 1;
                const searchValue = $('#searchInput').val().trim();
                updateFoodTable(searchValue, page);
            });
        });
        
        // AJAX function to update food table
        function updateFoodTable(searchTerm, page) {
            const $tableSection = $('.table-section');
            const $tableBody = $('#foodTableBody');
            
            // Show loading indicator
            $tableSection.css('opacity', '0.5');
            $tableBody.html('<tr><td colspan="7" style="text-align:center; padding:2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
            
            // Make AJAX request
            $.ajax({
                url: 'api/search_foods.php',
                method: 'GET',
                data: {
                    search: searchTerm,
                    page: page
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        const foods = data.foods;
                        const pagination = data.pagination;
                        
                        // Update results count
                        $('#resultsCount').text(pagination.totalCount);
                        
                        // Update table body
                        if (foods.length > 0) {
                            let tableHtml = '';
                            foods.forEach(function(food) {
                                tableHtml += `
                                    <tr data-food='${JSON.stringify(food).replace(/'/g, '&#39;')}'>
                                        <td class="select-column">
                                            <input type="checkbox" class="food-checkbox" 
                                                   data-food-name="${escapeHtml(food['Menu'])}">
                                        </td>
                                        <td>
                                            <div class="food-name" title="${escapeHtml(food['Menu'])}">
                                                ${escapeHtml(formatFoodName(food['Menu']))}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="nutrient-value">
                                                ${food['Energy (kJ)'] !== null ? parseFloat(food['Energy (kJ)']).toFixed(2) : '-'}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nutrient-value">
                                                ${food['Protein (g)'] !== null ? parseFloat(food['Protein (g)']).toFixed(2) : '-'}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nutrient-value">
                                                ${food['Fat (g)'] !== null ? parseFloat(food['Fat (g)']).toFixed(2) : '-'}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nutrient-value">
                                                ${food['Carbohydrates (g)'] !== null ? parseFloat(food['Carbohydrates (g)']).toFixed(2) : '-'}
                                            </span>
                                        </td>
                                        <td class="action-column">
                                            <a href="nutrition.php?food=${encodeURIComponent(food['Menu'])}" 
                                               class="btn btn-primary btn-analyze">
                                                <i class="fas fa-chart-bar"></i>
                                                Analyze
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });
                            $tableBody.html(tableHtml);
                            $('.no-results').remove();
                        } else {
                            // Show no results message
                            $tableBody.html('');
                            $('.table-container').after(`
                                <div class="no-results">
                                    <i class="fas fa-search"></i>
                                    <h3>No foods found</h3>
                                    <p>Try adjusting your search terms or clearing the search filter.</p>
                                </div>
                            `);
                        }
                        
                        // Update table info bar
                        $('.table-info-text').html(`
                            <i class="fas fa-table"></i>
                            Page ${pagination.currentPage} of ${Math.max(1, pagination.totalPages)} • 
                            ${pagination.itemsPerPage} items per page
                        `);
                        
                        // Update pagination
                        updatePagination(pagination, searchTerm);
                        
                        // Update current page
                        currentPage = pagination.currentPage;
                        
                        // Update URL without reloading
                        const newUrl = searchTerm 
                            ? `?search=${encodeURIComponent(searchTerm)}&page=${page}`
                            : (page > 1 ? `?page=${page}` : window.location.pathname);
                        window.history.replaceState({}, '', newUrl);
                        
                    } else {
                        showError('Failed to load food data');
                    }
                    
                    // Hide loading indicator
                    $tableSection.css('opacity', '1');
                },
                error: function() {
                    showError('Connection error. Please try again.');
                    $tableSection.css('opacity', '1');
                }
            });
        }
        
        // Update pagination controls
        function updatePagination(pagination, searchTerm) {
            const { currentPage, totalPages, showingStart, showingEnd, totalCount } = pagination;
            
            if (totalPages <= 1) {
                $('.pagination-section').hide();
                return;
            }
            
            $('.pagination-section').show();
            
            // Update pagination info
            $('.pagination-info span').text(`Showing ${showingStart} to ${showingEnd} of ${totalCount} foods`);
            
            // Generate pagination controls
            let paginationHtml = '';
            
            // Previous button
            if (currentPage > 1) {
                paginationHtml += `
                    <a href="?page=${currentPage - 1}${searchTerm ? '&search=' + encodeURIComponent(searchTerm) : ''}" 
                       class="btn btn-secondary pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </a>
                `;
            } else {
                paginationHtml += `
                    <button class="btn btn-secondary pagination-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                `;
            }
            
            // Page numbers
            paginationHtml += '<div class="page-numbers">';
            
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // First page
            if (startPage > 1) {
                paginationHtml += `<a href="?page=1${searchTerm ? '&search=' + encodeURIComponent(searchTerm) : ''}" class="page-number">1</a>`;
                if (startPage > 2) {
                    paginationHtml += '<span class="page-ellipsis">...</span>';
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = (i === currentPage) ? 'active' : '';
                paginationHtml += `<a href="?page=${i}${searchTerm ? '&search=' + encodeURIComponent(searchTerm) : ''}" class="page-number ${activeClass}">${i}</a>`;
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHtml += '<span class="page-ellipsis">...</span>';
                }
                paginationHtml += `<a href="?page=${totalPages}${searchTerm ? '&search=' + encodeURIComponent(searchTerm) : ''}" class="page-number">${totalPages}</a>`;
            }
            
            paginationHtml += '</div>';
            
            // Next button
            if (currentPage < totalPages) {
                paginationHtml += `
                    <a href="?page=${currentPage + 1}${searchTerm ? '&search=' + encodeURIComponent(searchTerm) : ''}" 
                       class="btn btn-secondary pagination-btn">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </a>
                `;
            } else {
                paginationHtml += `
                    <button class="btn btn-secondary pagination-btn" disabled>
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;
            }
            
            $('.pagination-controls').html(paginationHtml);
        }
        
        // Helper: Format food name (simplified version for client-side)
        function formatFoodName(name) {
            if (!name) return 'Unknown Food';
            
            // Basic formatting: capitalize first letter of each word
            return name
                .toLowerCase()
                .split(/[\s,]+/)
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }
        
        // Helper: Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
        
        // Helper: Show error message
        function showError(message) {
            $('#foodTableBody').html(`
                <tr>
                    <td colspan="7" style="text-align:center; padding:2rem; color:#ef4444;">
                        <i class="fas fa-exclamation-triangle"></i> ${message}
                    </td>
                </tr>
            `);
        }
    </script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
