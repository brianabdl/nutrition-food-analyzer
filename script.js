/**
 * Food Nutrition Comparison Application
 * Main JavaScript functionality for loading, displaying, and comparing nutrition data
 */

// Global variables to store loaded data
let foodsData = [];
let standardsData = [];
let filteredFoodsData = [];
let currentChart = null;
let multiChart = null;
let selectedFoods = [];
let maxSelections = 5; // Limit comparison to 5 foods for better visualization

// Pagination variables
let currentPage = 1;
let itemsPerPage = 10;
let totalPages = 1;

// Configuration object for nutrient mapping
const NUTRIENT_MAPPING = {
    'Energy (kJ)': 'Energy (kJ)',
    'Protein (g)': 'Protein (g)',
    'Fat (g)': 'Fat (g)',
    'Carbohydrates (g)': 'Carbohydrates (g)',
    'Dietary Fiber (g)': 'Dietary Fiber (g)',
    'PUFA (g)': 'PUFA (g)',
    'Cholesterol (mg)': 'Cholesterol (mg)',
    'Vitamin A (mg)': 'Vitamin A (mg)',
    'Vitamin E (eq.) (mg)': 'Vitamin E (eq.) (mg)',
    'Vitamin B1 (mg)': 'Vitamin B1 (mg)',
    'Vitamin B2 (mg)': 'Vitamin B2 (mg)',
    'Vitamin B6 (mg)': 'Vitamin B6 (mg)',
    'Total Folic Acid (µg)': 'Total Folic Acid (µg)',
    'Vitamin C (mg)': 'Vitamin C (mg)',
    'Sodium (mg)': 'Sodium (mg)',
    'Potassium (mg)': 'Potassium (mg)',
    'Calcium (mg)': 'Calcium (mg)',
    'Magnesium (mg)': 'Magnesium (mg)',
    'Phosphorus (mg)': 'Phosphorus (mg)',
    'Iron (mg)': 'Iron (mg)',
    'Zinc (mg)': 'Zinc (mg)'
};

// Application initialization
$(document).ready(function() {
    console.log('Initializing Food Nutrition Comparison App...');
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    setupEventListeners();
    loadData();
}

/**
 * Set up all event listeners
 */
function setupEventListeners() {
    // Search functionality
    $('#searchInput').on('input', handleSearch);
    $('#clearSearch').on('click', clearSearch);
    
    // Modal functionality
    $('#closeModal').on('click', closeModal);
    $('#closeMultiModal').on('click', closeMultiModal);
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            if (e.target.id === 'multiComparisonModal') {
                closeMultiModal();
            } else {
                closeModal();
            }
        }
    });
    
    // Multiple selection functionality
    $('#selectAll').on('change', handleSelectAll);
    $('#compareSelected').on('click', navigateToComparison);
    $('#clearSelection').on('click', clearAllSelections);
    $('#chartNutrientSelect').on('change', updateMultiChart);
    
    // Scroll to top functionality
    $('#scrollToTop').on('click', scrollToTableTop);
    
    // Show/hide scroll to top button based on table scroll position
    $('.table-container').on('scroll', handleTableScroll);
    
    // Pagination functionality
    $('#prevPage').on('click', goToPreviousPage);
    $('#nextPage').on('click', goToNextPage);
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Table sorting
    $('.sortable').on('click', handleSort);
    
    console.log('Event listeners set up successfully');
}

/**
 * Load data from PHP API
 */
async function loadData() {
    showLoading(true);
    
    try {
        console.log('Loading data from API...');
        
        // Load foods and standards from API
        const response = await fetch(`${API_URL}?action=get_foods&page=${currentPage}&limit=${API_CONFIG.itemsPerPage}`);
        
        if (!response.ok) {
            throw new Error('Failed to load data from API');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'API returned error');
        }
        
        foodsData = result.data.foods;
        filteredFoodsData = [...foodsData];
        
        // Update pagination info
        totalPages = result.data.pagination.total_pages;
        currentPage = result.data.pagination.current_page;
        
        console.log(`Loaded ${foodsData.length} foods from API`);
        
        // Render the table
        renderFoodTableFromAPI(result.data);
        
    } catch (error) {
        console.error('Error loading data:', error);
        showError('Failed to load nutrition data. Please check your connection.');
    } finally {
        showLoading(false);
    }
}

/**
 * Parse CSV text into array of objects
 * @param {string} csvText - Raw CSV text
 * @returns {Array} Array of objects representing CSV rows
 */
function parseCSV(csvText) {
    const lines = csvText.trim().split('\n');
    if (lines.length < 2) return [];
    
    const headers = lines[0].split(',').map(header => header.replace(/"/g, '').trim());
    const data = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = parseCSVLine(lines[i]);
        if (values.length === headers.length) {
            const row = {};
            headers.forEach((header, index) => {
                let value = values[index].replace(/"/g, '').trim();
                // Convert numeric strings to numbers
                if (!isNaN(value) && value !== '') {
                    value = parseFloat(value);
                }
                row[header] = value;
            });
            data.push(row);
        }
    }
    
    return data;
}

/**
 * Parse a single CSV line, handling commas within quotes
 * @param {string} line - CSV line to parse
 * @returns {Array} Array of values
 */
function parseCSVLine(line) {
    const values = [];
    let current = '';
    let inQuotes = false;
    
    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        
        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            values.push(current);
            current = '';
        } else {
            current += char;
        }
    }
    
    values.push(current);
    return values;
}

