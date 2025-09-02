<?php
$page_title = "Booking Approvals";
include_once 'includes/header.php';

include_once '../classes/Booking.php';
include_once '../classes/Customer.php';
include_once '../classes/Tour.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_POST) {
    try {
        $booking = new Booking($db);

        if (isset($_POST['approve_booking'])) {
            $booking_id = (int)$_POST['booking_id'];
            $admin_notes = $_POST['admin_notes'] ?? '';

            if ($booking->updateStatus($booking_id, 'Confirmed', $admin_notes)) {
                $message = 'Booking approved successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to approve booking.';
                $message_type = 'danger';
            }
        } elseif (isset($_POST['reject_booking'])) {
            $booking_id = (int)$_POST['booking_id'];
            $admin_notes = $_POST['admin_notes'] ?? '';

            if ($booking->updateStatus($booking_id, 'Cancelled', $admin_notes)) {
                $message = 'Booking rejected successfully!';
                $message_type = 'success';
            } else {
                $message = 'Failed to reject booking.';
                $message_type = 'danger';
            }
        }
    } catch (Exception $e) {
        $message = 'System error: ' . $e->getMessage();
        $message_type = 'danger';
        error_log("Booking approval error: " . $e->getMessage());
    }
}

// Get pending bookings
try {
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? 'Pending';

    $query = "SELECT b.*, c.full_name, c.email, c.phone, t.tour_name, t.departure_date, t.return_date 
              FROM bookings b 
              LEFT JOIN customers c ON b.customer_id = c.id 
              LEFT JOIN tours t ON b.tour_id = t.id 
              WHERE 1=1";

    $params = [];

    if ($status_filter && $status_filter !== 'All') {
        $query .= " AND b.booking_status = ?";
        $params[] = $status_filter;
    }

    if ($search) {
        $query .= " AND (c.full_name LIKE ? OR b.booking_reference LIKE ? OR t.tour_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $query .= " ORDER BY b.booking_date DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stats_query = "SELECT 
        COUNT(CASE WHEN booking_status = 'Pending' THEN 1 END) as pending,
        COUNT(CASE WHEN booking_status = 'Confirmed' THEN 1 END) as confirmed,
        COUNT(CASE WHEN booking_status = 'Cancelled' THEN 1 END) as cancelled,
        COUNT(*) as total
        FROM bookings";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading bookings: ' . $e->getMessage();
    $message_type = 'danger';
    $bookings = [];
    $stats = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'total' => 0];
    error_log("Booking approvals query error: " . $e->getMessage());
}
?>

<div class="page-header">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-clipboard-check me-2 text-primary"></i>
                <?php echo $page_title; ?>
            </h1>
            <p class="text-muted mb-0">Review and process customer booking requests</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="bookings.php" class="btn btn-outline-primary">
                    <i class="fas fa-list me-1"></i>All Bookings
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['pending']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Confirmed</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['confirmed']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-danger text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Cancelled</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['cancelled']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Customer name, booking ref, or tour...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="All" <?php echo $status_filter === 'All' ? 'selected' : ''; ?>>All Status</option>
                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="booking-approvals.php" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bookings List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Booking Requests
            <span class="badge bg-secondary"><?php echo count($bookings); ?></span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No bookings found</h5>
                <p class="text-muted">No bookings match your current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Booking Details</th>
                            <th>Customer</th>
                            <th>Tour Information</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Payment Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $booking['number_of_passengers']; ?>
                                            <?php echo $booking['number_of_passengers'] == 1 ? 'person' : 'people'; ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i>
                                            <?php echo htmlspecialchars($booking['email']); ?>
                                        </small>
                                        <?php if ($booking['phone']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>
                                                <?php echo htmlspecialchars($booking['phone']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($booking['tour_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($booking['departure_date'])); ?>
                                            <?php if ($booking['return_date']): ?>
                                                - <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo number_format($booking['total_amount'], 2); ?> MMK
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        Payment:
                                        <span class="badge bg-<?php echo $booking['payment_status'] === 'Paid' ? 'success' : 'warning'; ?>">
                                            <?php echo $booking['payment_status']; ?>
                                        </span>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                                            echo $booking['booking_status'] === 'Confirmed' ? 'success' : ($booking['booking_status'] === 'Pending' ? 'warning' : 'danger');
                                                            ?>">
                                        <?php echo $booking['booking_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                        <br>
                                        <?php echo date('H:i', strtotime($booking['booking_date'])); ?>
                                    </small>
                                </td>
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
                                    <?php if ($booking['booking_status'] === 'Pending'): ?>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <button class="btn btn-success btn-sm"
                                                onclick="showApprovalModal(<?php echo $booking['id']; ?>, 'approve', '<?php echo htmlspecialchars($booking['full_name']); ?>')">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="showApprovalModal(<?php echo $booking['id']; ?>, 'reject', '<?php echo htmlspecialchars($booking['full_name']); ?>')">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-check-circle me-1"></i>Processed
                                        </span>
                                    <?php endif; ?>
                                    <br>
                                    <a href="booking.php?id=<?php echo $booking['id']; ?>"
                                        class="btn btn-outline-info btn-sm mt-1">
                                        <i class="fas fa-eye me-1"></i>Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="approvalForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="modalBookingId">
                    <p id="approvalMessage"></p>
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3"
                            placeholder="Add any notes about this decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="confirmButton">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

<script>
    function showApprovalModal(bookingId, action, customerName) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const form = document.getElementById('approvalForm');
        const title = document.getElementById('approvalModalTitle');
        const message = document.getElementById('approvalMessage');
        const confirmBtn = document.getElementById('confirmButton');
        const bookingIdInput = document.getElementById('modalBookingId');

        bookingIdInput.value = bookingId;

        if (action === 'approve') {
            title.textContent = 'Approve Booking';
            message.innerHTML = `Are you sure you want to <strong>approve</strong> the booking for <strong>${customerName}</strong>?`;
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve Booking';
            confirmBtn.name = 'approve_booking';
        } else {
            title.textContent = 'Reject Booking';
            message.innerHTML = `Are you sure you want to <strong>reject</strong> the booking for <strong>${customerName}</strong>?`;
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i>Reject Booking';
            confirmBtn.name = 'reject_booking';
        }

        modal.show();
    }

    // Form submission handling
    document.getElementById('approvalForm').addEventListener('submit', function() {
        const confirmBtn = document.getElementById('confirmButton');
        const originalText = confirmBtn.innerHTML;

        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        confirmBtn.disabled = true;

        // Re-enable after a delay if something goes wrong
        setTimeout(() => {
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }, 10000);
    });

    // Auto-refresh every 60 seconds for new bookings
    setInterval(function() {
        if (!document.querySelector('.modal.show')) {
            window.location.reload();
        }
    }, 60000);
</script>

</body>

</html>