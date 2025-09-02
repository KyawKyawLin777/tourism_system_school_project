<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if(!function_exists('checkAdminLogin')) {
    include_once 'auth.php';
    checkAdminLogin();
}

// Include database connection
if(!isset($db)) {
    include_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'Administrator';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Handle logout
if(isset($_GET['logout'])) {
    include_once 'auth.php';
    logout();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }
        main {
            flex: 1;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .page-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        <?php echo $additional_css ?? ''; ?>
    </style>
</head>
<body class="d-flex flex-column h-100">
    <div class="container-fluid flex-grow-1">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <i class="fas fa-user-shield fa-2x mb-2"></i>
                        <h5><?php echo htmlspecialchars($admin_name); ?></h5>
                        <small><?php echo htmlspecialchars($admin_role); ?></small>
                        <hr class="text-white-50">
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'locations.php' ? 'active' : ''; ?>" href="locations.php">
                                <i class="fas fa-map-marker-alt me-2"></i>Locations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                                <i class="fas fa-box me-2"></i>Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'package-approvals.php' ? 'active' : ''; ?>" href="package-approvals.php">
                                <i class="fas fa-check-circle me-2"></i>Package Approvals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'bus-types.php' ? 'active' : ''; ?>" href="bus-types.php">
                                <i class="fas fa-bus me-2"></i>Bus Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'tours.php' ? 'active' : ''; ?>" href="tours.php">
                                <i class="fas fa-route me-2"></i>Tours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                                <i class="fas fa-users me-2"></i>Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'booking-approvals.php' ? 'active' : ''; ?>" href="booking-approvals.php">
                                <i class="fas fa-clipboard-check me-2"></i>Booking Approvals
                                <?php 
                                try {
                                    $pending_query = "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'";
                                    $pending_stmt = $db->prepare($pending_query);
                                    $pending_stmt->execute();
                                    $pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                    if($pending_count > 0) {
                                        echo '<span class="badge bg-danger float-end">' . $pending_count . '</span>';
                                    }
                                } catch (Exception $e) {
                                    error_log("Sidebar pending count error: " . $e->getMessage());
                                }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white <?php echo $current_page == 'admins.php' ? 'active' : ''; ?>" href="admins.php">
                                <i class="fas fa-user-cog me-2"></i>Admins
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="?logout=1" onclick="return confirm('Are you sure you want to logout?')">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Session Info -->
                    <div class="mt-4 p-3 text-white-50 small">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock me-2"></i>
                            <span>Session: <?php echo date('H:i', $_SESSION['login_time'] ?? time()); ?></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-server me-2"></i>
                            <span class="text-success">Online</span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Toast container for notifications -->
                <div class="toast-container"></div>
