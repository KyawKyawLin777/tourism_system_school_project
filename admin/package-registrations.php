<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/PackageRegistration.php';

$database = new Database();
$db = $database->getConnection();

$packageRegistration = new PackageRegistration($db);
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        $registration_id = $_POST['registration_id'];
        $admin_notes = $_POST['admin_notes'] ?? '';
        $admin_id = $_SESSION['admin_id'];

        switch ($_POST['action']) {
            case 'approve':
                if ($packageRegistration->updateStatus($registration_id, 'Approved', $admin_notes, $admin_id)) {
                    $message = '<div class="alert alert-success">Package registration approved successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to approve registration.</div>';
                }
                break;

            case 'reject':
                if ($packageRegistration->updateStatus($registration_id, 'Rejected', $admin_notes, $admin_id)) {
                    $message = '<div class="alert alert-success">Package registration rejected.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to reject registration.</div>';
                }
                break;
        }
    }
}

// Get all registrations
$registrations_stmt = $packageRegistration->read();
$stats = $packageRegistration->getStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Registrations - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .registration-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .registration-card:hover {
            transform: translateY(-3px);
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
                            <a class="nav-link text-white active" href="package-registrations.php">
                                <i class="fas fa-clipboard-list me-2"></i>Package Registrations
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
                    <h1 class="h2">Package Registrations</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-warning me-2"><?php echo $stats['pending']; ?> Pending</span>
                            <span class="badge bg-success me-2"><?php echo $stats['approved']; ?> Approved</span>
                            <span class="badge bg-danger"><?php echo $stats['rejected']; ?> Rejected</span>
                        </div>
                    </div>
                </div>

                <?php echo $message; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Registrations
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['total']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Approval
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['pending']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Approved
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['approved']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Rejected
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $stats['rejected']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrations Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Package Registrations</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Tour</th>
                                        <th>Preferred Date</th>
                                        <th>Passengers</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $registrations_stmt->fetch(PDO::FETCH_ASSOC)): 
                                        $status_class = '';
                                        switch ($row['registration_status']) {
                                            case 'Pending': $status_class = 'warning'; break;
                                            case 'Approved': $status_class = 'success'; break;
                                            case 'Rejected': $status_class = 'danger'; break;
                                            case 'Cancelled': $status_class = 'secondary'; break;
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['package_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['package_type']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['tour_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['location_name']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['preferred_date'])); ?></td>
                                        <td><?php echo $row['number_of_passengers']; ?></td>
                                        <td class="text-success fw-bold">$<?php echo number_format($row['total_estimated_cost'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $row['registration_status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['registration_date'])); ?></td>
                                        <td>
                                            <?php if ($row['registration_status'] == 'Pending'): ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-success" onclick="processRegistration(<?php echo $row['id']; ?>, 'approve', '<?php echo htmlspecialchars($row['full_name']); ?>')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-danger" onclick="processRegistration(<?php echo $row['id']; ?>, 'reject', '<?php echo htmlspecialchars($row['full_name']); ?>')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <?php else: ?>
                                            <small class="text-muted">
                                                Processed by: <?php echo htmlspecialchars($row['processed_by_name'] ?? 'System'); ?>
                                                <br>
                                                <?php echo $row['processed_at'] ? date('M d, Y H:i', strtotime($row['processed_at'])) : ''; ?>
                                            </small>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-info btn-sm mt-1" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Process Registration Modal -->
    <div class="modal fade" id="processModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="processModalTitle">Process Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="registration_id" id="registrationId">
                        <input type="hidden" name="action" id="actionType">
                        
                        <p id="processMessage"></p>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" 
                                      placeholder="Add any notes about this decision..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="processBtn">Process</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registration Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function processRegistration(id, action, customerName) {
            const modal = new bootstrap.Modal(document.getElementById('processModal'));
            const title = document.getElementById('processModalTitle');
            const message = document.getElementById('processMessage');
            const btn = document.getElementById('processBtn');
            
            document.getElementById('registrationId').value = id;
            document.getElementById('actionType').value = action;
            
            if (action === 'approve') {
                title.textContent = 'Approve Registration';
                message.innerHTML = `Are you sure you want to approve the package registration for <strong>${customerName}</strong>?`;
                btn.className = 'btn btn-success';
                btn.textContent = 'Approve Registration';
            } else {
                title.textContent = 'Reject Registration';
                message.innerHTML = `Are you sure you want to reject the package registration for <strong>${customerName}</strong>?`;
                btn.className = 'btn btn-danger';
                btn.textContent = 'Reject Registration';
            }
            
            document.getElementById('admin_notes').value = '';
            modal.show();
        }

        function viewDetails(registration) {
            const content = document.getElementById('detailsContent');
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Customer Information</h6>
                        <p><strong>Name:</strong> ${registration.full_name}</p>
                        <p><strong>Email:</strong> ${registration.email}</p>
                        <p><strong>Phone:</strong> ${registration.phone}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">Package Details</h6>
                        <p><strong>Package:</strong> ${registration.package_name}</p>
                        <p><strong>Type:</strong> ${registration.package_type}</p>
                        <p><strong>Tour:</strong> ${registration.tour_name}</p>
                        <p><strong>Location:</strong> ${registration.location_name}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-info">Booking Details</h6>
                        <p><strong>Preferred Date:</strong> ${new Date(registration.preferred_date).toLocaleDateString()}</p>
                        <p><strong>Passengers:</strong> ${registration.number_of_passengers}</p>
                        <p><strong>Estimated Cost:</strong> $${parseFloat(registration.total_estimated_cost).toFixed(2)}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">Status Information</h6>
                        <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(registration.registration_status)}">${registration.registration_status}</span></p>
                        <p><strong>Registered:</strong> ${new Date(registration.registration_date).toLocaleDateString()}</p>
                        ${registration.processed_at ? `<p><strong>Processed:</strong> ${new Date(registration.processed_at).toLocaleDateString()}</p>` : ''}
                    </div>
                </div>
                ${registration.special_requirements ? `
                <hr>
                <h6 class="text-secondary">Special Requirements</h6>
                <p>${registration.special_requirements}</p>
                ` : ''}
                ${registration.admin_notes ? `
                <hr>
                <h6 class="text-secondary">Admin Notes</h6>
                <p>${registration.admin_notes}</p>
                ` : ''}
            `;
            
            modal.show();
        }

        function getStatusClass(status) {
            switch (status) {
                case 'Pending': return 'warning';
                case 'Approved': return 'success';
                case 'Rejected': return 'danger';
                case 'Cancelled': return 'secondary';
                default: return 'secondary';
            }
        }
    </script>
</body>
</html>
