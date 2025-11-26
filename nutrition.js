/**
 * Nutrition Analysis Page
 * Standalone page for detailed food nutrition analysis
 */

// Global variables
let currentChart = null;
let foodsData = [];
let standardsData = [];

// Configuration object for nutrient mapping (same as main app)
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

// Initialize when page loads
$(document).ready(function() {
    console.log('Initializing Nutrition Analysis Page...');
    initializePage();
});

/**
 * Initialize the nutrition analysis page
 */
function initializePage() {
    // Get food name from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const foodName = urlParams.get('food');
    
    if (!foodName) {
        showErrorMessage('No food specified for analysis');
        return;
    }
    
    // Load data and show analysis
    loadDataAndAnalyze(decodeURIComponent(foodName));
}

/**
 * Load CSV data and perform analysis
 * @param {string} foodName - Name of food to analyze
 */
async function loadDataAndAnalyze(foodName) {
    try {
        showLoading(true);
        
        // Load data from PHP API
        const response = await fetch(`${API_URL}?action=get_nutrition_analysis&name=${encodeURIComponent(foodName)}`);
        
        if (!response.ok) {
            throw new Error('Failed to load data from API');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            if (result.error && result.error.includes('not found')) {
                showErrorMessage(`Food "${foodName}" not found in database`);
            } else {
                showErrorMessage(result.error || 'Failed to load nutrition data');
            }
            return;
        }
        
        // Extract data from API response
        const food = result.data.food;
        const comparisons = result.data.comparisons;
        const statistics = result.data.statistics;
        
        // Show analysis
        showNutritionAnalysisFromAPI(food, comparisons, statistics);
        
    } catch (error) {
        console.error('Error loading data:', error);
        showErrorMessage('Failed to load nutrition data');
    } finally {
        showLoading(false);
    }
}

/**
 * Show nutrition analysis from API response
 */
function showNutritionAnalysisFromAPI(food, comparisons, statistics) {
    // Set food name in title
    const formattedName = formatFoodName(food.Menu);
    $('#foodName').text(formattedName);
    
    // Update summary cards with API statistics
    $('#totalNutrients').text(statistics.total_nutrients);
    $('#safeNutrients').text(statistics.safe_nutrients);
    $('#safetyPercentage').text(`${statistics.safety_percentage}%`);
    
    // Render comparison table
    renderComparisonTableFromAPI(comparisons);
    
    // Render nutrient information cards
    renderNutrientInfoCardsFromAPI(comparisons);
    
    // Create nutrition chart
    createNutritionChartFromAPI(comparisons);
    
    // Show content and hide loading
    $('#nutritionContent').show();
}

/**
 * Parse CSV text into array of objects
 * @param {string} csvText - CSV text data
 * @returns {Array} Array of objects
 */
