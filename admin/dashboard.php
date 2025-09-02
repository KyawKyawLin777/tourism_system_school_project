<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Tour.php';
include_once '../classes/Customer.php';
include_once '../classes/Booking.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM tours WHERE status = 'Active') as active_tours,
    (SELECT COUNT(*) FROM customers) as total_customers,
    (SELECT COUNT(*) FROM bookings WHERE booking_status = 'Confirmed') as confirmed_bookings,
    (SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'Paid') as total_revenue";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Recent bookings
$recent_bookings_query = "SELECT b.*, c.full_name, t.tour_name 
                         FROM bookings b 
                         LEFT JOIN customers c ON b.customer_id = c.id 
                         LEFT JOIN tours t ON b.tour_id = t.id 
                         ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings_stmt = $db->prepare($recent_bookings_query);
$recent_bookings_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Myanmar Tourism</title>
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
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        main {
            flex: 1;
        }
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
                        <h5><?php echo $_SESSION['admin_name']; ?></h5>
                        <small><?php echo $_SESSION['admin_role']; ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="locations.php">
                                <i class="fas fa-map-marker-alt me-2"></i>Locations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="packages.php">
                                <i class="fas fa-box me-2"></i>Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="package-approvals.php">
                                <i class="fas fa-check-circle me-2"></i>Package Approvals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="bus-types.php">
                                <i class="fas fa-bus me-2"></i>Bus Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="tours.php">
                                <i class="fas fa-route me-2"></i>Tours
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="customers.php">
                                <i class="fas fa-users me-2"></i>Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="booking-approvals.php">
                                <i class="fas fa-clipboard-check me-2"></i>Booking Approvals
                                <?php 
                                $pending_query = "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'";
                                $pending_stmt = $db->prepare($pending_query);
                                $pending_stmt->execute();
                                $pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                if($pending_count > 0) {
                                    echo '<span class="badge bg-danger float-end">' . $pending_count . '</span>';
                                }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admins.php">
                                <i class="fas fa-user-cog me-2"></i>Admins
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="?logout=1">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Active Tours</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['active_tours']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-route fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Customers</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['total_customers']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Confirmed Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['confirmed_bookings']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                                        <div class="h5 mb-0 font-weight-bold">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking Ref</th>
                                        <th>Customer</th>
                                        <th>Tour</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $recent_bookings_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <td><?php echo $booking['booking_reference']; ?></td>
                                        <td><?php echo $booking['full_name']; ?></td>
                                        <td><?php echo $booking['tour_name']; ?></td>
                                        <td><?php echo number_format($booking['total_amount'], 2); ?> MMK</td>
                                        <td>
                                            <span class="badge bg-<?php echo $booking['booking_status'] == 'Confirmed' ? 'success' : ($booking['booking_status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo $booking['booking_status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Actions Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Pending Actions</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get pending counts
                                $pending_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'";
                                $pending_bookings_stmt = $db->prepare($pending_bookings_query);
                                $pending_bookings_stmt->execute();
                                $pending_bookings = $pending_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                
                                $pending_packages_query = "SELECT COUNT(*) as count FROM packages WHERE approval_status = 'Pending'";
                                $pending_packages_stmt = $db->prepare($pending_packages_query);
                                $pending_packages_stmt->execute();
                                $pending_packages = $pending_packages_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                
                                $unpaid_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'Pending' AND booking_status = 'Confirmed'";
                                $unpaid_bookings_stmt = $db->prepare($unpaid_bookings_query);
                                $unpaid_bookings_stmt->execute();
                                $unpaid_bookings = $unpaid_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                ?>
                                
                                <div class="list-group">
                                    <?php if($pending_bookings > 0): ?>
                                    <a href="booking-approvals.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-calendar-check me-2 text-warning"></i>
                                            Pending Bookings
                                        </div>
                                        <span class="badge bg-warning rounded-pill"><?php echo $pending_bookings; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($pending_packages > 0): ?>
                                    <a href="package-approvals.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-box me-2 text-primary"></i>
                                            Pending Packages
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?php echo $pending_packages; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($unpaid_bookings > 0): ?>
                                    <a href="bookings.php?payment=Pending&status=Confirmed" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-dollar-sign me-2 text-success"></i>
                                            Unpaid Confirmed Bookings
                                        </div>
                                        <span class="badge bg-success rounded-pill"><?php echo $unpaid_bookings; ?></span>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($pending_bookings == 0 && $pending_packages == 0 && $unpaid_bookings == 0): ?>
                                    <div class="list-group-item text-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        No pending actions at this time
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get today's stats
                                $today = date('Y-m-d');
                                $today_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = ?";
                                $today_bookings_stmt = $db->prepare($today_bookings_query);
                                $today_bookings_stmt->execute([$today]);
                                $today_bookings = $today_bookings_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                
                                $today_revenue_query = "SELECT SUM(total_amount) as total FROM bookings WHERE DATE(booking_date) = ? AND payment_status = 'Paid'";
                                $today_revenue_stmt = $db->prepare($today_revenue_query);
                                $today_revenue_stmt->execute([$today]);
                                $today_revenue = $today_revenue_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                                
                                // Get upcoming tours
                                $upcoming_tours_query = "SELECT COUNT(*) as count FROM tours WHERE departure_date > NOW() AND status = 'Active'";
                                $upcoming_tours_stmt = $db->prepare($upcoming_tours_query);
                                $upcoming_tours_stmt->execute();
                                $upcoming_tours = $upcoming_tours_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                ?>
                                
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0"><?php echo $today_bookings; ?></h3>
                                                <small class="text-muted">Today's Bookings</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0">$<?php echo number_format($today_revenue, 2); ?></h3>
                                                <small class="text-muted">Today's Revenue</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0"><?php echo $upcoming_tours; ?></h3>
                                                <small class="text-muted">Upcoming Tours</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0"><?php echo $stats['total_customers']; ?></h3>
                                                <small class="text-muted">Total Customers</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php
    if(isset($_GET['logout'])) {
        logout();
    }
    
    // Include the footer with booking notifications
    include_once 'includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