/**
 * Render food table from API response
 */
function renderFoodTableFromAPI(apiData) {
    const tbody = $('#foodTableBody');
    tbody.empty();
    
    const foods = apiData.foods;
    const pagination = apiData.pagination;
    
    if (foods.length === 0) {
        $('#noResults').show();
        $('.table-section').hide();
        $('#paginationSection').hide();
        $('#resultsCount').text(0);
        return;
    }
    
    $('#noResults').hide();
    $('.table-section').show();
    $('#paginationSection').show();
    
    foods.forEach((food, index) => {
        const actualIndex = index; // Index in current page
        const formattedFoodName = formatFoodName(food.Menu);
        const isSelected = selectedFoods.some(sf => sf.foodId === food.Menu);
        
        const row = $(`
            <tr data-index="${actualIndex}" class="${isSelected ? 'selected-row' : ''}">
                <td class="select-column">
                    <input type="checkbox" class="food-checkbox" data-food-index="${actualIndex}" ${isSelected ? 'checked' : ''}>
                </td>
                <td>
                    <div class="food-name" title="${escapeHtml(food.Menu || 'Unknown Food')}">${escapeHtml(formattedFoodName)}</div>
                </td>
                <td>
                    <span class="nutrient-value">${formatNutrientValue(food['Energy (kJ)'])}</span>
                </td>
                <td>
                    <span class="nutrient-value">${formatNutrientValue(food['Protein (g)'])}</span>
                </td>
                <td>
                    <span class="nutrient-value">${formatNutrientValue(food['Fat (g)'])}</span>
                </td>
                <td>
                    <span class="nutrient-value">${formatNutrientValue(food['Carbohydrates (g)'])}</span>
                </td>
                <td class="action-column">
                    <button class="btn btn-primary btn-analyze" data-food-name="${escapeHtml(food.Menu)}">
                        <i class="fas fa-chart-bar"></i>
                        Analyze
                    </button>
                </td>
            </tr>
        `);
        
        tbody.append(row);
    });
    
    // Attach click handlers for analyze buttons - navigate to nutrition page
    $('.btn-analyze').on('click', function () {
        const foodName = $(this).data('food-name');
        window.location.href = `nutrition.php?food=${encodeURIComponent(foodName)}`;
    });
    
    // Setup scroll indicator for table
    setupScrollIndicator();
    
    // Update pagination
    totalPages = pagination.total_pages;
    currentPage = pagination.current_page;
    updatePaginationControls();
    updatePaginationInfo(pagination);
    
    // Update results count
    $('#resultsCount').text(pagination.total_items);
}

/**
 * Update pagination info from API response
 */
function updatePaginationInfo(pagination) {
    const startIndex = (pagination.current_page - 1) * pagination.items_per_page + 1;
    const endIndex = Math.min(startIndex + pagination.items_per_page - 1, pagination.total_items);
    
    $('#showingStart').text(startIndex);
    $('#showingEnd').text(endIndex);
    $('#totalItems').text(pagination.total_items);
    
    // Update table info bar
    $('#currentPageDisplay').text(pagination.current_page);
    $('#totalPagesDisplay').text(pagination.total_pages);
    $('#itemsPerPageDisplay').text(pagination.items_per_page);
}

/**
 * Setup scroll indicator for the table container
 */
function setupScrollIndicator() {
    const tableContainer = $('.table-container');
    
    tableContainer.on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        
        if (scrollTop > 0) {
            $(this).addClass('scrolled');
        } else {
            $(this).removeClass('scrolled');
        }
    });
}

/**
 * Handle table scroll events for scroll-to-top button visibility
 */
function handleTableScroll() {
    const scrollTop = $(this).scrollTop();
    const scrollToTopBtn = $('#scrollToTop');
    
    if (scrollTop > 200) { // Show button after scrolling 200px
        scrollToTopBtn.fadeIn(300);
    } else {
        scrollToTopBtn.fadeOut(300);
    }
}

/**
 * Scroll to top of the table smoothly
 */
function scrollToTableTop() {
    $('.table-container').animate({
        scrollTop: 0
    }, 500, 'swing');
}

/**
 * Update pagination controls
 */
function updatePaginationControls() {
    // Update previous/next buttons
    $('#prevPage').prop('disabled', currentPage <= 1);
    $('#nextPage').prop('disabled', currentPage >= totalPages);
    
    // Generate page numbers
    const pageNumbers = $('#pageNumbers');
    pageNumbers.empty();
    
    if (totalPages <= 1) {
        return;
    }
    
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    // Adjust start page if we're near the end
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // Add first page and ellipsis if needed
    if (startPage > 1) {
        pageNumbers.append(`<span class="page-number" data-page="1">1</span>`);
        if (startPage > 2) {
            pageNumbers.append(`<span class="page-ellipsis">...</span>`);
        }
    }
    
    // Add page numbers
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage ? 'active' : '';
        pageNumbers.append(`<span class="page-number ${isActive}" data-page="${i}">${i}</span>`);
    }
    
    // Add last page and ellipsis if needed
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            pageNumbers.append(`<span class="page-ellipsis">...</span>`);
        }
        pageNumbers.append(`<span class="page-number" data-page="${totalPages}">${totalPages}</span>`);
    }
    
    // Attach click handlers to page numbers
    $('.page-number').on('click', function() {
        const page = parseInt($(this).data('page'));
        goToPage(page);
    });
}