function parseCSV(csvText) {
    const lines = csvText.split('\n').filter(line => line.trim());
    if (lines.length < 2) return [];
    
    const headers = lines[0].split(',').map(header => header.trim().replace(/"/g, ''));
    const data = [];
    
    for (let i = 1; i < lines.length; i++) {
        const values = parseCSVLine(lines[i]);
        if (values.length >= headers.length) {
            const row = {};
            headers.forEach((header, index) => {
                row[header] = values[index] ? values[index].trim().replace(/"/g, '') : '';
            });
            data.push(row);
        }
    }
    
    return data;
}

/**
 * Parse a single CSV line handling quotes and commas
 * @param {string} line - CSV line
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
 * Find food by name in the loaded data
 * @param {string} foodName - Name to search for
 * @returns {Object|null} Food object or null if not found
 */
function findFood(foodName) {
    const searchName = foodName.toLowerCase().trim();
    console.log('Searching for food:', searchName);
    console.log('Available foods (first 5):', foodsData.slice(0, 5).map(f => f.Menu));
    
    const found = foodsData.find(food => {
        const menuName = (food.Menu || '').toLowerCase().trim();
        const formattedName = formatFoodName(food.Menu).toLowerCase().trim();
        
        // Try exact match first
        if (menuName === searchName || formattedName === searchName) {
            return true;
        }
        
        // Try partial match
        if (menuName.includes(searchName) || formattedName.includes(searchName)) {
            return true;
        }
        
        // Try reverse partial match (search name contains menu name)
        if (searchName.includes(menuName) || searchName.includes(formattedName)) {
            return true;
        }
        
        return false;
    });
    
    console.log('Found food:', found ? found.Menu : 'Not found');
    return found;
}

/**
 * Format food name for consistent display
 * @param {string} name - Food name
 * @returns {string} Formatted name
 */
function formatFoodName(name) {
    if (!name) return '';
    
    // Split by common separators and capitalize each part
    return name.split(/[\/,\\-]/)
        .map(part => {
            const trimmed = part.trim();
            // Don't modify parts that are already properly formatted Indonesian names
            if (/^[A-Z]/.test(trimmed) || /[A-Z]/.test(trimmed)) {
                return trimmed;
            }
            // Capitalize first letter of each word for simple names
            return trimmed.split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                .join(' ');
        })
        .join(' / ');
}

/**
 * Show nutrition analysis for the food
 * @param {Object} food - Food object
 */
function showNutritionAnalysis(food) {
    // Set food name in title
    const formattedName = formatFoodName(food.Menu);
    $('#foodName').text(formattedName);
    
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
    
    // Show content and hide loading
    $('#nutritionContent').show();
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
 * Render the detailed comparison table from API data
 * @param {Array} comparisons - Array of comparison objects from API
 */
function renderComparisonTableFromAPI(comparisons) {
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
                    <span class="nutrient-value">${formatNutrientValue(comparison.food_value)}</span>
                </td>
                <td>${rangeText}</td>
                <td>
                    <span class="status-badge status-${comparison.status}">
                        <i class="fas fa-${getStatusIcon(comparison.status)}"></i>
                        ${comparison.status_text}
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
        const comparison = comparisons.find(c => c.nutrient === nutrient);
        if (comparison && comparison.standard) {
            showNutrientTooltipFromStandard(e, comparison.standard);
        }
    });
}

/**
 * Show tooltip with nutrient information from standard object
 */
function showNutrientTooltipFromStandard(event, standard) {
    const tooltip = $('#tooltip');
    let content = `<strong>${standard.Nutrisi}</strong><br>`;
    
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
 * Render nutrient information cards from API data
 * @param {Array} comparisons - Array of comparison objects from API
 */
function renderNutrientInfoCardsFromAPI(comparisons) {
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
 * Create nutrition comparison chart from API data
 * @param {Array} comparisons - Array of comparison objects from API
 */
function createNutritionChartFromAPI(comparisons) {
    const canvas = document.getElementById('nutritionChart');
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (currentChart) {
        currentChart.destroy();
    }
    
    // Filter comparisons with valid standards and food values
    const validComparisons = comparisons.filter(c => 
        c.standard && c.food_value !== null && c.food_value !== undefined && 
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
    const foodValues = validComparisons.map(c => parseFloat(c.food_value) || 0);
    const minValues = validComparisons.map(c => parseFloat(c.standard.Minimum) || 0);
    const maxValues = validComparisons.map(c => parseFloat(c.standard.Maximum) || parseFloat(c.standard.Minimum) || 0);
    
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
                {
                    label: 'Minimum Standard',
                    data: minValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Maximum Standard',
                    data: maxValues,
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
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
                            return `Status: ${comparison.status_text}`;
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
 * Show loading indicator
 * @param {boolean} show - Whether to show loading
 */
function showLoading(show) {
    if (show) {
        $('#loadingIndicator').show();
        $('#nutritionContent').hide();
        $('#errorMessage').hide();
    } else {
        $('#loadingIndicator').hide();
    }
}

/**
 * Show error message
 * @param {string} message - Error message
 */
function showErrorMessage(message) {
    $('#errorMessage').find('p').text(message);
    $('#errorMessage').show();
    $('#loadingIndicator').hide();
    $('#nutritionContent').hide();
}