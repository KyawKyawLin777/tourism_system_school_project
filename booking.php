<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$current_page = basename($_SERVER['PHP_SELF']); // index.php, tours.php, etc.

try {
    include_once 'config/database.php';
    include_once 'classes/Customer.php';
    include_once 'classes/Booking.php';
    include_once 'classes/Tour.php';
} catch (Exception $e) {
    die("Error loading required files: " . $e->getMessage());
}

$success_message = '';
$error_message = '';
$booking_reference = '';
$booking_status = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate required fields
        $required_fields = ['full_name', 'email', 'phone', 'address', 'tour_id', 'passengers', 'price'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '$field' is required.");
            }
        }

        $database = new Database();
        $db = $database->getConnection();

        $customer = new Customer($db);
        $booking = new Booking($db);
        $tour = new Tour($db);

        // Sanitize input data
        $customer->full_name = htmlspecialchars(strip_tags($_POST['full_name']));
        $customer->email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $customer->phone = htmlspecialchars(strip_tags($_POST['phone']));
        $customer->address = htmlspecialchars(strip_tags($_POST['address']));

        $tour_id = intval($_POST['tour_id']);
        $passengers = intval($_POST['passengers']);
        $price_per_person = floatval($_POST['price']);
        $total_amount = $passengers * $price_per_person;

        if (!filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        if ($passengers <= 0 || $passengers > 20) {
            throw new Exception("Invalid number of passengers.");
        }

        // Check tour availability
        $tour->id = $tour_id;
        $tour_details = $tour->readOne();
        if (!$tour_details) {
            throw new Exception("Tour not found.");
        }

        if ($tour_details['available_seats'] < $passengers) {
            throw new Exception("Not enough seats available. Only " . $tour_details['available_seats'] . " seats left.");
        }

        // Check if customer exists
        $existing_customer = $customer->emailExists();
        if (!$existing_customer) {
            if (!$customer->create()) {
                throw new Exception("Failed to create customer record.");
            }
        } else {
            $customer->id = $existing_customer['id'];
        }

        // Create booking
        $booking->customer_id = $customer->id;
        $booking->tour_id = $tour_id;
        $booking->number_of_passengers = $passengers;
        $booking->total_amount = $total_amount;

        if ($booking->create()) {
            // Update available seats
            if ($booking->updateSeats($tour_id, $passengers)) {
                $success_message = "Booking successful! Your booking reference is: " . $booking->booking_reference;
                $booking_reference = $booking->booking_reference;

                // Fetch booking status
                $stmt = $db->prepare("SELECT booking_status FROM bookings WHERE booking_reference = ?");
                $stmt->execute([$booking->booking_reference]);
                $status_row = $stmt->fetch(PDO::FETCH_ASSOC);
                $booking_status = $status_row['booking_status'] ?? 'Pending';
            } else {
                throw new Exception("Booking created but failed to update seat availability.");
            }
        } else {
            throw new Exception("Failed to create booking. Please try again.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Booking Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .booking-card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .success-icon {
            color: #28a745;
            font-size: 4rem;
        }

        .error-icon {
            color: #dc3545;
            font-size: 4rem;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-map-marked-alt me-2"></i>Myanmar Tourism
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'tours.php') ? 'active' : ''; ?>" href="tours.php">Tours</a>
                    </li>

                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'order.php') ? 'active' : ''; ?>" href="order.php">Order</a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="customer/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'admins.php') ? 'active' : ''; ?>" href="admin/admins.php">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="customer/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <?php if (!empty($success_message)) { ?>
                    <div class="card shadow booking-card border-0">
                        <!-- Header -->
                        <div class="card-header text-white text-center py-4 
                        <?php echo ($booking_status == 'Pending') ? 'bg-info' : 'bg-success'; ?>">
                            <h2 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php
                                echo ($booking_status == 'Pending') ? "Thank You for your Order!" : "Booking Confirmed!";
                                ?>
                            </h2>
                        </div>

                        <!-- Body -->
                        <div class="card-body py-5">
                            <div class="text-center mb-5">
                                <i class="fas fa-ticket-alt success-icon mb-3" style="font-size:4rem;color:#0d6efd;"></i>
                                <h4 class="fw-bold text-primary"><?php echo htmlspecialchars($success_message); ?></h4>
                            </div>

                            <!-- Customer & Booking Info -->
                            <div class="row g-4 mb-4">
                                <!-- Customer Details -->
                                <div class="col-md-6">
                                    <div class="card border-light shadow-sm h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary mb-3">
                                                <i class="fas fa-user me-2"></i>Customer Details
                                            </h5>
                                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($_POST['full_name']); ?></p>
                                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($_POST['email']); ?></p>
                                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($_POST['phone']); ?></p>
                                            <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($_POST['address']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Booking Info -->
                                <div class="col-md-6">
                                    <div class="card border-light shadow-sm h-100">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary mb-3">
                                                <i class="fas fa-map-marker-alt me-2"></i>Booking Information
                                            </h5>
                                            <p class="mb-1"><strong>Reference:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($booking_reference); ?></span></p>
                                            <p class="mb-1"><strong>Passengers:</strong> <?php echo intval($_POST['passengers']); ?></p>
                                            <p class="mb-1"><strong>Price per Person:</strong> <?php echo number_format(floatval($_POST['price']), 2); ?> MMK</p>
                                            <p class="mb-1"><strong>Total Amount:</strong> <span class="text-success fw-bold"><?php echo number_format($passengers * floatval($_POST['price']), 2); ?> MMK</span></p>
                                            <p class="mb-0"><strong>Status:</strong>
                                                <?php if ($booking_status == 'Pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                            <div class="mt-3 alert alert-info rounded-3">
                                                <h6 class="mb-1"><i class="fas fa-info-circle me-2"></i>Next Steps</h6>
                                                <p class="mb-0">We will contact you soon to confirm your booking.</p>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo htmlspecialchars($booking_status); ?></span>
                                        <?php endif; ?>
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                                <a href="index.php" class="btn btn-primary btn-lg me-md-2">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                                <a href="order.php" class="btn btn-success btn-lg me-md-2">
                                    <i class="fas fa-dollar me-2"></i>Get Payment
                                </a>
                                <a href="tours.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Browse More Tours
                                </a>
                            </div>
                        </div>
                    </div>

                <?php } elseif (!empty($error_message)) { ?>
                    <!-- Error Card -->
                    <div class="card shadow booking-card border-danger">
                        <div class="card-header bg-danger text-white text-center py-4">
                            <h2><i class="fas fa-exclamation-triangle me-2"></i>Booking Failed</h2>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-times-circle error-icon mb-3" style="font-size:4rem;color:#dc3545;"></i>
                            <h4 class="text-danger"><?php echo htmlspecialchars($error_message); ?></h4>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                <button onclick="history.back()" class="btn btn-primary btn-lg me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Go Back & Try Again
                                </button>

                                <a href="tours.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Browse Tours
                                </a>
                            </div>
                        </div>
                    </div>

                <?php } else { ?>
                    <!-- No Booking Data -->
                    <div class="card shadow booking-card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-exclamation-circle text-warning mb-3" style="font-size:4rem;"></i>
                            <h3 class="mt-3">No Booking Data Found</h3>
                            <p class="text-muted">Please select a tour and fill out the booking form to proceed.</p>
                            <a href="tours.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Browse Available Tours
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>


    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 Myanmar Tourism. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>