/**
 * Update pagination information
 */
function updatePaginationInfo(startIndex, endIndex) {
    $('#showingStart').text(startIndex + 1);
    $('#showingEnd').text(endIndex);
    $('#totalItems').text(filteredFoodsData.length);
    
    // Update table info bar
    $('#currentPageDisplay').text(currentPage);
    $('#totalPagesDisplay').text(totalPages);
    $('#itemsPerPageDisplay').text(itemsPerPage);
}

/**
 * Go to specific page
 */
function goToPage(page) {
    if (page >= 1 && page <= totalPages && page !== currentPage) {
        currentPage = page;
        const searchTerm = $('#searchInput').val().trim();
        loadDataWithSearch(searchTerm);
        
        // Scroll to top of table when changing pages
        $('.table-container').scrollTop(0);
    }
}

/**
 * Go to previous page
 */
function goToPreviousPage() {
    if (currentPage > 1) {
        goToPage(currentPage - 1);
    }
}

/**
 * Go to next page
 */
function goToNextPage() {
    if (currentPage < totalPages) {
        goToPage(currentPage + 1);
    }
}

/**
 * Format nutrient values for display
 * @param {number|string} value - Nutrient value
 * @returns {string} Formatted value
 */
function formatNutrientValue(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    
    if (typeof value === 'number') {
        return value.toFixed(2);
    }
    
    return value.toString();
}

/**
 * Escape HTML characters to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format food names to proper case with better readability (keeping original Indonesian names)
 * @param {string} foodName - Raw food name from CSV
 * @returns {string} Properly formatted food name
 */
function formatFoodName(foodName) {
    if (!foodName || typeof foodName !== 'string') {
        return 'Unknown Food';
    }
    
    // Clean the food name while preserving original Indonesian terms
    let formatted = foodName.trim();
    
    // Handle specific patterns and clean formatting
    formatted = formatted
        // Clean up extra spaces and special characters
        .replace(/\s+/g, ' ')
        .replace(/[\/\\]+/g, ' / ')
        .replace(/\s*\+\s*/g, ' + ')
        .replace(/\s*\-\s*/g, ' - ')
        .replace(/\s*\(\s*/g, ' (')
        .replace(/\s*\)\s*/g, ') ')
        
        // Handle parentheses content
        .replace(/\(\s*>\s*(\d+)\s*(.*?)\s*\)/gi, '(>$1 $2)')
        
        // Fix common abbreviations and spacing issues
        .replace(/\bpt(h?)\b/gi, 'putih')
        .replace(/\bcampur\b/gi, 'campur')
        
        // Clean up slashes and special characters
        .replace(/\s*\/\s*/g, ' / ')
        .replace(/\s*\\\s*/g, ' / ');
    
    // Capitalize each word properly while preserving Indonesian terms
    formatted = formatted.replace(/\b\w+/g, function(word) {
        // Handle special cases for acronyms
        const acronyms = ['dna', 'rna', 'ph', 'mg', 'kg', 'ml', 'dl', 'cl'];
        
        if (acronyms.includes(word.toLowerCase())) {
            return word.toUpperCase();
        }
        
        // Capitalize first letter, keep rest as is for Indonesian words
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    });
    
    // Ensure first word is always capitalized
    formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);
    
    // Clean up final formatting
    formatted = formatted
        .replace(/\s+/g, ' ')
        .replace(/\s+([\/\-\+\(\)])/g, ' $1')
        .replace(/([\/\-\+\(\)])\s+/g, '$1 ')
        .trim();
    
    return formatted;
}

/**
 * Handle search input
 */
function handleSearch() {
    const searchTerm = $('#searchInput').val().trim();
    
    // Show/hide clear button based on search input
    if (searchTerm === '') {
        $('#clearSearch').hide();
    } else {
        $('#clearSearch').show();
    }
    
    // Reset to first page when searching
    currentPage = 1;
    
    // Load data from API with search
    loadDataWithSearch(searchTerm);
}

/**
 * Load data with search from API
 */
