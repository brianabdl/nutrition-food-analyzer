<?php
/**
 * User Profile Page
 * Displays user information and logout functionality
 */

// Page configuration
$page_title = 'My Profile - Food Nutrition Analyzer';
$header_title = 'My Profile';
$header_subtitle = 'View and manage your account information';
$header_icon = 'user-circle';
$show_active_users_link = true;
$show_back_link = true;
$show_about_link = true;
$require_login = true;

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="profile-page">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></h2>
                    <p class="profile-subtitle">Student Account</p>
                </div>

                <div class="profile-card-body">
                    <div class="profile-info-section">
                        <h3 class="section-title">
                            <i class="fas fa-id-card"></i>
                            Account Information
                        </h3>

                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-user"></i>
                                    Full Name
                                </label>
                                <div class="profile-info-value">
                                    <?php echo htmlspecialchars($currentUser['name']); ?>
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-id-badge"></i>
                                    NIM (Student ID)
                                </label>
                                <div class="profile-info-value">
                                    <?php echo htmlspecialchars($currentUser['nim']); ?>
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <div class="profile-info-value">
                                    <?php echo htmlspecialchars($currentUser['email']); ?>
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Member Since
                                </label>
                                <div class="profile-info-value">
                                    <?php
                                    $date = new DateTime($currentUser['created_at']);
                                    echo $date->format('F j, Y');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Information -->
                    <div class="profile-info-section">
                        <h3 class="section-title">
                            <i class="fas fa-clock"></i>
                            Session Information
                        </h3>

                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Last Login
                                </label>
                                <div class="profile-info-value">
                                    <?php
                                    if (isset($currentUser['last_activity']) && !empty($currentUser['last_activity'])) {
                                        // Check if it's a Unix timestamp (integer)
                                        if (is_numeric($currentUser['last_activity'])) {
                                            $lastActivity = new DateTime();
                                            $lastActivity->setTimestamp((int) $currentUser['last_activity']);
                                        } else {
                                            // It's a datetime string
                                            $lastActivity = new DateTime($currentUser['last_activity']);
                                        }
                                        echo $lastActivity->format('F j, Y \a\t g:i A');
                                    } else {
                                        echo 'Just now';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="profile-info-item">
                                <label class="profile-info-label">
                                    <i class="fas fa-hourglass-half"></i>
                                    Session Status
                                </label>
                                <div class="profile-info-value">
                                    <span class="status-badge status-active">
                                        <i class="fas fa-circle"></i>
                                        Active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Actions -->
                <div class="profile-card-footer">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>