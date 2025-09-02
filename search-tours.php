<?php
include_once 'config/database.php';
include_once 'classes/Tour.php';

$database = new Database();
$db = $database->getConnection();

// Get search parameters
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$package_type = isset($_GET['package_type']) ? $_GET['package_type'] : '';
$departure_date = isset($_GET['departure_date']) ? $_GET['departure_date'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build search query
$where_conditions = ["t.status = 'Active'"];
$params = [];

if (!empty($destination)) {
    $where_conditions[] = "l.id = ?";
    $params[] = $destination;
}

if (!empty($package_type)) {
    $where_conditions[] = "p.package_type = ?";
    $params[] = $package_type;
}

if (!empty($departure_date)) {
    $where_conditions[] = "t.departure_date >= ?";
    $params[] = $departure_date;
}

if (!empty($duration)) {
    switch($duration) {
        case '1-2':
            $where_conditions[] = "p.duration_days BETWEEN 1 AND 2";
            break;
        case '3-4':
            $where_conditions[] = "p.duration_days BETWEEN 3 AND 4";
            break;
        case '5+':
            $where_conditions[] = "p.duration_days >= 5";
            break;
    }
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

$query = "SELECT 
            t.id, t.tour_name, t.departure_date, t.return_date, t.available_seats, t.status,
            p.package_name, p.package_type, p.price, p.duration_days, p.description,
            l.name as location_name,
            bt.type_name as bus_type, bt.amenities
          FROM tours t
          LEFT JOIN packages p ON t.package_id = p.id
          LEFT JOIN locations l ON p.location_id = l.id
          LEFT JOIN bus_types bt ON t.bus_type_id = bt.id
          WHERE $where_clause
          ORDER BY t.departure_date ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);

// Get locations for filter
$locations_query = "SELECT * FROM locations ORDER BY name";
$locations_stmt = $db->prepare($locations_query);
$locations_stmt->execute();

// Count results
$count_query = "SELECT COUNT(*) as total FROM tours t
                LEFT JOIN packages p ON t.package_id = p.id
                LEFT JOIN locations l ON p.location_id = l.id
                WHERE $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Myanmar Tourism</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .tour-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        .search-filters {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .filter-tag {
            display: inline-block;
            background: #e9ecef;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            margin: 0.25rem;
            font-size: 0.875rem;
        }
        .no-results {
            text-align: center;
            padding: 3rem 0;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="tours.php">
                    <i class="fas fa-list me-1"></i>All Tours
                </a>
            </div>
        </div>
    </nav>

    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">Search Results</h1>
                    <p class="lead mb-0">Found <?php echo $total_results; ?> tour<?php echo $total_results != 1 ? 's' : ''; ?> matching your criteria</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="index.php" class="btn btn-light btn-lg">
                        <i class="fas fa-search me-2"></i>New Search
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <!-- Active Filters -->
        <?php if (!empty($destination) || !empty($package_type) || !empty($departure_date) || !empty($duration) || !empty($min_price) || !empty($max_price)) { ?>
        <div class="search-filters">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Active Filters:</h5>
            <div class="d-flex flex-wrap align-items-center">
                <?php if (!empty($destination)) { 
                    $location_names = ['1' => 'Bagan', '2' => 'Hpaan', '3' => 'Taung Gyi'];
                ?>
                    <span class="filter-tag">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?php echo $location_names[$destination]; ?>
                    </span>
                <?php } ?>
                
                <?php if (!empty($package_type)) { ?>
                    <span class="filter-tag">
                        <i class="fas fa-bed me-1"></i>
                        <?php echo $package_type; ?> Occupancy
                    </span>
                <?php } ?>
                
                <?php if (!empty($departure_date)) { ?>
                    <span class="filter-tag">
                        <i class="fas fa-calendar me-1"></i>
                        From <?php echo date('M d, Y', strtotime($departure_date)); ?>
                    </span>
                <?php } ?>
                
                <?php if (!empty($duration)) { ?>
                    <span class="filter-tag">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo $duration; ?> Days
                    </span>
                <?php } ?>
                
                <?php if (!empty($min_price) || !empty($max_price)) { ?>
                    <span class="filter-tag">
                        <i class="fas fa-dollar-sign me-1"></i>
                        $<?php echo $min_price ?: '0'; ?> - $<?php echo $max_price ?: '∞'; ?>
                    </span>
                <?php } ?>
                
                <a href="search-tours.php" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="fas fa-times me-1"></i>Clear All
                </a>
            </div>
        </div>
        <?php } ?>

        <!-- Search Results -->
        <?php if ($total_results > 0) { ?>
        <div class="row g-4">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $badge_class = $package_type == 'Single' ? 'bg-warning' : 'bg-info';
                $departure_formatted = date('M d, Y', strtotime($departure_date));
                $return_formatted = date('M d, Y', strtotime($return_date));
                
                // Calculate days until departure
                $days_until = ceil((strtotime($departure_date) - time()) / (60 * 60 * 24));
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card tour-card h-100 position-relative">
                    <span class="badge <?php echo $badge_class; ?> package-badge"><?php echo $package_type; ?></span>
                    
                    <?php if ($days_until <= 7 && $days_until > 0) { ?>
                    <span class="badge bg-danger position-absolute" style="top: 10px; left: 10px; z-index: 1;">
                        <i class="fas fa-clock me-1"></i>Departing Soon
                    </span>
                    <?php } ?>
                    
                    <img src="/placeholder.svg?height=200&width=350" class="card-img-top" alt="<?php echo $tour_name; ?>">
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
                        
                        <?php if ($days_until > 0) { ?>
                        <p class="card-text">
                            <small class="text-info">
                                <i class="fas fa-hourglass-half me-1"></i>Departing in <?php echo $days_until; ?> day<?php echo $days_until != 1 ? 's' : ''; ?>
                            </small>
                        </p>
                        <?php } ?>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-primary mb-0">$<?php echo number_format($price, 2); ?></span>
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i><?php echo $available_seats; ?> seats left
                                </small>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="tour-details.php?id=<?php echo $id; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <a href="tour-details.php?id=<?php echo $id; ?>#booking" class="btn btn-outline-success">
                                    <i class="fas fa-credit-card me-2"></i>Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <!-- Search Tips -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5><i class="fas fa-lightbulb text-warning me-2"></i>Search Tips</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Try different date ranges for more options</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Consider both Single and Double occupancy</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Book early for better availability</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Check our featured destinations for inspiration</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } else { ?>
        <!-- No Results -->
        <div class="no-results">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h3>No tours found</h3>
            <p class="text-muted mb-4">We couldn't find any tours matching your search criteria.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Try these suggestions:</h5>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Expand your date range</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Consider different destinations</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Adjust your price range</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Try both Single and Double occupancy</li>
                            </ul>
                            <div class="mt-3">
                                <a href="index.php" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-2"></i>New Search
                                </a>
                                <a href="tours.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>View All Tours
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
