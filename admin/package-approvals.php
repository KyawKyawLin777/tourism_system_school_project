<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Package.php';
include_once '../classes/Location.php';

$database = new Database();
$db = $database->getConnection();

$package = new Package($db);
$message = '';

// Handle approval actions
if ($_POST) {
    if (isset($_POST['action'])) {
        $package_id = $_POST['package_id'];
        $action = $_POST['action'];
        $admin_id = $_SESSION['admin_id'];

        switch ($action) {
            case 'approve':
                $query = "UPDATE packages SET approval_status = 'Approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$admin_id, $package_id])) {
                    $message = '<div class="alert alert-success">Package approved successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to approve package.</div>';
                }
                break;

            case 'reject':
                $query = "UPDATE packages SET approval_status = 'Rejected', approved_by = ?, approved_at = NOW() WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$admin_id, $package_id])) {
                    $message = '<div class="alert alert-warning">Package rejected.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to reject package.</div>';
                }
                break;
        }
    }
}

// Get pending packages
$pending_query = "SELECT p.*, l.name as location_name, a.full_name as approved_by_name
                  FROM packages p 
                  LEFT JOIN locations l ON p.location_id = l.id
                  LEFT JOIN admins a ON p.approved_by = a.id
                  WHERE p.approval_status = 'Pending'
                  ORDER BY p.created_at DESC";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->execute();

// Get recently processed packages
$processed_query = "SELECT p.*, l.name as location_name, a.full_name as approved_by_name
                    FROM packages p 
                    LEFT JOIN locations l ON p.location_id = l.id
                    LEFT JOIN admins a ON p.approved_by = a.id
                    WHERE p.approval_status IN ('Approved', 'Rejected')
                    ORDER BY p.approved_at DESC
                    LIMIT 10";
$processed_stmt = $db->prepare($processed_query);
$processed_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Approvals - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .package-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .package-card:hover {
            transform: translateY(-3px);
        }

        .status-badge {
            font-size: 0.8rem;
        }

        .approval-actions {
            position: absolute;
            top: 10px;
            right: 10px;
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
                            <a class="nav-link text-white active" href="package-approvals.php">
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
                    <h1 class="h2">Package Approvals</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-warning fs-6">
                                <?php echo $pending_stmt->rowCount(); ?> Pending
                            </span>
                        </div>
                    </div>
                </div>

                <?php echo $message; ?>

                <!-- Pending Packages -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Pending Package Approvals
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($pending_stmt->rowCount() > 0) { ?>
                            <div class="row g-4">
                                <?php while ($package = $pending_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="card package-card h-100 position-relative">
                                            <div class="approval-actions">
                                                <div class="btn-group-vertical">
                                                    <button class="btn btn-success btn-sm" onclick="approvePackage(<?php echo $package['id']; ?>, '<?php echo $package['package_name']; ?>')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="rejectPackage(<?php echo $package['id']; ?>, '<?php echo $package['package_name']; ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <img src="<?php echo $package['image_url'] ?: '/placeholder.svg?height=200&width=300'; ?>" class="card-img-top" alt="<?php echo $package['package_name']; ?>" style="height: 200px; object-fit: cover;">

                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $package['package_name']; ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $package['location_name']; ?> •
                                                    <i class="fas fa-clock me-1"></i><?php echo $package['duration_days']; ?> Days
                                                </p>
                                                <p class="card-text"><?php echo substr($package['description'], 0, 100) . '...'; ?></p>

                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="h5 text-primary mb-0">$<?php echo number_format($package['price'], 2); ?></span>
                                                    <span class="badge bg-<?php echo $package['package_type'] == 'Single' ? 'warning' : 'info'; ?>">
                                                        <?php echo $package['package_type']; ?>
                                                    </span>
                                                </div>

                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>Submitted: <?php echo date('M d, Y', strtotime($package['created_at'])); ?>
                                                </small>
                                            </div>

                                            <div class="card-footer bg-transparent">
                                                <button class="btn btn-outline-info btn-sm w-100" onclick="viewPackageDetails(<?php echo htmlspecialchars(json_encode($package)); ?>)">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h4>All Caught Up!</h4>
                                <p class="text-muted">No packages pending approval at this time.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Recently Processed -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recently Processed Packages
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Package</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Processed By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($package = $processed_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $package['package_name']; ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $package['package_type']; ?> • <?php echo $package['duration_days']; ?> days</small>
                                            </td>
                                            <td><?php echo $package['location_name']; ?></td>
                                            <td>$<?php echo number_format($package['price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $package['approval_status'] == 'Approved' ? 'success' : 'danger'; ?>">
                                                    <?php echo $package['approval_status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $package['approved_by_name']; ?></td>
                                            <td>
                                                <?php
                                                if (!empty($package['approved_at'])) {
                                                    echo date('M d, Y H:i', strtotime($package['approved_at']));
                                                } else {
                                                    echo '<span class="text-muted">Not approved yet</span>';
                                                }
                                                ?>
                                            </td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Package Details Modal -->
    <div class="modal fade" id="packageDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="packageDetailsContent">
                    <!-- Content populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Confirmation Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="approvalModalBody">
                    <!-- Content populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="package_id" id="approvalPackageId">
                        <input type="hidden" name="action" id="approvalAction">
                        <button type="submit" class="btn" id="approvalSubmitBtn">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewPackageDetails(packageData) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="${packageData.image_url || '/placeholder.svg?height=300&width=400'}" class="img-fluid rounded" alt="${packageData.package_name}">
                    </div>
                    <div class="col-md-6">
                        <h4>${packageData.package_name}</h4>
                        <p><strong>Type:</strong> <span class="badge bg-${packageData.package_type === 'Single' ? 'warning' : 'info'}">${packageData.package_type}</span></p>
                        <p><strong>Location:</strong> ${packageData.location_name}</p>
                        <p><strong>Duration:</strong> ${packageData.duration_days} days</p>
                        <p><strong>Price:</strong> $${parseFloat(packageData.price).toFixed(2)}</p>
                        <p><strong>Submitted:</strong> ${new Date(packageData.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>Description</h5>
                        <p>${packageData.description}</p>
                        <h5>What's Included</h5>
                        <ul>
                            ${packageData.includes.split(',').map(item => `<li>${item.trim()}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;

            document.getElementById('packageDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('packageDetailsModal')).show();
        }

        function approvePackage(id, name) {
            document.getElementById('approvalModalTitle').textContent = 'Approve Package';
            document.getElementById('approvalModalBody').innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Are you sure you want to approve the package "<strong>${name}</strong>"?
                </div>
                <p>This package will be made available for booking once approved.</p>
            `;
            document.getElementById('approvalPackageId').value = id;
            document.getElementById('approvalAction').value = 'approve';
            document.getElementById('approvalSubmitBtn').className = 'btn btn-success';
            document.getElementById('approvalSubmitBtn').textContent = 'Approve Package';

            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }

        function rejectPackage(id, name) {
            document.getElementById('approvalModalTitle').textContent = 'Reject Package';
            document.getElementById('approvalModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    Are you sure you want to reject the package "<strong>${name}</strong>"?
                </div>
                <p>This package will not be available for booking and may need revision.</p>
            `;
            document.getElementById('approvalPackageId').value = id;
            document.getElementById('approvalAction').value = 'reject';
            document.getElementById('approvalSubmitBtn').className = 'btn btn-danger';
            document.getElementById('approvalSubmitBtn').textContent = 'Reject Package';

            new bootstrap.Modal(document.getElementById('approvalModal')).show();
        }
    </script>
</body>

</html>