<?php
/**
 * Header Include
 * Reusable header component
 */

// Include session management
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';
require_once MODELS_PATH . '/User.php';

// Check if login is required for this page
if (isset($require_login) && $require_login) {
    requireLogin();
}

// Get current user
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Comprehensive food nutrition analysis tool for comparing food nutrients with standard health recommendations">
    <meta name="keywords"
        content="food nutrition, nutrition analyzer, food comparison, health recommendations, nutritional values">
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
                        <p class="app-subtitle">
                            <?php echo $header_subtitle ?? 'Compare food nutrients with standard health recommendations'; ?>
                        </p>
                    </div>
                </h1>

                <!-- User Info & Actions -->
                <?php if (isset($show_user_link) && $show_user_link && $currentUser): ?>
                    <div class="header-user-info">
                        <a href="profile.php" class="user-profile-link">
                            <div class="user-profile">
                                <i class="fas fa-user-circle"></i>
                                <div class="user-details">
                                    <span class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                                    <span class="user-nim">NIM: <?php echo htmlspecialchars($currentUser['nim']); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="header-nav-row">
                <?php if (isset($show_back_link) && $show_back_link): ?>
                    <div class="header-nav">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-arrow-left"></i>
                            Back to Search
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Active Users Display -->
                <?php if (isset($show_active_users_link) && $show_active_users_link && $currentUser): ?>
                    <div class="header-nav active-users-section">
                        <div class="active-users-widget">
                            <i class="fas fa-users"></i>
                            <span id="activeUsersCount">...</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Active Users Modal -->
    <?php if ($currentUser): ?>
        <div id="activeUsersModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-users"></i>
                        Active Users
                    </h3>
                    <button class="modal-close" id="closeActiveUsersModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="activeUsersList">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Real-time active users tracking
            let activeUsersInterval = null;

            function fetchActiveUsers() {
                $.ajax({
                    url: 'api/active_users.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            const data = response.data;
                            $('#activeUsersCount').text(data.count);

                            // Update modal content if open
                            if ($('#activeUsersModal').is(':visible')) {
                                updateActiveUsersList(data.users);
                            }
                        }
                    },
                    error: function () {
                        $('#activeUsersCount').text('?');
                    }
                });
            }

            function updateActiveUsersList(users) {
                if (users.length === 0) {
                    $('#activeUsersList').html(`
                    <div class="no-active-users">
                        <i class="fas fa-user-slash"></i>
                        <p>No other users online</p>
                    </div>
                `);
                    return;
                }

                let html = '<div class="active-users-list">';
                users.forEach(function (user) {
                    const idleMinutes = Math.floor(user.idle_seconds / 60);
                    const idleText = idleMinutes === 0 ? 'Just now' : `${idleMinutes}m ago`;
                    const statusClass = user.idle_seconds < 60 ? 'status-active' : 'status-idle';

                    html += `
                    <div class="active-user-item ${statusClass}">
                        <div class="user-status-indicator"></div>
                        <div class="user-info">
                            <div class="user-name-nim">
                                <strong>${escapeHtml(user.name)}</strong>
                                <span class="user-nim-badge">NIM: ${escapeHtml(user.nim)}</span>
                            </div>
                            <div class="user-activity">
                                <i class="fas fa-clock"></i>
                                Last activity: ${idleText}
                            </div>
                        </div>
                    </div>
                `;
                });
                html += '</div>';

                $('#activeUsersList').html(html);
            }

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

            $(document).ready(function () {
                // Initial fetch
                fetchActiveUsers();

                // Refresh every 10 seconds
                activeUsersInterval = setInterval(fetchActiveUsers, 10000);

                // Show active users modal
                $('#showActiveUsers').on('click', function (e) {
                    e.preventDefault();
                    $('#activeUsersModal').fadeIn(200);
                    fetchActiveUsers(); // Fetch fresh data when opening
                });

                // Close modal
                $('#closeActiveUsersModal, #activeUsersModal').on('click', function (e) {
                    if (e.target === this) {
                        $('#activeUsersModal').fadeOut(200);
                    }
                });

                // Prevent modal content clicks from closing
                $('.modal-content').on('click', function (e) {
                    e.stopPropagation();
                });
            });
        </script>
    <?php endif; ?>