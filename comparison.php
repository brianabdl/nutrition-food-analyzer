<?php
/**
 * Comparison Page (PHP Version)
 */

require_once __DIR__ . '/config/config.php';

// Page configuration
$page_title = 'Multiple Foods Comparison - Food Nutrition Analyzer';
$header_title = 'Multiple Foods Comparison';
$header_subtitle = 'Compare nutritional values across multiple foods';
$header_icon = 'balance-scale';
$show_back_link = true;
$include_chart = true;

include INCLUDES_PATH . '/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="loading-indicator">
                <div class="spinner"></div>
                <p>Loading comparison data...</p>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="no-results" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to Load Comparison Data</h3>
                <p>Please check if the food parameters are provided or try again.</p>
            </div>

            <!-- Comparison Content -->
            <div id="comparisonContent" style="display: none;">
                <!-- Comparison Summary -->
                <section class="multi-summary-section">
                    <div class="summary-card">
                        <div class="summary-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Foods Compared</h3>
                            <span id="comparedFoodsCount" class="summary-value">0</span>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Nutrients Analyzed</h3>
                            <span id="multiNutrientsCount" class="summary-value">0</span>
                        </div>
                    </div>
                </section>

                <!-- Food Selection Display -->
                <section class="selected-foods-section">
                    <h3>Selected Foods</h3>
                    <div id="selectedFoodsList" class="selected-foods-list">
                        <!-- Dynamic food cards will be inserted here -->
                    </div>
                </section>

                <!-- Multi Chart Section -->
                <section class="chart-section">
                    <h3>Nutritional Comparison Chart</h3>
                    <div class="chart-controls">
                        <select id="chartNutrientSelect" class="nutrient-selector">
                            <option value="all">All Major Nutrients</option>
                            <option value="Energy (kJ)">Energy (kJ)</option>
                            <option value="Protein (g)">Protein (g)</option>
                            <option value="Fat (g)">Fat (g)</option>
                            <option value="Carbohydrates (g)">Carbohydrates (g)</option>
                            <option value="Dietary Fiber (g)">Dietary Fiber (g)</option>
                            <option value="Vitamin C (mg)">Vitamin C (mg)</option>
                            <option value="Calcium (mg)">Calcium (mg)</option>
                            <option value="Iron (mg)">Iron (mg)</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="multiComparisonChart"></canvas>
                    </div>
                </section>

                <!-- Detailed Multi-Comparison Table -->
                <section class="comparison-section">
                    <h3>Detailed Nutritional Comparison</h3>
                    <div class="comparison-table-container">
                        <div class="table-scroll">
                            <table class="multi-comparison-table">
                                <thead id="multiComparisonHeader">
                                    <!-- Dynamic headers will be inserted here -->
                                </thead>
                                <tbody id="multiComparisonBody">
                                    <!-- Dynamic content will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Recommendations Section -->
                <section class="recommendations-section">
                    <h3>Nutritional Insights & Recommendations</h3>
                    <div id="nutritionalInsights" class="insights-container">
                        <!-- Dynamic recommendations will be inserted here -->
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- JavaScript - Updated for PHP backend -->
    <script>
        const API_URL = 'controllers/api.php';
    </script>
    <script src="comparison.js"></script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
