<?php
/**
 * Nutrition Analysis Page (PHP Version)
 * Server-side rendering with direct database queries
 */

require_once __DIR__ . '/config/config.php';
require_once MODELS_PATH . '/Food.php';
require_once MODELS_PATH . '/Standard.php';

// Require login
$require_login = true;

// Get food name from URL
$foodName = isset($_GET['food']) ? trim($_GET['food']) : '';

if (empty($foodName)) {
    header('Location: index.php');
    exit;
}

// Initialize models
$foodModel = new Food();
$standardModel = new Standard();

// Get food data
$food = $foodModel->getFoodByName($foodName);

if (!$food) {
    $error_message = "Food '{$foodName}' not found in database";
} else {
    // Get standards
    $standards = $standardModel->getAllStandards();
    
    // Generate comparisons
    $comparisons = [];
    $totalNutrients = 0;
    $safeNutrients = 0;
    
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
                    $safeNutrients++;
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
                    $safeNutrients++;
                } else {
                    $comparison['status'] = 'deficiency';
                    $comparison['status_text'] = 'Deficient';
                }
            } elseif ($max !== null) {
                if ($value <= $max) {
                    $comparison['status'] = 'normal';
                    $comparison['status_text'] = 'Normal Range';
                    $comparison['in_range'] = true;
                    $safeNutrients++;
                } else {
                    $comparison['status'] = 'excess';
                    $comparison['status_text'] = 'Excess';
                }
            }
        }
        
        $comparisons[] = $comparison;
        $totalNutrients++;
    }
    
    $safetyPercentage = $totalNutrients > 0 ? round(($safeNutrients / $totalNutrients) * 100) : 0;
}

// Page configuration
$page_title = 'Nutrition Analysis - Food Nutrition Analyzer';
$header_title = 'Food Nutrition Analysis';
$header_subtitle = 'Detailed nutritional breakdown and health comparison';
$header_icon = 'apple-alt';
$show_back_link = true;
$include_chart = true;

