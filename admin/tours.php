<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Tour.php';
include_once '../classes/Package.php';
include_once '../classes/BusType.php';

$database = new Database();
$db = $database->getConnection();

$tour = new Tour($db);
$package = new Package($db);
$busType = new BusType($db);
$message = '';

// Handle form submissions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $tour->tour_name = $_POST['tour_name'];
                $tour->package_id = $_POST['package_id'];
                $tour->bus_type_id = $_POST['bus_type_id'];
                $tour->departure_date = $_POST['departure_date'];
                $tour->return_date = $_POST['return_date'];
                $tour->available_seats = $_POST['available_seats'];
                
                if($tour->create()) {
                    $message = '<div class="alert alert-success">Tour created successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to create tour.</div>';
                }
                break;
                
            case 'update':
                $tour->id = $_POST['id'];
                $tour->tour_name = $_POST['tour_name'];
                $tour->package_id = $_POST['package_id'];
                $tour->bus_type_id = $_POST['bus_type_id'];
                $tour->departure_date = $_POST['departure_date'];
                $tour->return_date = $_POST['return_date'];
                $tour->available_seats = $_POST['available_seats'];
                $tour->status = $_POST['status'];
                
                if($tour->update()) {
                    $message = '<div class="alert alert-success">Tour updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update tour.</div>';
                }
                break;
                
            case 'delete':
                $tour->id = $_POST['id'];
                if($tour->delete()) {
                    $message = '<div class="alert alert-success">Tour deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to delete tour. It may have existing bookings.</div>';
                }
                break;
        }
    }
}

