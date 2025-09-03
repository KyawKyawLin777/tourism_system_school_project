<?php
include_once 'auth.php';
checkAdminLogin();

include_once '../config/database.php';
include_once '../classes/Package.php';
include_once '../classes/Location.php';

$database = new Database();
$db = $database->getConnection();

$package = new Package($db);
$location = new Location($db);
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $package->package_name = $_POST['package_name'];
                $package->package_type = $_POST['package_type'];
                $package->location_id = $_POST['location_id'];
                $package->duration_days = $_POST['duration_days'];
                $package->price = $_POST['price'];
                $package->description = $_POST['description'];
                $package->includes = $_POST['includes'];
                $package->image_url = $_POST['image_url'];

                if ($package->create()) {
                    $message = '<div class="alert alert-success">Package created successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to create package.</div>';
                }
                break;

            case 'update':
                $package->id = $_POST['id'];
                $package->package_name = $_POST['package_name'];
                $package->package_type = $_POST['package_type'];
                $package->location_id = $_POST['location_id'];
                $package->duration_days = $_POST['duration_days'];
                $package->price = $_POST['price'];
                $package->description = $_POST['description'];
                $package->includes = $_POST['includes'];
                $package->image_url = $_POST['image_url'];

                if ($package->update()) {
                    $message = '<div class="alert alert-success">Package updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to update package.</div>';
                }
                break;

            case 'delete':
                $package->id = $_POST['id'];
                if ($package->delete()) {
                    $message = '<div class="alert alert-success">Package deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Failed to delete package. It may be used in tours.</div>';
                }
                break;
        }
    }
}

$stmt = $package->read();
$locations_stmt = $location->read();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .package-card {
            transition: transform 0.3s;
        }

        .package-card:hover {
            transform: translateY(-2px);
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
                            <a class="nav-link text-white active" href="packages.php">
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
                    <h1 class="h2">Manage Packages</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#packageModal">
                        <i class="fas fa-plus me-2"></i>Add Package
                    </button>
                </div>

                <?php echo $message; ?>

                <!-- View Toggle -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="tableViewBtn" onclick="toggleView('table')">
                            <i class="fas fa-table me-1"></i>Table View
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="cardViewBtn" onclick="toggleView('card')">
                            <i class="fas fa-th-large me-1"></i>Card View
                        </button>
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
                                        <th>Package Name</th>
                                        <!-- <th>Type</th> -->
                                        <th>Location</th>
                                        <th>Duration</th>
                                        <th>Price</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $no = 1; // start counter
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <td><?php echo $no; ?></td> <!-- Row number -->
                                            <td><?php echo $row['package_name']; ?></td>
                                            <!-- <td>
                                            <span class="badge bg-<?php echo $row['package_type'] == 'Single' ? 'warning' : 'info'; ?>">
                                                <?php echo $row['package_type']; ?>
                                            </span>
                                        </td> -->
                                            <td><?php echo $row['location_name']; ?></td>
                                            <td><?php echo $row['duration_days']; ?> days</td>
                                            <td><?php echo number_format($row['price'], 2); ?>. MMK</td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewPackage(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editPackage(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePackage(<?php echo $row['id']; ?>, '<?php echo $row['package_name']; ?>')">
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
                    // Reset the statement for card view
                    $stmt = $package->read();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card package-card h-100">
                                <img src="<?php echo $row['image_url'] ?: '/placeholder.svg?height=200&width=300'; ?>" class="card-img-top" alt="<?php echo $row['package_name']; ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title"><?php echo $row['package_name']; ?></h5>
                                        <!-- <span class="badge bg-<?php echo $row['package_type'] == 'Single' ? 'warning' : 'info'; ?>">
                                            <?php echo $row['package_type']; ?>
                                        </span> -->
                                    </div>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo $row['location_name']; ?> •
                                        <i class="fas fa-clock me-1"></i><?php echo $row['duration_days']; ?> Days
                                    </p>
                                    <p class="card-text"><?php echo substr($row['description'], 0, 100) . '...'; ?></p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="h5 text-primary mb-0">$<?php echo number_format($row['price'], 2); ?></span>
                                            <small class="text-muted">ID: <?php echo $row['id']; ?></small>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-outline-info btn-sm" onclick="viewPackage(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm" onclick="editPackage(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" onclick="deletePackage(<?php echo $row['id']; ?>, '<?php echo $row['package_name']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Package Modal -->
    <div class="modal fade" id="packageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="packageForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="packageId">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="package_name" class="form-label">Package Name</label>
                                    <input type="text" class="form-control" id="package_name" name="package_name" required>
                                </div>
                            </div>
                            <div class="col-md-6" hidden>
                                <div class="mb-3">
                                    <label for="package_type" class="form-label">Package Type</label>
                                    <select class="form-select" id="package_type" name="package_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Single">Single</option>
                                        <option value="Double" selected>Double</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_id" class="form-label">Location</label>
                                    <select class="form-select" id="location_id" name="location_id" required>
                                        <option value="">Select Location</option>
                                        <?php while ($loc = $locations_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value="<?php echo $loc['id']; ?>"><?php echo $loc['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="duration_days" class="form-label">Duration (Days)</label>
                                    <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="includes" class="form-label">What's Included</label>
                            <textarea class="form-control" id="includes" name="includes" rows="3" placeholder="Separate items with commas" required></textarea>
                            <div class="form-text">Example: Hotel accommodation, breakfast, guided tours, entrance fees</div>
                        </div>

                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Package Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Package Details</h5>
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
                    <p>Are you sure you want to delete the package "<span id="deletePackageName"></span>"?</p>
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
                        <button type="submit" class="btn btn-danger">Delete Package</button>
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

        function editPackage(packageData) {
            document.getElementById('modalTitle').textContent = 'Edit Package';
            document.getElementById('formAction').value = 'update';
            document.getElementById('packageId').value = packageData.id;
            document.getElementById('package_name').value = packageData.package_name;
            document.getElementById('package_type').value = packageData.package_type;
            document.getElementById('location_id').value = packageData.location_id;
            document.getElementById('duration_days').value = packageData.duration_days;
            document.getElementById('price').value = packageData.price;
            document.getElementById('description').value = packageData.description;
            document.getElementById('includes').value = packageData.includes;
            document.getElementById('image_url').value = packageData.image_url;
            document.getElementById('submitBtn').textContent = 'Update Package';

            new bootstrap.Modal(document.getElementById('packageModal')).show();
        }

        function viewPackage(packageData) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <img src="${packageData.image_url || '/placeholder.svg?height=300&width=400'}" class="img-fluid rounded" alt="${packageData.package_name}">
                    </div>
                    <div class="col-md-6">
                        <h4>${packageData.package_name}</h4>
                       
                        <p><strong>Location:</strong> ${packageData.location_name}</p>
                        <p><strong>Duration:</strong> ${packageData.duration_days} days</p>
                        <p><strong>Price:</strong> $${parseFloat(packageData.price).toFixed(2)}</p>
                        <p><strong>Created:</strong> ${new Date(packageData.created_at).toLocaleDateString()}</p>
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

            document.getElementById('viewContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function deletePackage(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deletePackageName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Reset form when modal is closed
        document.getElementById('packageModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('packageForm').reset();
            document.getElementById('modalTitle').textContent = 'Add Package';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Add Package';
        });
    </script>
</body>

</html>