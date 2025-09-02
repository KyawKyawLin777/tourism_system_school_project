<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        $message = "<div class='alert alert-success'>Booking deleted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Failed to delete booking.</div>";
    }
}

// Handle search and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                        <h5><?php echo $_SESSION['admin_name']; ?></h5>
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
                            <a class="nav-link text-white active" href="bookings.php">
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
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Bookings Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-primary fs-6"><?php echo $total_records; ?> Total</span>
                        </div>
                        <div class="btn-group">
                            <a href="booking-approvals.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-clock me-1"></i><?php echo $stats['pending_bookings']; ?> Pending
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stats-card bg-primary text-white">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Bookings</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['total_bookings']; ?></div>
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
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['pending_bookings']; ?></div>
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
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['confirmed_bookings']; ?></div>
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
                            <input type="text" class="form-control" id="search" name="search" placeholder="Reference, customer, tour..." value="<?php echo htmlspecialchars($search); ?>">
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
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!empty($search) || !empty($status_filter) || !empty($payment_filter) || !empty($date_from) || !empty($date_to)) { ?>
                    <div class="mt-3">
                        <a href="bookings.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                    <?php } ?>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking Ref</th>
                                        <th>Customer</th>
                                        <th>Tour</th>
                                        <th>Passengers</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Booking Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr class="booking-row">
                                        <td>
                                            <strong><?php echo $booking['booking_reference']; ?></strong>
                                            <?php if($booking['processed_by_name']) { ?>
                                            <br><small class="text-muted">by <?php echo $booking['processed_by_name']; ?></small>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <strong><?php echo $booking['full_name']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $booking['email']; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo $booking['phone']; ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $booking['tour_name']; ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i><?php echo $booking['location_name']; ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d', strtotime($booking['departure_date'])); ?> - 
                                                <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo $booking['number_of_passengers']; ?></span>
                                        </td>
                                        <td class="text-success fw-bold">
                                            <?php echo number_format($booking['total_amount'], 2); ?> MMK
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['booking_status'] == 'Confirmed' ? 'success' : 
                                                    ($booking['booking_status'] == 'Pending' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo $booking['booking_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['payment_status'] == 'Paid' ? 'success' : 
                                                    ($booking['payment_status'] == 'Pending' ? 'secondary' : 'info'); 
                                            ?>">
                                                <?php echo $booking['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($booking['booking_date'])); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical">
                                                <button class="btn btn-outline-info btn-sm" onclick="viewBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($booking['booking_status'] == 'Pending') { ?>
                                                <a href="booking-approvals.php" class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php } ?>
                                                <a href="?delete_id=<?php echo $booking['id']; ?>" 
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this booking?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1) { ?>
                <nav aria-label="Bookings pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) : ''; ?>">Previous</a>
                        </li>
                        <?php } ?>
                        
                        <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++) { ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php } ?>
                        
                        <?php if($page < $total_pages) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) ? '&' . http_build_query(array_filter($_GET, function($key) { return $key != 'page'; }, ARRAY_FILTER_USE_KEY)) : ''; ?>">Next</a>
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
                    <h5 class="modal-title">Booking Details</h5>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBookingDetails(bookingData) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>Booking Information</h5>
                        <table class="table table-borderless">
                            <tr><td><strong>Reference:</strong></td><td>${bookingData.booking_reference}</td></tr>
                            <tr><td><strong>Tour:</strong></td><td>${bookingData.tour_name}</td></tr>
                            <tr><td><strong>Package:</strong></td><td>${bookingData.package_name} (${bookingData.package_type})</td></tr>
                            <tr><td><strong>Location:</strong></td><td>${bookingData.location_name}</td></tr>
                            <tr><td><strong>Passengers:</strong></td><td>${bookingData.number_of_passengers}</td></tr>
                            <tr><td><strong>Total Amount:</strong></td><td class="text-success fw-bold">$${parseFloat(bookingData.total_amount).toFixed(2)}</td></tr>
                            <tr><td><strong>Booking Date:</strong></td><td>${new Date(bookingData.booking_date).toLocaleString()}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
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
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>Tour Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Departure:</strong> ${bookingData.departure_date ? new Date(bookingData.departure_date).toLocaleDateString() : 'N/A'}</p>
                                <p><strong>Return:</strong> ${bookingData.return_date ? new Date(bookingData.return_date).toLocaleDateString() : 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Package Type:</strong> ${bookingData.package_type}</p>
                                <p><strong>Location:</strong> ${bookingData.location_name}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ${bookingData.admin_notes ? `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>Admin Notes</h5>
                        <div class="alert alert-info">${bookingData.admin_notes}</div>
                    </div>
                </div>
                ` : ''}
            `;
            
            document.getElementById('bookingDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('bookingDetailsModal')).show();
        }
    </script>
</body>
</html>
