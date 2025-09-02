<?php
include_once 'auth.php';
checkCustomerLogin();

include_once '../config/database.php';
include_once '../classes/PackageRegistration.php';
include_once '../classes/Tour.php';
include_once '../classes/Package.php';

$database = new Database();
$db = $database->getConnection();

$packageRegistration = new PackageRegistration($db);
$tour = new Tour($db);
$package = new Package($db);
$customer_info = getCustomerInfo();

$message = '';

// Handle form submission
if ($_POST) {
    $packageRegistration->customer_id = $customer_info['id'];
    $packageRegistration->package_id = $_POST['package_id'];
    $packageRegistration->tour_id = $_POST['tour_id'];
    $packageRegistration->preferred_date = $_POST['preferred_date'];
    $packageRegistration->number_of_passengers = $_POST['number_of_passengers'];
    $packageRegistration->special_requirements = $_POST['special_requirements'];
    $packageRegistration->total_estimated_cost = $_POST['total_estimated_cost'];

    if ($packageRegistration->create()) {
        $message = '<div class="alert alert-success">Package registration submitted successfully! We will review your request and get back to you soon.</div>';
    } else {
        $message = '<div class="alert alert-danger">Failed to submit registration. Please try again.</div>';
    }
}

// Get available tours and packages
$tours_stmt = $tour->read();
$packages_stmt = $package->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Package - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="register-package.php">
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
                    <h1 class="h2">Register for Package</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <?php echo $message; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>Package Registration Form
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="registrationForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="tour_id" class="form-label">
                                                <i class="fas fa-route me-1"></i>Select Tour *
                                            </label>
                                            <select class="form-select" id="tour_id" name="tour_id" required onchange="updateTourDetails()">
                                                <option value="">Choose a tour...</option>
                                                <?php while ($tour_row = $tours_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                                <option value="<?php echo $tour_row['id']; ?>" 
                                                        data-price="<?php echo $tour_row['price']; ?>"
                                                        data-departure="<?php echo $tour_row['departure_date']; ?>"
                                                        data-return="<?php echo $tour_row['return_date']; ?>"
                                                        data-location="<?php echo $tour_row['location_name']; ?>">
                                                    <?php echo htmlspecialchars($tour_row['tour_name']); ?> - 
                                                    <?php echo htmlspecialchars($tour_row['location_name']); ?> 
                                                    ($<?php echo number_format($tour_row['price'], 2); ?>)
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="package_id" class="form-label">
                                                <i class="fas fa-box me-1"></i>Select Package Type *
                                            </label>
                                            <select class="form-select" id="package_id" name="package_id" required onchange="updatePackageDetails()">
                                                <option value="">Choose package type...</option>
                                                <?php while ($package_row = $packages_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                                <option value="<?php echo $package_row['id']; ?>"
                                                        data-type="<?php echo $package_row['package_type']; ?>"
                                                        data-description="<?php echo $package_row['description']; ?>">
                                                    <?php echo htmlspecialchars($package_row['package_name']); ?> - 
                                                    <?php echo htmlspecialchars($package_row['package_type']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="preferred_date" class="form-label">
                                                <i class="fas fa-calendar me-1"></i>Preferred Date *
                                            </label>
                                            <input type="date" class="form-control" id="preferred_date" 
                                                   name="preferred_date" required min="<?php echo date('Y-m-d'); ?>">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="number_of_passengers" class="form-label">
                                                <i class="fas fa-users me-1"></i>Number of Passengers *
                                            </label>
                                            <input type="number" class="form-control" id="number_of_passengers" 
                                                   name="number_of_passengers" min="1" max="10" value="1" required onchange="calculateTotal()">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="special_requirements" class="form-label">
                                            <i class="fas fa-comment me-1"></i>Special Requirements
                                        </label>
                                        <textarea class="form-control" id="special_requirements" 
                                                  name="special_requirements" rows="3" 
                                                  placeholder="Any special dietary requirements, accessibility needs, or other requests..."></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="total_estimated_cost" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>Estimated Total Cost
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="total_estimated_cost" 
                                                   name="total_estimated_cost" step="0.01" readonly>
                                        </div>
                                        <small class="form-text text-muted">
                                            This is an estimated cost. Final pricing will be confirmed upon approval.
                                        </small>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="reset" class="btn btn-outline-secondary me-md-2">
                                            <i class="fas fa-undo me-1"></i>Reset Form
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i>Submit Registration
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Registration Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="tourDetails" class="mb-3" style="display: none;">
                                    <h6 class="text-primary">Tour Details</h6>
                                    <p id="tourInfo" class="small text-muted"></p>
                                </div>

                                <div id="packageDetails" class="mb-3" style="display: none;">
                                    <h6 class="text-success">Package Details</h6>
                                    <p id="packageInfo" class="small text-muted"></p>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Important Notes</h6>
                                    <ul class="small mb-0">
                                        <li>All registrations are subject to approval</li>
                                        <li>You will be notified via email once processed</li>
                                        <li>Payment is required only after approval</li>
                                        <li>Cancellation policy applies after approval</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateTourDetails() {
            const tourSelect = document.getElementById('tour_id');
            const selectedOption = tourSelect.options[tourSelect.selectedIndex];
            const tourDetails = document.getElementById('tourDetails');
            const tourInfo = document.getElementById('tourInfo');

            if (selectedOption.value) {
                const price = selectedOption.dataset.price;
                const departure = selectedOption.dataset.departure;
                const returnDate = selectedOption.dataset.return;
                const location = selectedOption.dataset.location;

                tourInfo.innerHTML = `
                    <strong>Location:</strong> ${location}<br>
                    <strong>Departure:</strong> ${new Date(departure).toLocaleDateString()}<br>
                    <strong>Return:</strong> ${new Date(returnDate).toLocaleDateString()}<br>
                    <strong>Price per person:</strong> $${parseFloat(price).toFixed(2)}
                `;
                tourDetails.style.display = 'block';
                calculateTotal();
            } else {
                tourDetails.style.display = 'none';
            }
        }

        function updatePackageDetails() {
            const packageSelect = document.getElementById('package_id');
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            const packageDetails = document.getElementById('packageDetails');
            const packageInfo = document.getElementById('packageInfo');

            if (selectedOption.value) {
                const type = selectedOption.dataset.type;
                const description = selectedOption.dataset.description;

                packageInfo.innerHTML = `
                    <strong>Type:</strong> ${type}<br>
                    <strong>Description:</strong> ${description}
                `;
                packageDetails.style.display = 'block';
            } else {
                packageDetails.style.display = 'none';
            }
        }

        function calculateTotal() {
            const tourSelect = document.getElementById('tour_id');
            const passengersInput = document.getElementById('number_of_passengers');
            const totalInput = document.getElementById('total_estimated_cost');

            if (tourSelect.value && passengersInput.value) {
                const selectedOption = tourSelect.options[tourSelect.selectedIndex];
                const pricePerPerson = parseFloat(selectedOption.dataset.price);
                const passengers = parseInt(passengersInput.value);
                const total = pricePerPerson * passengers;

                totalInput.value = total.toFixed(2);
            } else {
                totalInput.value = '';
            }
        }

        // Handle logout
        if (window.location.search.includes('logout=1')) {
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>
