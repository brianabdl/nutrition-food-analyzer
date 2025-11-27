<?php
/**
 * About Page (PHP Version)
 */

require_once __DIR__ . '/config/config.php';

// Page configuration
$page_title = 'About the Team - Food Nutrition Analyzer';
$header_title = 'About the Team';
$header_subtitle = 'Meet the developers behind the Food Nutrition Analyzer';
$header_icon = 'users';

include INCLUDES_PATH . '/header.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Navigation Back -->
            <section class="navigation-section">
                <a href="index.php" class="btn btn-secondary nav-back">
                    <i class="fas fa-arrow-left"></i>
                    Back to App
                </a>
            </section>

            <!-- Project Information -->
            <section class="project-info-section">
                <div class="project-card">
                    <div class="project-header">
                        <i class="fas fa-apple-alt project-icon"></i>
                        <div class="project-details">
                            <h2>Food Nutrition Analyzer</h2>
                            <p>A comprehensive web application for analyzing and comparing food nutrition data with health standards</p>
                        </div>
                    </div>
                    
                    <div class="project-features">
                        <div class="feature-item">
                            <i class="fas fa-search"></i>
                            <span>Search & Filter Foods</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chart-bar"></i>
                            <span>Nutrition Analysis</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-balance-scale"></i>
                            <span>Multi-Food Comparison</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Responsive Design</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Team Members Section -->
            <section class="team-section">
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Development Team
                </h2>
                
                <div class="team-grid">
                    <!-- Team Member 1 -->
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Muhammad Brian Abdillah</h3>
                            <p class="member-id">24091397052</p>
                            <p class="member-role">Lead Developer</p>
                            <div class="member-skills">
                                <span class="skill-tag">Frontend Development</span>
                                <span class="skill-tag">Backend PHP</span>
                                <span class="skill-tag">JavaScript</span>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Renita Dwi Setiyani</h3>
                            <p class="member-id">24091397098</p>
                            <p class="member-role">Frontend Developer</p>
                            <div class="member-skills">
                                <span class="skill-tag">CSS Styling</span>
                                <span class="skill-tag">Responsive Design</span>
                                <span class="skill-tag">Data Visualization</span>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 3 -->
                    <div class="team-member">
                        <div class="member-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="member-info">
                            <h3 class="member-name">Hanson Philip</h3>
                            <p class="member-id">24091397034</p>
                            <p class="member-role">Backend Developer</p>
                            <div class="member-skills">
                                <span class="skill-tag">Data Processing</span>
                                <span class="skill-tag">Algorithm Design</span>
                                <span class="skill-tag">Testing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Technologies Used Section -->
            <section class="tech-section">
                <h2 class="section-title">
                    <i class="fas fa-code"></i>
                    Technologies Used
                </h2>
                
                <div class="tech-grid">
                    <div class="tech-item">
                        <i class="fab fa-php"></i>
                        <span>PHP 8.1</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-database"></i>
                        <span>SQLite</span>
                    </div>
                    <div class="tech-item">
                        <i class="fab fa-html5"></i>
                        <span>HTML</span>
                    </div>
                    <div class="tech-item">
                        <i class="fab fa-css3-alt"></i>
                        <span>CSS</span>
                    </div>
                    <div class="tech-item">
                        <i class="fab fa-js-square"></i>
                        <span>JavaScript</span>
                    </div>
                    <div class="tech-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Chart.js</span>
                    </div>
                </div>
            </section>

            <!-- Project Stats Section -->
            <section class="stats-section">
                <h2 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    Project Statistics
                </h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1000+</h3>
                            <p>Food Items</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-content">
                            <h3>21</h3>
                            <p>Nutrients Analyzed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="stat-content">
                            <h3>3000+</h3>
                            <p>Lines of Code</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3>100%</h3>
                            <p>Responsive</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Acknowledgments Section -->
            <section class="acknowledgments-section">
                <div class="acknowledgment-card">
                    <h2>
                        <i class="fas fa-heart"></i>
                        Acknowledgments
                    </h2>
                    <p>We would like to thank:</p>
                    <ul>
                        <li><strong>Nauval Almas</strong> for providing the comprehensive nutrition dataset on Kaggle</li>
                        <li><strong>Open Source Community</strong> for the amazing libraries and tools used in this project</li>
                        <li><strong>Chart.js Team</strong> for the powerful data visualization library</li>
                        <li><strong>Font Awesome</strong> for the beautiful icon set</li>
                        <li><strong>Our Instructors</strong> for guidance and support throughout development</li>
                    </ul>
                </div>
            </section>
        </div>
    </main>

<?php include INCLUDES_PATH . '/footer.php'; ?>
