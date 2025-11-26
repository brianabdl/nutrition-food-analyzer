<?php
/**
 * Header Include
 * Reusable header component
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Comprehensive food nutrition analysis tool for comparing food nutrients with standard health recommendations">
    <meta name="keywords" content="food nutrition, nutrition analyzer, food comparison, health recommendations, nutritional values">
    <title><?php echo $page_title ?? 'Food Nutrition Analyzer'; ?></title>
    <link rel="stylesheet" href="style.css">
    <!-- jQuery CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <?php if (isset($include_chart) && $include_chart): ?>
    <!-- Chart.js CDN for charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <?php endif; ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <div class="header-main">
                <h1 class="app-title">
                    <i class="fas fa-<?php echo $header_icon ?? 'apple-alt'; ?>"></i>
                    <div class="title-content">
                        <span class="title-text"><?php echo $header_title ?? 'Food Nutrition Analyzer'; ?></span>
                        <p class="app-subtitle"><?php echo $header_subtitle ?? 'Compare food nutrients with standard health recommendations'; ?></p>
                    </div>
                </h1>
            </div>
            <?php if (isset($show_back_link) && $show_back_link): ?>
            <div class="header-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Search
                </a>
            </div>
            <?php endif; ?>
            <?php if (isset($show_about_link) && $show_about_link): ?>
            <div class="header-nav">
                <a href="about.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    About Team
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>
