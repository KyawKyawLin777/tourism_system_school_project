<?php
include_once 'config/database.php';
include_once 'classes/Tour.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$current_page = basename($_SERVER['PHP_SELF']); // index.php, tours.php, etc.

$tour = new Tour($db);

// Filter by location if specified
$location_filter = isset($_GET['location']) ? $_GET['location'] : null;
if ($location_filter) {
    $stmt = $tour->getByLocation($location_filter);
} else {
    $stmt = $tour->read();
}

// Get locations for filter
$locations_query = "SELECT * FROM locations ORDER BY name";
$locations_stmt = $db->prepare($locations_query);
$locations_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tours - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .tour-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tour-card:hover {
            transform: translateY(-5px);
        }

        .package-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 fw-bold text-center mb-3">Available Tours</h1>
                <p class="lead text-center text-muted">Choose your perfect Myanmar adventure</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <form method="GET" action="tours.php">
                    <div class="input-group">
                        <select name="location" class="form-select">
                            <option value="">All Destinations</option>
                            <?php while ($location = $locations_stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo $location_filter == $location['id'] ? 'selected' : ''; ?>>
                                    <?php echo $location['name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <?php if ($location_filter) { ?>
                            <a href="tours.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        <?php } ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tours Grid -->
        <div class="row g-4">
            <?php
            $tour_count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $tour_count++;
                $badge_class = $package_type == 'Single' ? 'bg-warning' : 'bg-info';
                $departure_formatted = date('M d, Y', strtotime($departure_date));
                $return_formatted = date('M d, Y', strtotime($return_date));
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card tour-card h-100 position-relative">
                        <span class="badge <?php echo $badge_class; ?> package-badge"><?php echo $package_type; ?></span>

                        <img src="<?php echo htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?php echo $tour_name; ?>" height="250px">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $tour_name; ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo $location_name; ?> •
                                <i class="fas fa-clock me-1"></i><?php echo $duration_days; ?> Days
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i><?php echo $departure_formatted; ?> - <?php echo $return_formatted; ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <i class="fas fa-bus me-1"></i><?php echo $bus_type; ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 text-primary mb-0"><?php echo number_format($price, 2); ?> MMK</span>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i><?php echo $available_seats; ?> seats left

                                    </small>
                                </div>

                                <div class="d-grid gap-2">
                                    <!-- Customer login ဖြစ်ပြီးသားဆိုရင် tour details သို့သွားမယ် -->
                                    <a href="tour-details.php?id=<?php echo (int)$id; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if ($tour_count == 0) { ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>No tours found</h3>
                    <p class="text-muted">Try adjusting your filters or check back later for new tours.</p>
                    <a href="tours.php" class="btn btn-primary">View All Tours</a>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-map-marked-alt me-2"></i>Myanmar Tourism</h5>
                    <p>Discover the beauty of Myanmar with our expertly crafted tour packages.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 Myanmar Tourism. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>