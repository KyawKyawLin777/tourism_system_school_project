<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/BusType.php';

$database = new Database();
$db = $database->getConnection();

$busType = new BusType($db);
$message = '';

// Handle form submissions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $busType->type_name = $_POST['type_name'];
                $busType->capacity = $_POST['capacity'];
                $busType->amenities = $_POST['amenities'];
                $busType->price_per_km = $_POST['price_per_km'];
                
                if($busType->create()) {
                    $message = '<div class="alert alert-success">Bus type created successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to create bus type.</div>';
                }
                break;
                
            case 'update':
                $busType->id = $_POST['id'];
                $busType->type_name = $_POST['type_name'];
                $busType->capacity = $_POST['capacity'];
                $busType->amenities = $_POST['amenities'];
                $busType->price_per_km = $_POST['price_per_km'];
                
                if($busType->update()) {
                    $message = '<div class="alert alert-success">Bus type updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update bus type.</div>';
                }
                break;
                
            case 'delete':
                $busType->id = $_POST['id'];
                if($busType->delete()) {
                    $message = '<div class="alert alert-success">Bus type deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to delete bus type. It may be used in tours.</div>';
                }
                break;
        }
    }
}

$stmt = $busType->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bus Types - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bus-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .bus-card:hover {
            transform: translateY(-3px);
        }
        .capacity-badge {
            font-size: 0.9rem;
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
                            <a class="nav-link text-white active" href="bus-types.php">
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
                    <h1 class="h2">Manage Bus Types</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#busTypeModal">
                        <i class="fas fa-plus me-2"></i>Add Bus Type
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
                        <div class="card bus-card h-100">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-bus me-2"></i><?php echo $row['type_name']; ?>
                                </h5>
                                <span class="badge bg-light text-dark capacity-badge">
                                    <?php echo $row['capacity']; ?> seats
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Price per KM</h6>
                                    <h4 class="text-success"><?php echo number_format($row['price_per_km'], 2); ?> MMK</h4>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Amenities</h6>
                                    <p class="card-text"><?php echo $row['amenities']; ?></p>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">ID: <?php echo $row['id']; ?></small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="editBusType(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <!-- <button class="btn btn-outline-danger btn-sm" onclick="deleteBusType(<?php echo $row['id']; ?>, '<?php echo $row['type_name']; ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button> -->
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
                                        <th>Bus Type</th>
                                        <th>Capacity</th>
                                        <th>Price/KM</th>
                                        <th>Amenities</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset statement for table view
                                    $stmt = $busType->read();
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <i class="fas fa-bus text-primary me-2"></i>
                                            <?php echo $row['type_name']; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $row['capacity']; ?> seats</span>
                                        </td>
                                        <td class="text-success fw-bold">$<?php echo number_format($row['price_per_km'], 2); ?></td>
                                        <td><?php echo substr($row['amenities'], 0, 50) . '...'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editBusType(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteBusType(<?php echo $row['id']; ?>, '<?php echo $row['type_name']; ?>')">
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

    <!-- Bus Type Modal -->
    <div class="modal fade" id="busTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Bus Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="busTypeForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="busTypeId">
                        
                        <div class="mb-3">
                            <label for="type_name" class="form-label">Bus Type Name</label>
                            <input type="text" class="form-control" id="type_name" name="type_name" required>
                            <div class="form-text">e.g., Standard Bus, Luxury Bus, VIP Bus</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity (Seats)</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price_per_km" class="form-label">Price per KM ($)</label>
                                    <input type="number" class="form-control" id="price_per_km" name="price_per_km" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amenities" class="form-label">Amenities</label>
                            <textarea class="form-control" id="amenities" name="amenities" rows="4" required></textarea>
                            <div class="form-text">Describe the amenities and features of this bus type</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Bus Type</button>
                    </div>
                </form>
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
                    <p>Are you sure you want to delete the bus type "<span id="deleteBusTypeName"></span>"?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone and may affect existing tours.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete Bus Type</button>
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

        function editBusType(busTypeData) {
            document.getElementById('modalTitle').textContent = 'Edit Bus Type';
            document.getElementById('formAction').value = 'update';
            document.getElementById('busTypeId').value = busTypeData.id;
            document.getElementById('type_name').value = busTypeData.type_name;
            document.getElementById('capacity').value = busTypeData.capacity;
            document.getElementById('price_per_km').value = busTypeData.price_per_km;
            document.getElementById('amenities').value = busTypeData.amenities;
            document.getElementById('submitBtn').textContent = 'Update Bus Type';
            
            new bootstrap.Modal(document.getElementById('busTypeModal')).show();
        }

        function deleteBusType(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteBusTypeName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Reset form when modal is closed
        document.getElementById('busTypeModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('busTypeForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Bus Type';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Add Bus Type';
        });
    </script>
</body>
</html>
