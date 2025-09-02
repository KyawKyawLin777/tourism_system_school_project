<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Tour.php';

$database = new Database();
$db = $database->getConnection();

$tour = new Tour($db);
$tour->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Tour ID not found.');

$tour_details = $tour->readOne();
if (!$tour_details) {
    die('ERROR: Tour not found.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tour_details['tour_name']; ?> - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-map-marked-alt me-2"></i>Myanmar Tourism
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <img src="<?php echo htmlspecialchars($tour_details['image_url']); ?>"
                        class="card-img-top img-fluid"
                        alt="<?php echo htmlspecialchars($tour_details['tour_name']); ?>"
                        style="max-height: 500px; object-fit: cover;">

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h1 class="card-title"><?php echo htmlspecialchars($tour_details['tour_name']); ?></h1>
                            <span class="badge bg-<?php echo $tour_details['package_type'] == 'Single' ? 'warning' : 'info'; ?> fs-6">
                                <?php echo $tour_details['package_type']; ?> Package
                            </span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><i class="fas fa-map-marker-alt text-primary me-2"></i><strong>Destination:</strong> <?php echo htmlspecialchars($tour_details['location_name']); ?></p>
                                <p><i class="fas fa-calendar text-success me-2"></i><strong>Duration:</strong> <?php echo $tour_details['duration_days']; ?> Days</p>
                                <p><i class="fas fa-bus text-warning me-2"></i><strong>Transportation:</strong> <?php echo htmlspecialchars($tour_details['bus_type']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><i class="fas fa-calendar-alt text-info me-2"></i><strong>Departure:</strong> <?php echo date('M d, Y', strtotime($tour_details['departure_date'])); ?></p>
                                <p><i class="fas fa-calendar-check text-info me-2"></i><strong>Return:</strong> <?php echo date('M d, Y', strtotime($tour_details['return_date'])); ?></p>
                                <p><i class="fas fa-users text-secondary me-2"></i><strong>Available Seats:</strong> <?php echo $tour_details['available_seats']; ?></p>
                            </div>
                        </div>

                        <h3>Tour Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($tour_details['description'])); ?></p>

                        <h3>What's Included</h3>
                        <ul class="list-group list-group-flush mb-4">
                            <?php
                            $includes = explode(',', $tour_details['includes']);
                            foreach ($includes as $include) {
                                echo '<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>' . htmlspecialchars(trim($include)) . '</li>';
                            }
                            ?>
                        </ul>

                        <h3>Transportation Details</h3>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-bus me-2"></i><?php echo htmlspecialchars($tour_details['bus_type']); ?></h5>
                            <p class="mb-0"><strong>Capacity:</strong> <?php echo htmlspecialchars($tour_details['capacity']); ?> passengers</p>
                            <p class="mb-0"><strong>Amenities:</strong> <?php echo htmlspecialchars($tour_details['amenities']); ?></p>
                        </div>

                        <!-- Booking button -->
                        <div class="mt-4">
                            <?php if ($tour_details['available_seats'] > 0): ?>
                                <?php if (isset($_SESSION['customer_id'])): ?>
                                    <!-- Customer logged in → show Book Now -->
                                    <a href="booking_form.php?tour_id=<?php echo urlencode($tour_details['id']); ?>" class="btn btn-primary btn-lg">
                                        <i class="fas fa-ticket-alt me-2"></i>Book Now
                                    </a>
                                <?php else: ?>
                                    <!-- Not logged in → show login prompt -->
                                    <a href="customer/login.php?redirect=<?php echo urlencode("tour-details.php?id=" . $tour_details['id']); ?>"
                                        class="btn btn-info btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Log in to Book
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-danger btn-lg" disabled>
                                    <i class="fas fa-ban me-2"></i>Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>



        </div>
    </div>
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-map-marked-alt me-2"></i>Myanmar Tourism</h5>
                    <p>Discover the beauty of Myanmar with our expertly crafted tour packages.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 Myanmar Tourism. All rights reserved.</p>
                    <div>
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate total price dynamically
        document.getElementById('passengers').addEventListener('change', function() {
            const passengers = parseInt(this.value) || 0;
            const pricePerPerson = <?php echo $tour_details['price']; ?>;
            const totalPrice = passengers * pricePerPerson;

            // You can add a total price display here if needed
        });
    </script>
</body>

</html>