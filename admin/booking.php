<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check authentication
session_start();
include_once 'auth.php';
checkAdminLogin();

// Include required files
try {
    include_once '../config/database.php';
    include_once '../classes/Booking.php';
    include_once '../classes/Customer.php';
    include_once '../classes/Tour.php';
} catch (Exception $e) {
    die("Error loading required files: " . $e->getMessage());
}

// Initialize database connection
try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$message = '';
$error_message = '';

// Handle form submissions
if ($_POST) {
    try {
        if (isset($_POST['action'])) {
            $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
            $action = $_POST['action'];
            $admin_id = $_SESSION['admin_id'];
            $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';

            if ($booking_id <= 0) {
                throw new Exception("Invalid booking ID");
            }

            switch ($action) {
                case 'confirm':
                    $query = "UPDATE bookings SET booking_status = 'Confirmed', processed_by = ?, processed_at = NOW(), admin_notes = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    if ($stmt->execute([$admin_id, $admin_notes, $booking_id])) {
                        $message = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Booking confirmed successfully!</div>';
                    } else {
                        throw new Exception("Failed to confirm booking");
                    }
                    break;

                case 'cancel':
                    // Start transaction
                    $db->beginTransaction();

                    try {
                        // Update booking status
                        $query = "UPDATE bookings SET booking_status = 'Cancelled', processed_by = ?, processed_at = NOW(), admin_notes = ? WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$admin_id, $admin_notes, $booking_id]);

                        // Return seats to tour
                        $seat_query = "UPDATE tours t 
                                       JOIN bookings b ON t.id = b.tour_id 
                                       SET t.available_seats = t.available_seats + b.number_of_passengers 
                                       WHERE b.id = ?";
                        $seat_stmt = $db->prepare($seat_query);
                        $seat_stmt->execute([$booking_id]);

                        $db->commit();
                        $message = '<div class="alert alert-warning"><i class="fas fa-times-circle me-2"></i>Booking cancelled and seats returned.</div>';
                    } catch (Exception $e) {
                        $db->rollback();
                        throw $e;
                    }
                    break;

                case 'mark_paid':
                    $query = "UPDATE bookings SET payment_status = 'Paid', processed_by = ?, processed_at = NOW(), admin_notes = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    if ($stmt->execute([$admin_id, $admin_notes, $booking_id])) {
                        $message = '<div class="alert alert-success"><i class="fas fa-dollar-sign me-2"></i>Payment status updated to Paid!</div>';
                    } else {
                        throw new Exception("Failed to update payment status");
                    }
                    break;

                default:
                    throw new Exception("Invalid action specified");
            }
        }
    } catch (Exception $e) {
        $error_message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        error_log("Admin Booking Error: " . $e->getMessage());
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(b.booking_reference LIKE ? OR c.full_name LIKE ? OR c.email LIKE ? OR t.tour_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
    $where_conditions[] = "b.booking_status = ?";
    $params[] = $status_filter;
}

if (!empty($payment_filter)) {
    $where_conditions[] = "b.payment_status = ?";
    $params[] = $payment_filter;
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(b.booking_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(b.booking_date) <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM bookings b
                    LEFT JOIN customers c ON b.customer_id = c.id
                    LEFT JOIN tours t ON b.tour_id = t.id
                    WHERE $where_clause";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get bookings with pagination
    $query = "SELECT b.*, c.full_name, c.email, c.phone, 
                     t.tour_name, t.departure_date, t.return_date,
                     p.package_name, p.package_type, l.name as location_name,
                     a.full_name as processed_by_name
              FROM bookings b
              LEFT JOIN customers c ON b.customer_id = c.id
              LEFT JOIN tours t ON b.tour_id = t.id
              LEFT JOIN packages p ON t.package_id = p.id
              LEFT JOIN locations l ON p.location_id = l.id
              LEFT JOIN admins a ON b.processed_by = a.id
              WHERE $where_clause
              ORDER BY b.booking_date DESC
              LIMIT $records_per_page OFFSET $offset";

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // Get statistics
    $stats_query = "SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN booking_status = 'Pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN booking_status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as total_revenue,
        SUM(CASE WHEN payment_status = 'Pending' THEN total_amount ELSE 0 END) as pending_revenue
        FROM bookings";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = '<div class="alert alert-danger">Error loading booking data: ' . htmlspecialchars($e->getMessage()) . '</div>';
    error_log("Booking Query Error: " . $e->getMessage());

    // Set default values to prevent further errors
    $total_records = 0;
    $total_pages = 0;
    $stats = [
        'total_bookings' => 0,
        'pending_bookings' => 0,
        'confirmed_bookings' => 0,
        'cancelled_bookings' => 0,
        'total_revenue' => 0,
        'pending_revenue' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .booking-row {
            transition: background-color 0.3s;
        }

        .booking-row:hover {
            background-color: #f8f9fa;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <i class="fas fa-user-shield fa-2x mb-2"></i>
                        <h5><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></h5>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
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
                            <a class="nav-link text-white active" href="booking.php">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="booking-approvals.php">
                                <i class="fas fa-clipboard-check me-2"></i>Booking Approvals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="admins.php">
                                <i class="fas fa-user-cog me-2"></i>Admins
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="login.php?logout=1">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-calendar-check me-2"></i>Bookings Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-primary fs-6"><?php echo number_format($total_records); ?> Total</span>
                        </div>
                        <div class="btn-group">
                            <a href="booking-approvals.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-clock me-1"></i><?php echo $stats['pending_bookings']; ?> Pending
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Display Messages -->
                <?php
                if (!empty($message)) echo $message;
                if (!empty($error_message)) echo $error_message;
                ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['total_bookings']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card bg-warning text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['pending_bookings']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card bg-success text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Confirmed</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo number_format($stats['confirmed_bookings']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card bg-info text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                                        <div class="h5 mb-0 font-weight-bold">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Reference, customer, tour..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo $status_filter == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="payment" class="form-label">Payment</label>
                            <select class="form-select" id="payment" name="payment">
                                <option value="">All Payment</option>
                                <option value="Pending" <?php echo $payment_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Paid" <?php echo $payment_filter == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="Refunded" <?php echo $payment_filter == 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <?php if (!empty($search) || !empty($status_filter) || !empty($payment_filter) || !empty($date_from) || !empty($date_to)) { ?>
                        <div class="mt-3">
                            <a href="booking.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>All Bookings
                            <span class="badge bg-secondary ms-2"><?php echo number_format($total_records); ?> records</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($stmt) && $stmt->rowCount() > 0) { ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Customer</th>
                                            <th>Tour</th>
                                            <th>Passengers</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Booking Date</th>
                                            <th>Payment Method</th>
                                            <th>Payment Image</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <tr class="booking-row">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong>
                                                    <?php if (!empty($booking['processed_by_name'])) { ?>
                                                        <br><small class="text-muted">by <?php echo htmlspecialchars($booking['processed_by_name']); ?></small>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['tour_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($booking['location_name']); ?>
                                                    </small>
                                                    <?php if (!empty($booking['departure_date'])) { ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?php echo date('M d', strtotime($booking['departure_date'])); ?> -
                                                            <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                                                        </small>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info"><?php echo $booking['number_of_passengers']; ?></span>
                                                </td>
                                                <td class="text-success fw-bold">
                                                    $<?php echo number_format($booking['total_amount'], 2); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                                            echo $booking['booking_status'] == 'Confirmed' ? 'success' : ($booking['booking_status'] == 'Pending' ? 'warning' : 'danger');
                                                                            ?>">
                                                        <?php echo htmlspecialchars($booking['booking_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                                            echo $booking['payment_status'] == 'Paid' ? 'success' : ($booking['payment_status'] == 'Pending' ? 'secondary' : 'info');
                                                                            ?>">
                                                        <?php echo htmlspecialchars($booking['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo date('H:i', strtotime($booking['booking_date'])); ?></small>
                                                </td>
                                                <td> <?php echo htmlspecialchars($booking['payment_method']); ?></td>
                                                <td>
                                                    <?php if (!empty($booking['payment_image'])): ?>
                                                        <a href="../<?php echo htmlspecialchars($booking['payment_image']); ?>" target="_blank">
                                                            <img src="../<?php echo htmlspecialchars($booking['payment_image']); ?>"
                                                                class="card-img-top img-fluid"
                                                                alt="Payment Proof"
                                                                style="max-height: 100px; object-fit: cover;">
                                                        </a>

                                                    <?php else: ?>
                                                        <span class="text-muted">No proof</span>
                                                    <?php endif; ?>
                                                </td>


                                                <td>
                                                    <div class="btn-group-vertical">
                                                        <button class="btn btn-outline-info btn-sm" onclick="viewBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($booking['booking_status'] == 'Pending') { ?>
                                                            <div class="btn-group">
                                                                <button class="btn btn-success btn-sm" onclick="processBooking(<?php echo $booking['id']; ?>, 'confirm', '<?php echo htmlspecialchars($booking['booking_reference']); ?>')">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" onclick="processBooking(<?php echo $booking['id']; ?>, 'cancel', '<?php echo htmlspecialchars($booking['booking_reference']); ?>')">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        <?php } ?>
                                                        <?php if ($booking['payment_status'] == 'Pending') { ?>
                                                            <button class="btn btn-warning btn-sm" onclick="markPaid(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_reference']); ?>')">
                                                                <i class="fas fa-dollar-sign"></i>
                                                            </button>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h4>No Bookings Found</h4>
                                <p class="text-muted">No bookings match your current filters.</p>
                                <?php if (!empty($search) || !empty($status_filter) || !empty($payment_filter)) { ?>
                                    <a href="booking.php" class="btn btn-primary">
                                        <i class="fas fa-refresh me-1"></i>View All Bookings
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <nav aria-label="Bookings pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . http_build_query(array_filter($_GET, function ($key) {
                                                                                                    return $key != 'page';
                                                                                                }, ARRAY_FILTER_USE_KEY)) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php } ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) { ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . http_build_query(array_filter($_GET, function ($key) {
                                                                                            return $key != 'page';
                                                                                        }, ARRAY_FILTER_USE_KEY)) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <?php if ($page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . http_build_query(array_filter($_GET, function ($key) {
                                                                                                    return $key != 'page';
                                                                                                }, ARRAY_FILTER_USE_KEY)) : ''; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            </main>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Booking Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetailsContent">
                    <!-- Content populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Booking Modal -->
    <div class="modal fade" id="processBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalTitle">Process Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="booking_id" id="processBookingId">
                        <input type="hidden" name="action" id="processAction">

                        <div id="processModalBody">
                            <!-- Content populated by JavaScript -->
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Add any notes about this booking..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="processSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBookingDetails(bookingData) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-ticket-alt me-2"></i>Booking Information</h5>
                        <table class="table table-borderless">
                            <tr><td><strong>Reference:</strong></td><td>${bookingData.booking_reference}</td></tr>
                            <tr><td><strong>Tour:</strong></td><td>${bookingData.tour_name}</td></tr>
                            <tr><td><strong>Package:</strong></td><td>${bookingData.package_name || 'N/A'} ${bookingData.package_type ? '(' + bookingData.package_type + ')' : ''}</td></tr>
                            <tr><td><strong>Location:</strong></td><td>${bookingData.location_name}</td></tr>
                            <tr><td><strong>Passengers:</strong></td><td>${bookingData.number_of_passengers}</td></tr>
                            <tr><td><strong>Total Amount:</strong></td><td class="text-success fw-bold">$${parseFloat(bookingData.total_amount).toFixed(2)}</td></tr>
                            <tr><td><strong>Booking Date:</strong></td><td>${new Date(bookingData.booking_date).toLocaleString()}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
                        <table class="table table-borderless">
                            <tr><td><strong>Name:</strong></td><td>${bookingData.full_name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${bookingData.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${bookingData.phone}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>
                                <span class="badge bg-${bookingData.booking_status === 'Confirmed' ? 'success' : (bookingData.booking_status === 'Pending' ? 'warning' : 'danger')}">${bookingData.booking_status}</span>
                            </td></tr>
                            <tr><td><strong>Payment:</strong></td><td>
                                <span class="badge bg-${bookingData.payment_status === 'Paid' ? 'success' : 'secondary'}">${bookingData.payment_status}</span>
                            </td></tr>
                            ${bookingData.processed_by_name ? `<tr><td><strong>Processed By:</strong></td><td>${bookingData.processed_by_name}</td></tr>` : ''}
                        </table>
                    </div>
                </div>
                ${bookingData.departure_date ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-calendar me-2"></i>Tour Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Departure:</strong> ${new Date(bookingData.departure_date).toLocaleDateString()}</p>
                                <p><strong>Return:</strong> ${bookingData.return_date ? new Date(bookingData.return_date).toLocaleDateString() : 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Package Type:</strong> ${bookingData.package_type || 'N/A'}</p>
                                <p><strong>Location:</strong> ${bookingData.location_name}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
                ${bookingData.admin_notes ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5><i class="fas fa-sticky-note me-2"></i>Admin Notes</h5>
                        <div class="alert alert-info">${bookingData.admin_notes}</div>
                    </div>
                </div>
                ` : ''}
            `;

            document.getElementById('bookingDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('bookingDetailsModal')).show();
        }

        function processBooking(id, action, reference) {
            const isConfirm = action === 'confirm';
            const isCancel = action === 'cancel';

            document.getElementById('processModalTitle').textContent = isConfirm ? 'Confirm Booking' : 'Cancel Booking';
            document.getElementById('processModalBody').innerHTML = `
                <div class="alert alert-${isConfirm ? 'success' : 'danger'}">
                    <i class="fas fa-${isConfirm ? 'check' : 'times'}-circle me-2"></i>
                    Are you sure you want to ${action} booking "<strong>${reference}</strong>"?
                </div>
                <p>${isConfirm ? 'This will confirm the booking and notify the customer.' : 'This will cancel the booking and return the seats to available inventory.'}</p>
            `;

            document.getElementById('processBookingId').value = id;
            document.getElementById('processAction').value = action;
            document.getElementById('processSubmitBtn').className = `btn btn-${isConfirm ? 'success' : 'danger'}`;
            document.getElementById('processSubmitBtn').textContent = isConfirm ? 'Confirm Booking' : 'Cancel Booking';

            new bootstrap.Modal(document.getElementById('processBookingModal')).show();
        }

        function markPaid(id, reference) {
            document.getElementById('processModalTitle').textContent = 'Mark as Paid';
            document.getElementById('processModalBody').innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-dollar-sign me-2"></i>
                    Mark booking "<strong>${reference}</strong>" as paid?
                </div>
                <p>This will update the payment status to "Paid" for this booking.</p>
            `;

            document.getElementById('processBookingId').value = id;
            document.getElementById('processAction').value = 'mark_paid';
            document.getElementById('processSubmitBtn').className = 'btn btn-info';
            document.getElementById('processSubmitBtn').textContent = 'Mark as Paid';

            new bootstrap.Modal(document.getElementById('processBookingModal')).show();
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>