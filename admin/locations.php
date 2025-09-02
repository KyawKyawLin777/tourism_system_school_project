<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Location.php';

$database = new Database();
$db = $database->getConnection();

$location = new Location($db);
$message = '';

// Handle form submissions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $location->name = $_POST['name'];
                $location->description = $_POST['description'];
                $location->image_url = $_POST['image_url'];
                
                if($location->create()) {
                    $message = '<div class="alert alert-success">Location created successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to create location.</div>';
                }
                break;
                
            case 'update':
                $location->id = $_POST['id'];
                $location->name = $_POST['name'];
                $location->description = $_POST['description'];
                $location->image_url = $_POST['image_url'];
                
                if($location->update()) {
                    $message = '<div class="alert alert-success">Location updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update location.</div>';
                }
                break;
                
            case 'delete':
                $location->id = $_POST['id'];
                if($location->delete()) {
                    $message = '<div class="alert alert-success">Location deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to delete location.</div>';
                }
                break;
        }
    }
}

$stmt = $location->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Locations - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                            <a class="nav-link text-white active" href="locations.php">
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
                    <h1 class="h2">Manage Locations</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal">
                        <i class="fas fa-plus me-2"></i>Add Location
                    </button>
                </div>

                <?php echo $message; ?>

                <!-- Locations Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Image URL</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                               <tbody>
                                <?php 
                                $no = 1; // start counter
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php echo $no; ?></td> <!-- Row number -->
                                   
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo substr($row['description'], 0, 100) . '...'; ?></td>
                                    <td><?php echo $row['image_url']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editLocation(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLocation(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php $no++; } ?>
                            </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Location Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="locationForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="locationId">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Location Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Location</button>
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
                    Are you sure you want to delete this location?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editLocation(location) {
            document.getElementById('modalTitle').textContent = 'Edit Location';
            document.getElementById('formAction').value = 'update';
            document.getElementById('locationId').value = location.id;
            document.getElementById('name').value = location.name;
            document.getElementById('description').value = location.description;
            document.getElementById('image_url').value = location.image_url;
            document.getElementById('submitBtn').textContent = 'Update Location';
            
            new bootstrap.Modal(document.getElementById('locationModal')).show();
        }

        function deleteLocation(id) {
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Reset form when modal is closed
        document.getElementById('locationModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('locationForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Location';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Add Location';
        });
    </script>
</body>
</html>
