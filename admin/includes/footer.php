<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    return;
}

// Handle AJAX requests for booking actions
if (isset($_POST['action']) && isset($_POST['booking_id'])) {
    header('Content-Type: application/json');
    
    try {
        if (!isset($db)) {
            include_once '../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
        }
        
        include_once '../classes/Booking.php';
        
        $booking = new Booking($db);
        $booking_id = (int)$_POST['booking_id'];
        $action = $_POST['action'];
        $admin_notes = $_POST['admin_notes'] ?? '';
        
        if ($action === 'approve') {
            $result = $booking->updateStatus($booking_id, 'Confirmed', $admin_notes);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Booking approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve booking']);
            }
        } elseif ($action === 'reject') {
            $result = $booking->updateStatus($booking_id, 'Cancelled', $admin_notes);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Booking rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject booking']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Footer booking action error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error occurred']);
    }
    exit;
}

// Get pending bookings for footer notification
try {
    if (!isset($db)) {
        include_once '../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
    }
    
    $pending_bookings_query = "SELECT b.*, c.full_name, c.email, t.tour_name, t.departure_date 
                              FROM bookings b 
                              LEFT JOIN customers c ON b.customer_id = c.id 
                              LEFT JOIN tours t ON b.tour_id = t.id 
                              WHERE b.booking_status = 'Pending' 
                              ORDER BY b.booking_date DESC 
                              LIMIT 5";
    $pending_bookings_stmt = $db->prepare($pending_bookings_query);
    $pending_bookings_stmt->execute();
    $pending_bookings = $pending_bookings_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pending_query = "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'";
    $total_pending_stmt = $db->prepare($total_pending_query);
    $total_pending_stmt->execute();
    $total_pending = $total_pending_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (Exception $e) {
    error_log("Footer query error: " . $e->getMessage());
    $pending_bookings = [];
    $total_pending = 0;
}
?>

<!-- Admin Footer with Booking Notifications -->
<footer class="bg-light border-top mt-auto">
    <div class="container-fluid">
        <div class="row align-items-center py-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-copyright me-1"></i>
                        <?php echo date('Y'); ?> Myanmar Tourism System
                    </small>
                    <span class="mx-2 text-muted">|</span>
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i>
                        Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
                    </small>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center">
                    <!-- System Status -->
                    <div class="me-3">
                        <small class="text-success">
                            <i class="fas fa-circle me-1"></i>System Online
                        </small>
                    </div>
                    
                    <!-- Booking Notifications -->
                    <?php if ($total_pending > 0): ?>
                    <div class="dropdown me-3">
                        <button class="btn btn-warning btn-sm position-relative" type="button" 
                                id="bookingNotifications" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $total_pending; ?>
                            </span>
                        </button>
                        
                        <div class="dropdown-menu dropdown-menu-end" style="width: 400px; max-height: 500px; overflow-y: auto;">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-check me-2"></i>Pending Bookings</span>
                                <a href="booking-approvals.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="dropdown-divider"></div>
                            
                            <?php foreach ($pending_bookings as $booking): ?>
                            <div class="dropdown-item-text p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($booking['full_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($booking['tour_name']); ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('M d', strtotime($booking['booking_date'])); ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-success fw-bold">
                                        $<?php echo number_format($booking['total_amount'], 2); ?>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success btn-sm" 
                                                onclick="processBooking(<?php echo $booking['id']; ?>, 'approve')"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="processBooking(<?php echo $booking['id']; ?>, 'reject')"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="booking.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-info btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($pending_bookings)): ?>
                            <div class="dropdown-item-text text-center py-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                No pending bookings
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                id="quickActions" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>All Bookings
                            </a></li>
                            <li><a class="dropdown-item" href="customers.php">
                                <i class="fas fa-users me-2"></i>Customers
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Website
                            </a></li>
                            <li><a class="dropdown-item" href="?logout=1">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Booking Action Modal -->
<div class="modal fade" id="bookingActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingActionTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="bookingActionMessage"></p>
                <div class="mb-3">
                    <label for="adminNotes" class="form-label">Admin Notes (Optional)</label>
                    <textarea class="form-control" id="adminNotes" rows="3" 
                              placeholder="Add any notes about this decision..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="actionToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-info-circle me-2"></i>
            <strong class="me-auto">Booking Action</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
let currentBookingId = null;
let currentAction = null;

function processBooking(bookingId, action) {
    currentBookingId = bookingId;
    currentAction = action;
    
    const modal = new bootstrap.Modal(document.getElementById('bookingActionModal'));
    const title = document.getElementById('bookingActionTitle');
    const message = document.getElementById('bookingActionMessage');
    const confirmBtn = document.getElementById('confirmActionBtn');
    
    if (action === 'approve') {
        title.textContent = 'Approve Booking';
        message.textContent = 'Are you sure you want to approve this booking?';
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve';
    } else {
        title.textContent = 'Reject Booking';
        message.textContent = 'Are you sure you want to reject this booking?';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-times me-2"></i>Reject';
    }
    
    modal.show();
}

document.getElementById('confirmActionBtn').addEventListener('click', function() {
    if (!currentBookingId || !currentAction) return;
    
    const adminNotes = document.getElementById('adminNotes').value;
    const formData = new FormData();
    formData.append('action', currentAction);
    formData.append('booking_id', currentBookingId);
    formData.append('admin_notes', adminNotes);
    
    // Show loading state
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    this.disabled = true;
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide modal
        bootstrap.Modal.getInstance(document.getElementById('bookingActionModal')).hide();
        
        // Show toast notification
        showToast(data.message, data.success ? 'success' : 'danger');
        
        if (data.success) {
            // Refresh the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while processing the booking', 'danger');
    })
    .finally(() => {
        // Reset button state
        this.disabled = false;
        if (currentAction === 'approve') {
            this.innerHTML = '<i class="fas fa-check me-2"></i>Approve';
        } else {
            this.innerHTML = '<i class="fas fa-times me-2"></i>Reject';
        }
    });
});

function showToast(message, type) {
    const toast = document.getElementById('actionToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastHeader = toast.querySelector('.toast-header');
    
    // Update toast content
    toastMessage.textContent = message;
    
    // Update toast styling based on type
    toast.className = `toast ${type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'}`;
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Reset modal when hidden
document.getElementById('bookingActionModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('adminNotes').value = '';
    currentBookingId = null;
    currentAction = null;
});

// Auto-refresh notification count every 30 seconds
setInterval(function() {
    // Only refresh if we're not in the middle of an action
    if (!currentBookingId) {
        fetch(window.location.href + '?check_notifications=1')
        .then(response => response.text())
        .then(data => {
            // Update notification badge if needed
            const badge = document.querySelector('#bookingNotifications .badge');
            if (badge && data.includes('pending_count')) {
                // Parse the response to get new count
                // This is a simple implementation - you might want to use JSON
            }
        })
        .catch(error => console.log('Notification refresh error:', error));
    }
}, 30000);
</script>