$stmt = $tour->readAll();
$packages_stmt = $package->read();
$bus_types_stmt = $busType->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .tour-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .tour-card:hover {
            transform: translateY(-3px);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
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
                            <a class="nav-link text-white active" href="tours.php">
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
                    <h1 class="h2">Manage Tours</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tourModal">
                        <i class="fas fa-plus me-2"></i>Add Tour
                    </button>
                </div>

                <?php echo $message; ?>

                <!-- View Toggle -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="cardViewBtn" onclick="toggleView('card')">
                            <i class="fas fa-th-large me-1"></i>Card View
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="tableViewBtn" onclick="toggleView('table')">
                            <i class="fas fa-table me-1"></i>Table View
                        </button>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="row g-4">
                    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card tour-card h-100 position-relative">
                            <span class="badge bg-<?php 
                                echo $row['status'] == 'Active' ? 'success' : 
                                    ($row['status'] == 'Completed' ? 'info' : 'danger'); 
                            ?> status-badge">
                                <?php echo $row['status']; ?>
                            </span>
                         
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo $row['tour_name']; ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo $row['location_name']; ?> • 
                                    <i class="fas fa-clock me-1"></i><?php echo $row['duration_days']; ?> Days
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M d', strtotime($row['departure_date'])); ?> - 
                                        <?php echo date('M d, Y', strtotime($row['return_date'])); ?>
                                    </small>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-bus me-1"></i><?php echo $row['bus_type']; ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0"><?php echo number_format($row['price'], 2); ?> MMK</span>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i><?php echo $row['available_seats']; ?> seats
                                        </small>
                                    </div>
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-outline-info btn-sm" onclick="viewTour(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="editTour(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteTour(<?php echo $row['id']; ?>, '<?php echo $row['tour_name']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <!-- Table View -->
                <div id="tableView" class="card" style="display: none;">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tour Name</th>
                                        <th>Package</th>
                                        <th>Location</th>
                                        <th>Dates</th>
                                        <th>Price</th>
                                        <th>Seats</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset statement for table view
                                    $stmt = $tour->readAll();
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <strong><?php echo $row['tour_name']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $row['bus_type']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo $row['package_name']; ?>
                                            <br>
                                            <span class="badge bg-<?php echo $row['package_type'] == 'Single' ? 'warning' : 'info'; ?>">
                                                <?php echo $row['package_type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['location_name']; ?></td>
                                        <td>
                                            <?php echo date('M d', strtotime($row['departure_date'])); ?> - 
                                            <?php echo date('M d, Y', strtotime($row['return_date'])); ?>
                                        </td>
                                        <td class="text-success fw-bold">$<?php echo number_format($row['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $row['available_seats']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['status'] == 'Active' ? 'success' : 
                                                    ($row['status'] == 'Completed' ? 'info' : 'danger'); 
                                            ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" onclick="viewTour(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editTour(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTour(<?php echo $row['id']; ?>, '<?php echo $row['tour_name']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

    <!-- Tour Modal -->
    <div class="modal fade" id="tourModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="tourForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="tourId">
                        
                        <div class="mb-3">
                            <label for="tour_name" class="form-label">Tour Name</label>
                            <input type="text" class="form-control" id="tour_name" name="tour_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="package_id" class="form-label">Package</label>
                                    <select class="form-select" id="package_id" name="package_id" required>
                                        <option value="">Select Package</option>
                                        <?php while($pkg = $packages_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value="<?php echo $pkg['id']; ?>" data-location="<?php echo $pkg['location_name']; ?>" data-type="<?php echo $pkg['package_type']; ?>">
                                                <?php echo $pkg['package_name']; ?> (<?php echo $pkg['package_type']; ?>) - $<?php echo $pkg['price']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bus_type_id" class="form-label">Bus Type</label>
                                    <select class="form-select" id="bus_type_id" name="bus_type_id" required>
                                        <option value="">Select Bus Type</option>
                                        <?php while($bus = $bus_types_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value="<?php echo $bus['id']; ?>">
                                                <?php echo $bus['type_name']; ?> (<?php echo $bus['capacity']; ?> seats)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="departure_date" class="form-label">Departure Date</label>
                                    <input type="date" class="form-control" id="departure_date" name="departure_date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="return_date" class="form-label">Return Date</label>
                                    <input type="date" class="form-control" id="return_date" name="return_date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="available_seats" class="form-label">Available Seats</label>
                                    <input type="number" class="form-control" id="available_seats" name="available_seats" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6" id="statusDiv" style="display: none;">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active">Active</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Tour Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tour Details</h5>
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
                    <p>Are you sure you want to delete the tour "<span id="deleteTourName"></span>"?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone and may affect existing bookings.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete Tour</button>
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

        function editTour(tourData) {
            document.getElementById('modalTitle').textContent = 'Edit Tour';
            document.getElementById('formAction').value = 'update';
            document.getElementById('tourId').value = tourData.id;
            document.getElementById('tour_name').value = tourData.tour_name;
            document.getElementById('package_id').value = tourData.package_id;
            document.getElementById('bus_type_id').value = tourData.bus_type_id;
            document.getElementById('departure_date').value = tourData.departure_date;
            document.getElementById('return_date').value = tourData.return_date;
            document.getElementById('available_seats').value = tourData.available_seats;
            document.getElementById('status').value = tourData.status;
            document.getElementById('submitBtn').textContent = 'Update Tour';
            
            // Show status field for edit
            document.getElementById('statusDiv').style.display = 'block';
            
            new bootstrap.Modal(document.getElementById('tourModal')).show();
        }

        function viewTour(tourData) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="/placeholder.svg?height=300&width=400" class="img-fluid rounded" alt="${tourData.tour_name}">
                    </div>
                    <div class="col-md-6">
                        <h4>${tourData.tour_name}</h4>
                        <p><strong>Package:</strong> ${tourData.package_name} (${tourData.package_type})</p>
                        <p><strong>Location:</strong> ${tourData.location_name}</p>
                        <p><strong>Duration:</strong> ${tourData.duration_days} days</p>
                        <p><strong>Price:</strong> $${parseFloat(tourData.price).toFixed(2)}</p>
                        <p><strong>Bus Type:</strong> ${tourData.bus_type}</p>
                        <p><strong>Available Seats:</strong> ${tourData.available_seats}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-${tourData.status === 'Active' ? 'success' : (tourData.status === 'Completed' ? 'info' : 'danger')}">${tourData.status}</span>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Tour Schedule</h5>
                        <p><strong>Departure:</strong> ${new Date(tourData.departure_date).toLocaleDateString()}</p>
                        <p><strong>Return:</strong> ${new Date(tourData.return_date).toLocaleDateString()}</p>
                        <p><strong>Created:</strong> ${new Date(tourData.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Transportation</h5>
                        <p><strong>Bus Type:</strong> ${tourData.bus_type}</p>
                        <p><strong>Amenities:</strong> ${tourData.amenities}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('viewContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function deleteTour(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteTourName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Reset form when modal is closed
        document.getElementById('tourModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('tourForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Tour';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Add Tour';
            document.getElementById('statusDiv').style.display = 'none';
        });

        // Auto-calculate return date based on package duration
        document.getElementById('package_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            // You can add logic here to auto-calculate return date based on package duration
        });

        // Validate return date is after departure date
        document.getElementById('departure_date').addEventListener('change', function() {
            document.getElementById('return_date').min = this.value;
        });
    </script>
</body>
</html>
