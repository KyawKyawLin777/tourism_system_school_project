<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Customer.php';

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);
$message = '';

// Handle search and pagination
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $customer->id = $_POST['id'];
                $customer->full_name = $_POST['full_name'];
                $customer->email = $_POST['email'];
                $customer->phone = $_POST['phone'];
                $customer->address = $_POST['address'];

                if ($customer->update()) {
                    $message = '<div class="alert alert-success">Customer updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update customer.</div>';
                }
                break;

            case 'delete':
                $customer->id = $_POST['id'];
                if ($customer->delete()) {
                    $message = '<div class="alert alert-success">Customer deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to delete customer.</div>';
                }
                break;
        }
    }
}

// Build query with search
$where_clause = '';
$params = [];
if (!empty($search)) {
    $where_clause = "WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM customers $where_clause";
$count_stmt = $db->prepare($count_query);
if (!empty($params)) {
    $count_stmt->execute($params);
} else {
    $count_stmt->execute();
}
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get customers with pagination
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = c.id) as total_bookings,
          (SELECT SUM(b.total_amount) FROM bookings b WHERE b.customer_id = c.id AND b.payment_status = 'Paid') as total_spent
          FROM customers c 
          $where_clause 
          ORDER BY c.created_at DESC 
          LIMIT $records_per_page OFFSET $offset";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .customer-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .customer-card:hover {
            transform: translateY(-3px);
        }

        .stats-badge {
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
                            <a class="nav-link text-white active" href="customers.php">
                                <i class="fas fa-users me-2"></i>Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>Bookings
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
                    <h1 class="h2">Manage Customers</h1>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?php echo $total_records; ?> Total Customers</span>
                    </div>
                </div>

                <?php echo $message; ?>

                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" class="form-control me-2" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($search)) { ?>
                                <a href="customers.php" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php } ?>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="tableViewBtn" onclick="toggleView('table')">
                                <i class="fas fa-table me-1"></i>Table View
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="cardViewBtn" onclick="toggleView('card')">
                                <i class="fas fa-th-large me-1"></i>Card View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableView" class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Bookings</th>
                                        <!-- <th>Total Spent</th> -->
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $no = 1; // start counter
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $no; ?></td>
                                            <td>
                                                <div>
                                                    <strong><?php echo $row['full_name']; ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $row['email']; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-phone text-primary me-1"></i><?php echo $row['phone']; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo substr($row['address'], 0, 30) . '...'; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $row['total_bookings']; ?> bookings</span>
                                            </td>
                                            <!-- <td class="text-success fw-bold">
                                            <?php echo number_format($row['total_spent'] ?? 0, 2); ?> MMK
                                        </td> -->
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewCustomer(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCustomer(<?php echo $row['id']; ?>, '<?php echo $row['full_name']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php $no++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="row g-4" style="display: none;">
                    <?php

                    // Reset statement for card view
                    $stmt = $db->prepare($query);
                    if (!empty($params)) {
                        $stmt->execute($params);
                    } else {
                        $stmt->execute();
                    }
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card customer-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                                    </div>

                                    <h5 class="card-title"><?php echo $row['full_name']; ?></h5>
                                    <p class="text-muted"><?php echo $row['email']; ?></p>

                                    <div class="mb-3">
                                        <span class="badge bg-info stats-badge"><?php echo $row['total_bookings']; ?> bookings</span>
                                        <span class="badge bg-success stats-badge ms-1">$<?php echo number_format($row['total_spent'] ?? 0, 2); ?></span>
                                    </div>

                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-phone me-1"></i><?php echo $row['phone']; ?>
                                        </small>
                                    </p>

                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Joined <?php echo date('M Y', strtotime($row['created_at'])); ?>
                                        </small>
                                    </p>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewCustomer(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteCustomer(<?php echo $row['id']; ?>, '<?php echo $row['full_name']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <nav aria-label="Customer pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                </li>
                            <?php } ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) { ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <?php if ($page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            </main>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="customerId">

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete customer "<span id="deleteCustomerName"></span>"?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will also delete all their bookings and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleView(view) {
            const tableView = document.getElementById('tableView');
            const cardView = document.getElementById('cardView');
            const tableBtn = document.getElementById('tableViewBtn');
            const cardBtn = document.getElementById('cardViewBtn');

            if (view === 'table') {
                tableView.style.display = 'block';
                cardView.style.display = 'none';
                tableBtn.classList.add('active');
                cardBtn.classList.remove('active');
            } else {
                tableView.style.display = 'none';
                cardView.style.display = 'flex';
                cardBtn.classList.add('active');
                tableBtn.classList.remove('active');
            }
        }

        function editCustomer(customer) {
            document.getElementById('customerId').value = customer.id;
            document.getElementById('full_name').value = customer.full_name;
            document.getElementById('email').value = customer.email;
            document.getElementById('phone').value = customer.phone;
            document.getElementById('address').value = customer.address;

            new bootstrap.Modal(document.getElementById('customerModal')).show();
        }

        function viewCustomer(customer) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                            <h4 class="mt-2">${customer.full_name}</h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Contact Information</h5>
                        <p><strong>Email:</strong> ${customer.email}</p>
                        <p><strong>Phone:</strong> ${customer.phone}</p>
                        <p><strong>Address:</strong> ${customer.address}</p>
                        <p><strong>Customer ID:</strong> ${customer.id}</p>
                        <p><strong>Registered:</strong> ${new Date(customer.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3>${customer.total_bookings}</h3>
                                <p class="mb-0">Total Bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3>$${parseFloat(customer.total_spent || 0).toFixed(2)}</h3>
                                <p class="mb-0">Total Spent</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('viewContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function deleteCustomer(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteCustomerName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>

</html>