async function loadDataWithSearch(search = '') {
    showLoading(true);
    
    try {
        const url = `${API_URL}?action=get_foods&page=${currentPage}&limit=${API_CONFIG.itemsPerPage}&search=${encodeURIComponent(search)}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Failed to search foods');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'API returned error');
        }
        
        foodsData = result.data.foods;
        filteredFoodsData = [...foodsData];
        
        // Update pagination info
        totalPages = result.data.pagination.total_pages;
        currentPage = result.data.pagination.current_page;
        
        // Render the table
        renderFoodTableFromAPI(result.data);
        
    } catch (error) {
        console.error('Error searching foods:', error);
        showError('Failed to search foods. Please try again.');
    } finally {
        showLoading(false);
    }
}

/**
 * Clear search input and reset table
 */
function clearSearch() {
    $('#searchInput').val('');
    $('#clearSearch').hide();
    currentPage = 1;
    loadDataWithSearch('');
}

/**
 * Update results count display
 */
function updateResultsCount() {
    // Results count is now updated by API response
    // This function is kept for compatibility
}

/**
 * Handle table sorting
 * @param {Event} e - Click event
 */
function handleSort(e) {
    const $th = $(e.currentTarget);
    const column = $th.data('column');
    const currentSort = $th.hasClass('sort-asc') ? 'asc' : 
                       $th.hasClass('sort-desc') ? 'desc' : 'none';
    
    // Remove sort classes from all headers
    $('.sortable').removeClass('sort-asc sort-desc');
    
    // Determine new sort direction
    let newSort = 'asc';
    if (currentSort === 'asc') {
        newSort = 'desc';
    }
    
    // Add sort class to current header
    $th.addClass(`sort-${newSort}`);
    
    // Sort the data
    filteredFoodsData.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        // Handle missing or null values
        if (aVal === null || aVal === undefined || aVal === '') aVal = '';
        if (bVal === null || bVal === undefined || bVal === '') bVal = '';
        
        // Special handling for Menu column (food names) - sort alphabetically
        if (column === 'Menu') {
            // Use formatted food names for consistent sorting
            aVal = formatFoodName(aVal).toLowerCase();
            bVal = formatFoodName(bVal).toLowerCase();
            
            if (newSort === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        } else {
            // For numeric columns, convert to numbers
            if (aVal === '') aVal = -Infinity;
            if (bVal === '') bVal = -Infinity;
            
            if (typeof aVal === 'string' && !isNaN(aVal)) aVal = parseFloat(aVal);
            if (typeof bVal === 'string' && !isNaN(bVal)) bVal = parseFloat(bVal);
            
            if (newSort === 'asc') {
                return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
            } else {
                return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
            }
        }
    });
    
    renderFoodTable();
}

/**
 * Show nutrition analysis modal for selected food
 * @param {Object} food - Food object with nutrition data
 */
function showNutritionModal(food) {
    console.log('Showing nutrition modal for:', food.Menu);
    
    // Set modal title with formatted name
    const formattedName = formatFoodName(food.Menu);
    $('#modalFoodName').text(formattedName);
    
    // Generate comparison data
    const comparisonData = generateComparisonData(food);
    
    // Update summary cards
    updateSummaryCards(comparisonData);
    
    // Render comparison table
    renderComparisonTable(comparisonData);
    
    // Render nutrient information cards
    renderNutrientInfoCards(comparisonData);
    
    // Create nutrition chart
    createNutritionChart(comparisonData);
    
    // Show modal
    $('#nutritionModal').fadeIn(300);
    $('body').addClass('modal-open');
}

/**
 * Generate comparison data between food and standards
 * @param {Object} food - Food nutrition data
 * @returns {Array} Array of comparison objects
 */
function generateComparisonData(food) {
    const comparisons = [];
    
    Object.keys(NUTRIENT_MAPPING).forEach(nutrient => {
        const foodValue = food[nutrient];
        const standard = standardsData.find(s => s.Nutrisi === nutrient);
        
        const comparison = {
            nutrient: nutrient,
            foodValue: foodValue,
            standard: standard,
            status: 'no-standard',
            statusText: 'No Standard',
            inRange: false
        };
        
        if (standard && (standard.Minimum || standard.Maximum)) {
            const min = parseFloat(standard.Minimum) || null;
            const max = parseFloat(standard.Maximum) || null;
            const value = parseFloat(foodValue) || 0;
            
            if (min !== null && max !== null) {
                if (value >= min && value <= max) {
                    comparison.status = 'normal';
                    comparison.statusText = 'Normal Range';
                    comparison.inRange = true;
                } else if (value > max) {
                    comparison.status = 'excess';
                    comparison.statusText = 'Excess';
                } else if (value < min) {
                    comparison.status = 'deficiency';
                    comparison.statusText = 'Deficient';
                }
            } else if (min !== null) {
                if (value >= min) {
                    comparison.status = 'normal';
                    comparison.statusText = 'Normal Range';
                    comparison.inRange = true;
                } else {
                    comparison.status = 'deficiency';
                    comparison.statusText = 'Deficient';
                }
            } else if (max !== null) {
                if (value <= max) {
                    comparison.status = 'normal';
                    comparison.statusText = 'Normal Range';
                    comparison.inRange = true;
                } else {
                    comparison.status = 'excess';
                    comparison.statusText = 'Excess';
                }
            }
        }
        
        comparisons.push(comparison);
    });
    
    return comparisons;
}

/**
 * Update summary cards with comparison statistics
 * @param {Array} comparisons - Array of comparison objects
 */
function updateSummaryCards(comparisons) {
    const totalNutrients = comparisons.length;
    const safeNutrients = comparisons.filter(c => c.inRange).length;
    const safetyPercentage = totalNutrients > 0 ? Math.round((safeNutrients / totalNutrients) * 100) : 0;
    
    $('#totalNutrients').text(totalNutrients);
    $('#safeNutrients').text(safeNutrients);
    $('#safetyPercentage').text(`${safetyPercentage}%`);
}

/**
 * Render the detailed comparison table
 * @param {Array} comparisons - Array of comparison objects
 */
function renderComparisonTable(comparisons) {
    const tbody = $('#comparisonTableBody');
    tbody.empty();
    
    comparisons.forEach(comparison => {
        const standard = comparison.standard;
        let rangeText = 'No standard available';
        
        if (standard) {
            const min = standard.Minimum;
            const max = standard.Maximum;
            const recommendation = standard['Rekomendasi Harian Anak (1-5 tahun)'];
            
            if (min && max) {
                rangeText = `${min} - ${max}`;
            } else if (min) {
                rangeText = `≥ ${min}`;
            } else if (max) {
                rangeText = `≤ ${max}`;
            } else if (recommendation) {
                rangeText = recommendation;
            }
        }
        
        const row = $(`
            <tr class="comparison-row" data-nutrient="${comparison.nutrient}">
                <td>
                    <strong>${comparison.nutrient}</strong>
                </td>
                <td>
                    <span class="nutrient-value">${formatNutrientValue(comparison.foodValue)}</span>
                </td>
                <td>${rangeText}</td>
                <td>
                    <span class="status-badge status-${comparison.status}">
                        <i class="fas fa-${getStatusIcon(comparison.status)}"></i>
                        ${comparison.statusText}
                    </span>
                </td>
                <td>
                    ${standard ? `<button class="info-btn" data-nutrient="${comparison.nutrient}">
                        <i class="fas fa-info"></i>
                    </button>` : '-'}
                </td>
            </tr>
        `);
        
        tbody.append(row);
    });
    
    // Attach click handlers for info buttons
    $('.info-btn').on('click', function(e) {
        e.stopPropagation();
        const nutrient = $(this).data('nutrient');
        showNutrientTooltip(e, nutrient);
    });
}

/**
 * Get icon class for nutrient status
 * @param {string} status - Nutrient status
 * @returns {string} Font Awesome icon class
 */
function getStatusIcon(status) {
    switch (status) {
        case 'normal': return 'check-circle';
        case 'excess': return 'exclamation-triangle';
        case 'deficiency': return 'arrow-down';
        default: return 'question-circle';
    }
}

/**
 * Show tooltip with nutrient information
 * @param {Event} event - Click event
 * @param {string} nutrient - Nutrient name
 */
function showNutrientTooltip(event, nutrient) {
    const standard = standardsData.find(s => s.Nutrisi === nutrient);
    if (!standard) return;
    
    const tooltip = $('#tooltip');
    let content = `<strong>${nutrient}</strong><br>`;
    
    if (standard['Fungsi Zat']) {
        content += `<strong>Function:</strong> ${standard['Fungsi Zat']}<br>`;
    }
    if (standard['Dampak Kelebihan']) {
        content += `<strong>Excess Effects:</strong> ${standard['Dampak Kelebihan']}<br>`;
    }
    if (standard['Dampak Kekurangan']) {
        content += `<strong>Deficiency Effects:</strong> ${standard['Dampak Kekurangan']}`;
    }
    
    tooltip.html(content);
    tooltip.css({
        top: event.pageY + 10,
        left: event.pageX + 10
    }).addClass('show');
    
    // Hide tooltip after 5 seconds or on next click
    setTimeout(() => tooltip.removeClass('show'), 5000);
    $(document).one('click', () => tooltip.removeClass('show'));
}

/**
 * Render nutrient information cards
 * @param {Array} comparisons - Array of comparison objects
 */
function renderNutrientInfoCards(comparisons) {
    const container = $('#nutrientInfoSection');
    container.empty();
    
    // Filter comparisons that have detailed standard information
    const detailedComparisons = comparisons.filter(c => 
        c.standard && (c.standard['Fungsi Zat'] || c.standard['Dampak Kelebihan'] || c.standard['Dampak Kekurangan'])
    );
    
    detailedComparisons.slice(0, 6).forEach(comparison => {
        const standard = comparison.standard;
        const card = $(`
            <div class="nutrient-info-card">
                <h4>
                    <i class="fas fa-${getNutrientIcon(comparison.nutrient)}"></i>
                    ${comparison.nutrient}
                </h4>
                
                ${standard['Fungsi Zat'] ? `
                <div class="info-section">
                    <div class="info-label">Function</div>
                    <div class="info-text">${standard['Fungsi Zat']}</div>
                </div>
                ` : ''}
                
                ${standard['Dampak Kelebihan'] ? `
                <div class="info-section">
                    <div class="info-label">Excess Effects</div>
                    <div class="info-text">${standard['Dampak Kelebihan']}</div>
                </div>
                ` : ''}
                
                ${standard['Dampak Kekurangan'] ? `
                <div class="info-section">
                    <div class="info-label">Deficiency Effects</div>
                    <div class="info-text">${standard['Dampak Kekurangan']}</div>
                </div>
                ` : ''}
            </div>
        `);
        
        container.append(card);
    });
}

/**
 * Get appropriate icon for nutrient type
 * @param {string} nutrient - Nutrient name
 * @returns {string} Font Awesome icon class
 */
function getNutrientIcon(nutrient) {
    if (nutrient.includes('Energy')) return 'bolt';
    if (nutrient.includes('Protein')) return 'dumbbell';
    if (nutrient.includes('Fat')) return 'cheese';
    if (nutrient.includes('Carbohydrate')) return 'bread-slice';
    if (nutrient.includes('Vitamin')) return 'pills';
    if (nutrient.includes('Fiber')) return 'leaf';
    return 'atom';
}

/**
 * Create nutrition comparison chart using Chart.js
 * @param {Array} comparisons - Array of comparison objects
 */
function createNutritionChart(comparisons) {
    const canvas = document.getElementById('nutritionChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (currentChart) {
        currentChart.destroy();
    }
    
    // Filter comparisons with valid standards and food values
    const validComparisons = comparisons.filter(c => 
        c.standard && c.foodValue !== null && c.foodValue !== undefined && 
        (c.standard.Minimum || c.standard.Maximum)
    ).slice(0, 10); // Limit to 10 nutrients for better visualization
    
    if (validComparisons.length === 0) {
        // Show message if no data available for chart
        ctx.fillStyle = '#64748b';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No chart data available', canvas.width / 2, canvas.height / 2);
        return;
    }
    
    const labels = validComparisons.map(c => c.nutrient.replace(/ \([^)]*\)/g, '')); // Remove units from labels
    const foodValues = validComparisons.map(c => parseFloat(c.foodValue) || 0);
    
    currentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Food Value',
                    data: foodValues,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Nutrient Comparison: Food vs Standards'
                },
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const comparison = validComparisons[context.dataIndex];
                            return `Status: ${comparison.statusText}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Value'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Nutrients'
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Close the nutrition modal
 */
function closeModal() {
    $('#nutritionModal').fadeOut(300);
    $('body').removeClass('modal-open');
    
    // Destroy chart when closing modal
    if (currentChart) {
        currentChart.destroy();
        currentChart = null;
    }
}

/**
 * Show/hide loading indicator
 * @param {boolean} show - Whether to show loading indicator
 */
function showLoading(show) {
    if (show) {
        $('#loadingIndicator').show();
        $('.table-section').hide();
        $('#noResults').hide();
    } else {
        $('#loadingIndicator').hide();
    }
}

/**
 * Show error message
 * @param {string} message - Error message to display
 */
function showError(message) {
    console.error(message);
    
    // Create and show error modal or notification
    const errorHtml = `
        <div class="error-message" style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-width: 400px;
        ">
            <strong>Error:</strong> ${message}
            <button onclick="$(this).parent().fadeOut()" style="
                background: none;
                border: none;
                color: white;
                float: right;
                cursor: pointer;
                font-size: 18px;
                margin-left: 10px;
            ">&times;</button>
        </div>
    `;
    
    $('body').append(errorHtml);
    
    // Auto-remove error after 5 seconds
    setTimeout(() => {
        $('.error-message').fadeOut();
    }, 5000);
}

/**
 * Utility function to debounce function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Handle select all checkbox
 */
function handleSelectAll() {
    const isChecked = $('#selectAll').is(':checked');
    
    if (isChecked) {
        // Get foods currently displayed on this page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredFoodsData.length);
        const currentPageFoods = filteredFoodsData.slice(startIndex, endIndex);
        
        // Add current page foods to selection (avoid duplicates)
        currentPageFoods.forEach(food => {
            if (!selectedFoods.some(sf => sf.foodId === food.Menu)) {
                // Check if we haven't exceeded max selections
                if (selectedFoods.length < maxSelections) {
                    selectedFoods.push({
                        food: food,
                        foodId: food.Menu, // Use food name as ID
                        formattedName: formatFoodName(food.Menu)
                    });
                }
            }
        });
    } else {
        // Unselect only the foods on current page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredFoodsData.length);
        const currentPageFoods = filteredFoodsData.slice(startIndex, endIndex);
        
        // Remove current page foods from selection
        currentPageFoods.forEach(food => {
            selectedFoods = selectedFoods.filter(sf => sf.foodId !== food.Menu);
        });
    }
    
    updateSelectionUI();
    renderFoodTable();
    $('#selectAll').prop('checked', isChecked);

}

/**
 * Handle individual food selection
 */
function handleFoodSelection(foodIndex) {
    const food = filteredFoodsData[foodIndex];
    if (!food) {
        console.error('Food not found at index:', foodIndex);
        return;
    }
    
    const foodId = food.Menu; // Use food name as unique identifier
    const isCurrentlySelected = selectedFoods.some(sf => sf.foodId === foodId);
    
    if (isCurrentlySelected) {
        selectedFoods = selectedFoods.filter(sf => sf.foodId !== foodId);
    } else {
        if (selectedFoods.length >= maxSelections) {
            showError(`Maximum ${maxSelections} foods can be selected for comparison`);
            return;
        }
        
        selectedFoods.push({
            food: food,
            foodId: foodId, // Store food name as ID
            formattedName: formatFoodName(food.Menu)
        });
    }
    
    updateSelectionUI();
    renderFoodTable();
}

/**
 * Update selection UI elements
 */
function updateSelectionUI() {
    const count = selectedFoods.length;
    
    $('#selectedCount').text(count);
    
    // Show/hide comparison toolbar
    if (count > 0) {
        $('#comparisonToolbar').show();
    } else {
        $('#comparisonToolbar').hide();
    }
    
    // Update select all checkbox - check if all foods on current page are selected
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredFoodsData.length);
    const currentPageFoods = filteredFoodsData.slice(startIndex, endIndex);
    const currentPageSelectedCount = currentPageFoods.filter(food => 
        selectedFoods.some(sf => sf.foodId === food.Menu)
    ).length;
}

/**
 * Clear all selections
 */
function clearAllSelections() {
    selectedFoods = [];
    updateSelectionUI();
    renderFoodTable();
}

/**
 * Navigate to comparison page with selected foods
 */
function navigateToComparison() {
    console.log('navigateToComparison called, selectedFoods array:', selectedFoods);
    console.log('selectedFoods length:', selectedFoods.length);
    
    if (selectedFoods.length < 2) {
        showError('Please select at least 2 foods to compare');
        return;
    }
    
    // Create food names parameter - use the correct property path
    const foodNames = selectedFoods
        .map(selectedFood => {
            const foodName = selectedFood.food ? selectedFood.food.Menu : selectedFood.Menu;
            console.log('Processing food:', foodName);
            return encodeURIComponent(foodName || '');
        })
        .filter(name => name !== '') // Filter out empty names
        .join(',');
    
    console.log('Final foodNames string:', foodNames);
    console.log('Final URL:', `comparison.php?foods=${foodNames}`);
    
    if (!foodNames) {
        showError('No valid food names found for comparison');
        return;
    }
    
    window.location.href = `comparison.php?foods=${foodNames}`;
}

/**
 * Show multiple foods comparison modal
 */
function showMultiComparisonModal() {
    if (selectedFoods.length < 2) {
        showError('Please select at least 2 foods to compare');
        return;
    }
    
    console.log('Showing multi-comparison modal for', selectedFoods.length, 'foods');
    
    // Update summary
    $('#comparedFoodsCount').text(selectedFoods.length);
    $('#multiNutrientsCount').text(Object.keys(NUTRIENT_MAPPING).length);
    
    // Render selected foods list
    renderSelectedFoodsList();
    
    // Render comparison table
    renderMultiComparisonTable();
    
    // Create multi-comparison chart
    createMultiComparisonChart();
    
    // Generate insights
    generateNutritionalInsights();
    
    // Show modal
    $('#multiComparisonModal').fadeIn(300);
    $('body').addClass('modal-open');
}

/**
 * Render selected foods list
 */
function renderSelectedFoodsList() {
    const container = $('#selectedFoodsList');
    container.empty();
    
    selectedFoods.forEach((selectedFood, index) => {
        const food = selectedFood.food;
        const energyValue = formatNutrientValue(food['Energy (kJ)']);
        
        const card = $(`
            <div class="selected-food-card">
                <div class="food-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="food-info">
                    <div class="food-name">${escapeHtml(selectedFood.formattedName)}</div>
                    <div class="food-energy">${energyValue} kJ</div>
                </div>
            </div>
        `);
        
        container.append(card);
    });
}

/**
 * Render multi-comparison table
 */
function renderMultiComparisonTable() {
    const header = $('#multiComparisonHeader');
    const body = $('#multiComparisonBody');
    
    header.empty();
    body.empty();
    
    // Create header
    let headerRow = '<tr><th class="nutrient-name">Nutrient</th>';
    selectedFoods.forEach(selectedFood => {
        headerRow += `<th>${escapeHtml(selectedFood.formattedName)}</th>`;
    });
    headerRow += '<th>Recommended Range</th></tr>';
    header.html(headerRow);
    
    // Create rows for each nutrient
    Object.keys(NUTRIENT_MAPPING).forEach(nutrient => {
        const standard = standardsData.find(s => s.Nutrisi === nutrient);
        let row = `<tr><td class="nutrient-name"><strong>${nutrient}</strong></td>`;
        
        // Add values for each selected food
        selectedFoods.forEach(selectedFood => {
            const value = selectedFood.food[nutrient];
            const formattedValue = formatNutrientValue(value);
            
            // Determine status color based on standard
            let statusClass = '';
            if (standard && (standard.Minimum || standard.Maximum)) {
                const min = parseFloat(standard.Minimum) || null;
                const max = parseFloat(standard.Maximum) || null;
                const numValue = parseFloat(value) || 0;
                
                if (min !== null && max !== null) {
                    if (numValue >= min && numValue <= max) {
                        statusClass = 'status-normal';
                    } else if (numValue > max) {
                        statusClass = 'status-excess';
                    } else {
                        statusClass = 'status-deficiency';
                    }
                }
            }
            
            row += `<td><span class="${statusClass}">${formattedValue}</span></td>`;
        });
        
        // Add recommended range
        let rangeText = 'No standard';
        if (standard) {
            const min = standard.Minimum;
            const max = standard.Maximum;
            if (min && max) {
                rangeText = `${min} - ${max}`;
            } else if (min) {
                rangeText = `≥ ${min}`;
            } else if (max) {
                rangeText = `≤ ${max}`;
            } else if (standard['Rekomendasi Harian Anak (1-5 tahun)']) {
                rangeText = standard['Rekomendasi Harian Anak (1-5 tahun)'];
            }
        }
        
        row += `<td>${rangeText}</td></tr>`;
        body.append(row);
    });
}

/**
 * Create multi-comparison chart
 */
function createMultiComparisonChart() {
    const canvas = document.getElementById('multiComparisonChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (multiChart) {
        multiChart.destroy();
    }
    
    updateMultiChart();
}

/**
 * Update multi-comparison chart based on selected nutrient
 */
function updateMultiChart() {
    const selectedNutrient = $('#chartNutrientSelect').val();
    const canvas = document.getElementById('multiComparisonChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (multiChart) {
        multiChart.destroy();
    }
    
    let datasets = [];
    let labels = [];
    
    if (selectedNutrient === 'all') {
        // Show major nutrients for all foods
        const majorNutrients = ['Energy (kJ)', 'Protein (g)', 'Fat (g)', 'Carbohydrates (g)'];
        labels = majorNutrients.map(n => n.replace(/ \([^)]*\)/g, ''));
        
        selectedFoods.forEach((selectedFood, index) => {
            const data = majorNutrients.map(nutrient => {
                const value = selectedFood.food[nutrient];
                return parseFloat(value) || 0;
            });
            
            const colors = [
                'rgba(37, 99, 235, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)'
            ];
            
            datasets.push({
                label: selectedFood.formattedName,
                data: data,
                backgroundColor: colors[index % colors.length],
                borderColor: colors[index % colors.length].replace('0.8', '1'),
                borderWidth: 1
            });
        });
    } else {
        // Show specific nutrient comparison
        labels = selectedFoods.map(sf => sf.formattedName);
        const data = selectedFoods.map(selectedFood => {
            const value = selectedFood.food[selectedNutrient];
            return parseFloat(value) || 0;
        });
        
        datasets.push({
            label: selectedNutrient,
            data: data,
            backgroundColor: 'rgba(37, 99, 235, 0.8)',
            borderColor: 'rgba(37, 99, 235, 1)',
            borderWidth: 1
        });
    }
    
    multiChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: selectedNutrient === 'all' ? 'Major Nutrients Comparison' : `${selectedNutrient} Comparison`
                },
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Value'
                    }
                }
            }
        }
    });
}

