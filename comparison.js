/**
 * Multiple Foods Comparison Page
 * Standalone page for comparing multiple food nutrition profiles
 */

// Global variables
let multiChart = null;
let foodsData = [];
let standardsData = [];
let selectedFoods = [];

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
    console.log('Initializing Multiple Foods Comparison Page...');
    initializePage();
});

/**
 * Initialize the comparison page
 */
function initializePage() {
    // Get food names from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const foodsParam = urlParams.get('foods');
    
    if (!foodsParam) {
        showErrorMessage('No foods specified for comparison');
        return;
    }
    
    // Parse food names (comma-separated, URL-encoded)
    const foodNames = foodsParam.split(',').map(name => decodeURIComponent(name.trim()));
    
    if (foodNames.length < 2) {
        showErrorMessage('At least 2 foods are required for comparison');
        return;
    }
    
    // Load data and show comparison
    loadDataAndCompare(foodNames);
}

/**
 * Load CSV data and perform comparison
 * @param {Array} foodNames - Array of food names to compare
 */
async function loadDataAndCompare(foodNames) {
    try {
        showLoading(true);
        
        // Load data from PHP API
        const namesParam = foodNames.map(encodeURIComponent).join(',');
        const response = await fetch(`${API_URL}?action=get_comparison&names=${namesParam}`);
        
        if (!response.ok) {
            throw new Error('Failed to load data from API');
        }
        
        const result = await response.json();
        
        if (!result.success) {
            showErrorMessage(result.error || 'Failed to load comparison data');
            return;
        }
        
        console.log(`Loaded ${result.data.count} foods from API`);
        
        const foods = result.data.foods;
        standardsData = result.data.standards;
        
        if (foods.length < 2) {
            showErrorMessage('Could not find enough foods for comparison');
            return;
        }
        
        // Format foods for display
        selectedFoods = foods.map(food => ({
            food: food,
            formattedName: formatFoodName(food.Menu)
        }));
        
        // Show comparison
        showMultiComparison();
        
    } catch (error) {
        console.error('Error loading data:', error);
        showErrorMessage('Failed to load comparison data');
    } finally {
        showLoading(false);
    }
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
    
    return foodsData.find(food => {
        const menuName = (food.Menu || '').toLowerCase().trim();
        const formattedName = formatFoodName(food.Menu).toLowerCase().trim();
        
        return menuName === searchName || 
               formattedName === searchName ||
               menuName.includes(searchName) ||
               formattedName.includes(searchName) ||
               searchName.includes(menuName) ||
               searchName.includes(formattedName);
    });
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
 * Show multiple foods comparison
 */
function showMultiComparison() {
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
    
    // Setup chart nutrient selector
    setupChartControls();
    
    // Show content and hide loading
    $('#comparisonContent').show();
}

/**
 * Setup chart controls
 */
function setupChartControls() {
    $('#chartNutrientSelect').on('change', function() {
        updateMultiChart();
    });
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
 * Show loading indicator
 * @param {boolean} show - Whether to show loading
 */
function showLoading(show) {
    if (show) {
        $('#loadingIndicator').show();
        $('#comparisonContent').hide();
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
    $('#comparisonContent').hide();
}