include INCLUDES_PATH . '/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if (isset($error_message)): ?>
            <!-- Error Message -->
            <div class="no-results">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to Load Nutrition Data</h3>
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <a href="index.php" class="btn btn-primary">Back to Search</a>
            </div>
            <?php else: ?>
            <!-- Nutrition Content -->
            <div id="nutritionContent">
                <!-- Food Title -->
                <section class="food-title-section">
                    <h2 class="food-title"><?php echo htmlspecialchars( $foodModel->formatFoodName($food['Menu'])); ?></h2>
                </section>

                <!-- Summary Cards -->
                <section class="summary-section">
                    <div class="summary-card">
                        <div class="summary-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Total Nutrients Analyzed</h3>
                            <span class="summary-value"><?php echo $totalNutrients; ?></span>
                        </div>
                    </div>
                    <div class="summary-card safe-range">
                        <div class="summary-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Nutrients in Safe Range</h3>
                            <span class="summary-value"><?php echo $safeNutrients; ?></span>
                        </div>
                    </div>
                    <div class="summary-card percentage">
                        <div class="summary-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Safety Percentage</h3>
                            <span class="summary-value"><?php echo $safetyPercentage; ?>%</span>
                        </div>
                    </div>
                </section>

                <!-- Chart Section -->
                <section class="chart-section">
                    <h3>Nutrient Comparison Chart</h3>
                    <div class="chart-container">
                        <canvas id="nutritionChart"></canvas>
                    </div>
                </section>

                <!-- Detailed Comparison Table -->
                <section class="comparison-section">
                    <h3>Detailed Nutrient Analysis</h3>
                    <div class="comparison-table-container">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Nutrient</th>
                                    <th>Food Value</th>
                                    <th>Recommended Range</th>
                                    <th>Status</th>
                                    <th>Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comparisons as $comparison): 
                                    $standard = $comparison['standard'];
                                    $rangeText = 'No standard available';
                                    
                                    if ($standard) {
                                        $min = $standard['Minimum'];
                                        $max = $standard['Maximum'];
                                        $recommendation = $standard['Rekomendasi Harian Anak (1-5 tahun)'];
                                        
                                        if ($min && $max) {
                                            $rangeText = "$min - $max";
                                        } elseif ($min) {
                                            $rangeText = "≥ $min";
                                        } elseif ($max) {
                                            $rangeText = "≤ $max";
                                        } elseif ($recommendation) {
                                            $rangeText = $recommendation;
                                        }
                                    }
                                    
                                    $statusIcons = [
                                        'normal' => 'check-circle',
                                        'excess' => 'exclamation-triangle',
                                        'deficiency' => 'arrow-down',
                                        'no-standard' => 'question-circle'
                                    ];
                                ?>
                                <tr class="comparison-row">
                                    <td><strong><?php echo htmlspecialchars($comparison['nutrient']); ?></strong></td>
                                    <td>
                                        <span class="nutrient-value">
                                            <?php echo $comparison['food_value'] !== null ? number_format($comparison['food_value'], 2) : '-'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($rangeText); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $comparison['status']; ?>">
                                            <i class="fas fa-<?php echo $statusIcons[$comparison['status']]; ?>"></i>
                                            <?php echo $comparison['status_text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($standard && ($standard['Fungsi Zat'] || $standard['Dampak Kelebihan'] || $standard['Dampak Kekurangan'])): ?>
                                        <button class="info-btn" 
                                                data-nutrient="<?php echo htmlspecialchars($comparison['nutrient']); ?>"
                                                data-function="<?php echo htmlspecialchars($standard['Fungsi Zat'] ?? ''); ?>"
                                                data-excess="<?php echo htmlspecialchars($standard['Dampak Kelebihan'] ?? ''); ?>"
                                                data-deficiency="<?php echo htmlspecialchars($standard['Dampak Kekurangan'] ?? ''); ?>">
                                            <i class="fas fa-info"></i>
                                        </button>
                                        <?php else: ?>
                                        -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Nutrient Information Cards -->
                <section class="nutrient-info-section">
                    <?php
                    $detailedComparisons = array_filter($comparisons, function($c) {
                        $s = $c['standard'];
                        return $s && ($s['Fungsi Zat'] || $s['Dampak Kelebihan'] || $s['Dampak Kekurangan']);
                    });
                    
                    $nutrientIcons = [
                        'Energy' => 'bolt', 'Protein' => 'dumbbell', 'Fat' => 'cheese',
                        'Carbohydrate' => 'bread-slice', 'Vitamin' => 'pills', 'Fiber' => 'leaf'
                    ];
                    
                    foreach (array_slice($detailedComparisons, 0, 6) as $comparison):
                        $standard = $comparison['standard'];
                        $icon = 'atom';
                        foreach ($nutrientIcons as $key => $val) {
                            if (strpos($comparison['nutrient'], $key) !== false) {
                                $icon = $val;
                                break;
                            }
                        }
                    ?>
                    <div class="nutrient-info-card">
                        <h4>
                            <i class="fas fa-<?php echo $icon; ?>"></i>
                            <?php echo htmlspecialchars($comparison['nutrient']); ?>
                        </h4>
                        
                        <?php if ($standard['Fungsi Zat']): ?>
                        <div class="info-section">
                            <div class="info-label">Function</div>
                            <div class="info-text"><?php echo htmlspecialchars($standard['Fungsi Zat']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($standard['Dampak Kelebihan']): ?>
                        <div class="info-section">
                            <div class="info-label">Excess Effects</div>
                            <div class="info-text"><?php echo htmlspecialchars($standard['Dampak Kelebihan']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($standard['Dampak Kekurangan']): ?>
                        <div class="info-section">
                            <div class="info-label">Deficiency Effects</div>
                            <div class="info-text"><?php echo htmlspecialchars($standard['Dampak Kekurangan']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </section>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Tooltip for additional information -->
    <div id="tooltip" class="tooltip"></div>

    <!-- JavaScript for chart and interactions -->
    <script>
        <?php if (!isset($error_message)): ?>
        const comparisons = <?php echo json_encode($comparisons); ?>;
        const food = <?php echo json_encode($food); ?>;
        
        // Create nutrition chart
        document.addEventListener('DOMContentLoaded', function() {
            const validComparisons = comparisons.filter(c => 
                c.standard && c.food_value !== null && 
                (c.standard.Minimum || c.standard.Maximum)
            ).slice(0, 10);
            
            if (validComparisons.length > 0) {
                const ctx = document.getElementById('nutritionChart').getContext('2d');
                const labels = validComparisons.map(c => c.nutrient.replace(/ \([^)]*\)/g, ''));
                const foodValues = validComparisons.map(c => parseFloat(c.food_value) || 0);
                const minValues = validComparisons.map(c => parseFloat(c.standard.Minimum) || 0);
                const maxValues = validComparisons.map(c => parseFloat(c.standard.Maximum) || parseFloat(c.standard.Minimum) || 0);
                
                new Chart(ctx, {
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
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Value' }
                            },
                            x: {
                                title: { display: true, text: 'Nutrients' },
                                ticks: { maxRotation: 45, minRotation: 45 }
                            }
                        }
                    }
                });
            }
            
            // Tooltip functionality
            $('.info-btn').on('click', function(e) {
                e.stopPropagation();
                const nutrient = $(this).data('nutrient');
                const func = $(this).data('function');
                const excess = $(this).data('excess');
                const deficiency = $(this).data('deficiency');
                
                let content = '<strong>' + nutrient + '</strong><br>';
                if (func) content += '<strong>Function:</strong> ' + func + '<br>';
                if (excess) content += '<strong>Excess Effects:</strong> ' + excess + '<br>';
                if (deficiency) content += '<strong>Deficiency Effects:</strong> ' + deficiency;
                
                $('#tooltip').html(content).css({
                    top: e.pageY + 10,
                    left: e.pageX + 10
                }).addClass('show');
                
                setTimeout(() => $('#tooltip').removeClass('show'), 5000);
                $(document).one('click', () => $('#tooltip').removeClass('show'));
            });
        });
        <?php endif; ?>
    </script>

<?php include INCLUDES_PATH . '/footer.php'; ?>