/**
 * Generate nutritional insights and recommendations
 */
function generateNutritionalInsights() {
    const container = $('#nutritionalInsights');
    container.empty();
    
    // Analyze energy content
    const energyValues = selectedFoods.map(sf => parseFloat(sf.food['Energy (kJ)']) || 0);
    const avgEnergy = energyValues.reduce((a, b) => a + b, 0) / energyValues.length;
    const maxEnergy = Math.max(...energyValues);
    const minEnergy = Math.min(...energyValues);
    const highestEnergyFood = selectedFoods[energyValues.indexOf(maxEnergy)];
    
    const energyCard = $(`
        <div class="insight-card ${maxEnergy > 2000 ? 'highlight' : 'positive'}">
            <h4><i class="fas fa-bolt"></i> Energy Analysis</h4>
            <p>Average energy: <strong>${avgEnergy.toFixed(0)} kJ</strong></p>
            <p>Highest: <strong>${highestEnergyFood.formattedName}</strong> (${maxEnergy.toFixed(0)} kJ)</p>
            <div class="recommendation">
                ${maxEnergy > 2000 ? 
                    'Some selected foods are high in energy. Consider portion control.' : 
                    'Energy levels are moderate. Good for balanced nutrition.'
                }
            </div>
        </div>
    `);
    container.append(energyCard);
    
    // Analyze protein content
    const proteinValues = selectedFoods.map(sf => parseFloat(sf.food['Protein (g)']) || 0);
    const avgProtein = proteinValues.reduce((a, b) => a + b, 0) / proteinValues.length;
    const maxProtein = Math.max(...proteinValues);
    const highestProteinFood = selectedFoods[proteinValues.indexOf(maxProtein)];
    
    const proteinCard = $(`
        <div class="insight-card ${avgProtein >= 15 ? 'positive' : 'highlight'}">
            <h4><i class="fas fa-dumbbell"></i> Protein Analysis</h4>
            <p>Average protein: <strong>${avgProtein.toFixed(1)} g</strong></p>
            <p>Highest: <strong>${highestProteinFood.formattedName}</strong> (${maxProtein.toFixed(1)} g)</p>
            <div class="recommendation">
                ${avgProtein >= 15 ? 
                    'Good protein content. Excellent for growth and development.' : 
                    'Consider adding more protein-rich foods to meet daily requirements.'
                }
            </div>
        </div>
    `);
    container.append(proteinCard);
    
    // Analyze variety
    const varietyCard = $(`
        <div class="insight-card positive">
            <h4><i class="fas fa-seedling"></i> Nutritional Variety</h4>
            <p>You've selected <strong>${selectedFoods.length} different foods</strong> for comparison.</p>
            <div class="recommendation">
                Comparing multiple foods helps identify the best nutritional choices for balanced meals.
            </div>
        </div>
    `);
    container.append(varietyCard);
}

/**
 * Close multi-comparison modal
 */
function closeMultiModal() {
    $('#multiComparisonModal').fadeOut(300);
    $('body').removeClass('modal-open');
    
    // Destroy chart when closing modal
    if (multiChart) {
        multiChart.destroy();
        multiChart = null;
    }
}

// Update the existing setupEventListeners to include checkbox handlers
$(document).on('change', '.food-checkbox', function() {
    const foodIndex = parseInt($(this).data('food-index'));
    handleFoodSelection(foodIndex);
});

// Export functions for testing (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        parseCSV,
        formatNutrientValue,
        generateComparisonData,
        NUTRIENT_MAPPING,
        handleFoodSelection,
        selectedFoods
    };
}