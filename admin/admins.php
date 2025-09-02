<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Admin.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);
$message = '';

// Handle form submissions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $admin->username = $_POST['username'];
                $admin->email = $_POST['email'];
                $admin->password = $_POST['password'];
                $admin->full_name = $_POST['full_name'];
                $admin->role = $_POST['role'];
                
                if($admin->create()) {
                    $message = '<div class="alert alert-success">Admin created successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to create admin. Username or email may already exist.</div>';
                }
                break;
                
            case 'update':
                $admin->id = $_POST['id'];
                $admin->username = $_POST['username'];
                $admin->email = $_POST['email'];
                $admin->full_name = $_POST['full_name'];
                $admin->role = $_POST['role'];
                $admin->status = $_POST['status'];
                
                if($admin->update()) {
                    $message = '<div class="alert alert-success">Admin updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update admin.</div>';
                }
                break;
                
            case 'delete':
                $admin->id = $_POST['id'];
                if($_POST['id'] == $_SESSION['admin_id']) {
                    $message = '<div class="alert alert-danger">You cannot delete your own account!</div>';
                } else {
                    if($admin->delete()) {
                        $message = '<div class="alert alert-success">Admin deleted successfully!</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Failed to delete admin.</div>';
                    }
                }
                break;
        }
    }
}

$stmt = $admin->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .admin-card:hover {
            transform: translateY(-3px);
        }
        .role-badge {
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
                            <a class="nav-link text-white active" href="admins.php">
                                <i class="fas fa-user-cog me-2"></i>Admins
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Admins</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminModal">
                        <i class="fas fa-plus me-2"></i>Add Admin
                    </button>
                </div>

                <?php echo $message; ?>

                <!-- Admins Grid -->
                <div class="row g-4">
                    <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card admin-card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                                
                                <h5 class="card-title"><?php echo $row['full_name']; ?></h5>
                                <p class="text-muted">@<?php echo $row['username']; ?></p>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php 
                                        echo $row['role'] == 'Super Admin' ? 'danger' : 
                                            ($row['role'] == 'Admin' ? 'primary' : 'info'); 
                                    ?> role-badge">
                                        <?php echo $row['role']; ?>
                                    </span>
                                    <span class="badge bg-<?php echo $row['status'] == 'Active' ? 'success' : 'secondary'; ?> role-badge ms-1">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </div>
                                
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i><?php echo $row['email']; ?>
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
                                    <button class="btn btn-outline-primary btn-sm" onclick="editAdmin(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if($row['id'] != $_SESSION['admin_id']) { ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteAdmin(<?php echo $row['id']; ?>, '<?php echo $row['full_name']; ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <?php } else { ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-user"></i> You
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Admin Modal -->
    <div class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="adminForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="adminId">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="Super Admin">Super Admin</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Manager">Manager</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="statusDiv" style="display: none;">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="passwordDiv">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6">
                            <div class="form-text">Minimum 6 characters</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Admin</button>
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
                    <p>Are you sure you want to delete admin "<span id="deleteAdminName"></span>"?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone and will permanently remove the admin account.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Delete Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAdmin(adminData) {
            document.getElementById('modalTitle').textContent = 'Edit Admin';
            document.getElementById('formAction').value = 'update';
            document.getElementById('adminId').value = adminData.id;
            document.getElementById('full_name').value = adminData.full_name;
            document.getElementById('username').value = adminData.username;
            document.getElementById('email').value = adminData.email;
            document.getElementById('role').value = adminData.role;
            document.getElementById('status').value = adminData.status;
            document.getElementById('submitBtn').textContent = 'Update Admin';
            
            // Show status field and hide password for edit
            document.getElementById('statusDiv').style.display = 'block';
            document.getElementById('passwordDiv').style.display = 'none';
            document.getElementById('password').required = false;
            
            new bootstrap.Modal(document.getElementById('adminModal')).show();
        }

        function deleteAdmin(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteAdminName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Reset form when modal is closed
        document.getElementById('adminModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('adminForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Admin';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Add Admin';
            document.getElementById('statusDiv').style.display = 'none';
            document.getElementById('passwordDiv').style.display = 'block';
            document.getElementById('password').required = true;
        });
    </script>
</body>
</html>
