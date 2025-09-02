<?php
include_once 'auth.php';
checkCustomerLogin();

include_once '../config/database.php';
include_once '../classes/PackageRegistration.php';

$database = new Database();
$db = $database->getConnection();

$packageRegistration = new PackageRegistration($db);
$customer_info = getCustomerInfo();

// Get customer's registrations
$registrations_stmt = $packageRegistration->readByCustomer($customer_info['id']);

// Handle logout
if (isset($_GET['logout'])) {
    customerLogout();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Myanmar Tourism</title>
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .registration-card:hover {
            transform: translateY(-3px);
        }

        .status-badge {
            font-size: 0.8rem;
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
                        <i class="fas fa-user-circle fa-3x mb-2"></i>
                        <h5><?php echo htmlspecialchars($customer_info['name']); ?></h5>
                        <small><?php echo htmlspecialchars($customer_info['email']); ?></small>
                    </div>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="register-package.php">
                                <i class="fas fa-plus-circle me-2"></i>Register for Package
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="my-registrations.php">
                                <i class="fas fa-list me-2"></i>My Registrations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="profile.php">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../index.php">
                                <i class="fas fa-globe me-2"></i>Browse Tours
                            </a>
                        </li>
                        <li class="nav-item mt-3">
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
                    <h1 class="h2">Welcome, <?php echo htmlspecialchars($customer_info['name']); ?>!</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="register-package.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>New Registration
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <?php
                    $total_registrations = 0;
                    $pending_count = 0;
                    $approved_count = 0;
                    $rejected_count = 0;

                    // Count registrations by status
                    $temp_stmt = $packageRegistration->readByCustomer($customer_info['id']);
                    while ($row = $temp_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $total_registrations++;
                        switch ($row['registration_status']) {
                            case 'Pending':
                                $pending_count++;
                                break;
                            case 'Approved':
                                $approved_count++;
                                break;
                            case 'Rejected':
                                $rejected_count++;
                                break;
                        }
                    }
                    ?>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Registrations
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $total_registrations; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-list fa-2x text-gray-300"></i>
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
                                            <?php echo $pending_count; ?>
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
                                            <?php echo $approved_count; ?>
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
                                            <?php echo $rejected_count; ?>
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

                <!-- Recent Registrations -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Package Registrations</h6>
                        <a href="my-registrations.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($registrations_stmt->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Package</th>
                                            <th>Tour</th>
                                            <th>Preferred Date</th>
                                            <th>Passengers</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 0;
                                        while ($row = $registrations_stmt->fetch(PDO::FETCH_ASSOC) && $count < 5):
                                            $count++;
                                            $status_class = '';
                                            switch ($row['registration_status']) {
                                                case 'Pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'Approved':
                                                    $status_class = 'success';
                                                    break;
                                                case 'Rejected':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'Cancelled':
                                                    $status_class = 'secondary';
                                                    break;
                                            }
                                        ?>
                                            <tr>
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
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo $row['registration_status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['registration_date'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No registrations yet</h5>
                                <p class="text-muted">Start by registering for a tour package!</p>
                                <a href="register-package.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Register Now
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>