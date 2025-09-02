<?php
include_once 'config/database.php';
include_once 'classes/Tour.php';

$database = new Database();
$db = $database->getConnection();

$tour = new Tour($db);
$stmt = $tour->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Myanmar Tourism - Explore Beautiful Destinations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://www.visitbritain.com/sites/cms/files/styles/hero_header_xl_2x/public/lookatmedam/211d1fa8-bb04-4d34-b96e-c75e367c7ceal.jpg.webp?h=0e69f19d&itok=8kp5-hD7?height=600&width=1200');
            background-size: cover;
            background-position: center;
            height: 70vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .destination-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .destination-card:hover {
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
    
                    <li class="nav-item">
                        <a class="nav-link" href="tours.php">Tours</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="admin/admins.php">Admin</a>
                    </li>
             
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Discover Myanmar's Hidden Gems</h1>
            <p class="lead mb-4">Experience the beauty of Bagan, Hpaan, and Taung Gyi with our carefully crafted tour packages</p>
            <a href="tours.php" class="btn btn-warning btn-lg">
                <i class="fas fa-search me-2"></i>Explore Tours
            </a>
        </div>
    </section>

    <!-- Tour Search Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">
                                <i class="fas fa-search text-primary me-2"></i>Find Your Perfect Tour
                            </h3>
                            
                            <form method="GET" action="search-tours.php" id="tourSearchForm">
                                <div class="row g-3">
                                    <!-- Destination -->
                                    <div class="col-md-3">
                                        <label for="destination" class="form-label">
                                            <i class="fas fa-map-marker-alt text-primary me-1"></i>Destination
                                        </label>
                                        <select class="form-select" id="destination" name="destination">
                                            <option value="">All Destinations</option>
                                            <option value="1">Bagan</option>
                                            <option value="2">Hpaan</option>
                                            <option value="3">Taung Gyi</option>
                                            <option value="4">Mandalay</option>
                                             <option value="5">Ngapali Beach</option>
                                            <option value="6">Inle Lake</option>
                                            <option value="7">Mrauk U</option>
                                            <option value="8">Kyaiktiyo Pagoda</option>
                                            <option value="9">Naypyidaw</option>
                                             
                                        </select>
                                    </div>
                                    
                                    <!-- Package Type -->
                                    <div class="col-md-3">
                                        <label for="package_type" class="form-label">
                                            <i class="fas fa-bed text-primary me-1"></i>Package Type
                                        </label>
                                        <select class="form-select" id="package_type" name="package_type">
                                            <option value="">All Types</option>
                                            <option value="Single">Single Occupancy</option>
                                            <option value="Double">Double Occupancy</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Departure Date -->
                                    <div class="col-md-3">
                                        <label for="departure_date" class="form-label">
                                            <i class="fas fa-calendar text-primary me-1"></i>Departure Date
                                        </label>
                                        <input type="date" class="form-control" id="departure_date" name="departure_date" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    
                                    <!-- Duration -->
                                    <div class="col-md-3">
                                        <label for="duration" class="form-label">
                                            <i class="fas fa-clock text-primary me-1"></i>Duration
                                        </label>
                                        <select class="form-select" id="duration" name="duration">
                                            <option value="">Any Duration</option>
                                            <option value="1-2">1-2 Days</option>
                                            <option value="3-4">3-4 Days</option>
                                            <option value="5+">5+ Days</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Price Range -->
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label for="min_price" class="form-label">
                                            <i class="fas fa-dollar-sign text-primary me-1"></i>Min Price
                                        </label>
                                        <input type="number" class="form-control" id="min_price" name="min_price" placeholder="$0" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="max_price" class="form-label">
                                            <i class="fas fa-dollar-sign text-primary me-1"></i>Max Price
                                        </label>
                                        <input type="number" class="form-control" id="max_price" name="max_price" placeholder="$1000" min="0">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                                            <i class="fas fa-search me-2"></i>Search Tours
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Quick Filters -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <small class="text-muted">Quick Filters:</small>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm me-2 mb-2" onclick="quickFilter('weekend')">
                                                <i class="fas fa-calendar-weekend me-1"></i>This Weekend
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm me-2 mb-2" onclick="quickFilter('budget')">
                                                <i class="fas fa-coins me-1"></i>Budget Tours (Under 250 MMK)
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm me-2 mb-2" onclick="quickFilter('luxury')">
                                                <i class="fas fa-star me-1"></i>Luxury Tours ($MMK +)
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm me-2 mb-2" onclick="quickFilter('available')">
                                                <i class="fas fa-check-circle me-1"></i>Available Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-3">Featured Destinations</h2>
                    <p class="lead text-muted">Explore the most beautiful places in Myanmar</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/1a/f2/80/87/cows-and-keeper-lady.jpg?w=1400&h=-1&s=1" class="card-img-top" alt="Bagan">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-temple text-warning me-2"></i>Bagan
                            </h5>
                            <p class="card-text">Ancient city with thousands of pagodas and temples. Experience hot air balloon rides and rich cultural heritage.</p>
                            <a href="destination.php?location=1" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/11/43/22/86/p-20171113-155557-pn.jpg?w=2400&height=700&s=1" class="card-img-top" alt="Hpaan" height="210px">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-mountain text-success me-2"></i>Hpaan
                            </h5>
                            <p class="card-text">Capital of Kayin State, known for limestone caves, Buddhist monasteries, and scenic mountain views.</p>
                            <a href="destination.php?location=2" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/01/c2/11/24/pagoda-field-at-kakku.jpg?w=2400&h=1200&s=1" class="card-img-top" alt="Taung Gyi">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-water text-info me-2"></i>Taung Gyi
                            </h5>
                            <p class="card-text">Capital of Shan State with cool climate, near Inle Lake. Famous for traditional markets and highland culture.</p>
                            <a href="destination.php?location=3" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Available Tours -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold mb-3">Available Tours</h2>
                    <p class="lead text-muted">Choose from our carefully selected tour packages</p>
                </div>
            </div>
            <div class="row g-4">
                <?php
                // Make sure your $stmt query fetches the 'available_seats' column
                // For example, if your Tour class's read() method does a JOIN:
                // $stmt = $db->prepare("SELECT p.*, t.available_seats FROM packages p JOIN tours t ON p.id = t.package_id WHERE t.status = 'Active'");
                // $stmt->execute(); // If not already executed by the class method

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row); // This makes fields like $id, $package_name, $price, $available_seats available as variables
                    $badge_class = $package_type == 'Single' ? 'bg-warning' : 'bg-info';
                ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card destination-card h-100 position-relative">
                        <span class="badge <?php echo $badge_class; ?> package-badge"><?php echo htmlspecialchars($package_type); ?></span>

                        <img src="<?php echo htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package_name); ?>" style="height: 250px; object-fit: cover;">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($package_name); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($duration_days); ?> Days
                            </p>
                            <p class="card-text">
                                <?php echo htmlspecialchars($description); ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 text-primary mb-0"><?php echo number_format($price, 2); ?>  MMK</span>
                                    <!-- ADDED LINE for available seats -->
                                    <span class="h5 text-secondary mb-0">
                                        <i class="fas fa-chair me-1"></i><?php echo htmlspecialchars($available_seats); ?> Seats Left
                                    </span>
                                </div>
                                <a href="tour-details.php?id=<?php echo $id; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-4">
                <a href="tours.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-list me-2"></i>View All Tours
                </a>
            </div>
        </div>
    </section>


    <!-- Features -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <i class="fas fa-bus fa-3x text-primary"></i>
                    </div>
                    <h5>Comfortable Transportation</h5>
                    <p class="text-muted">Modern buses with AC and entertainment systems</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <i class="fas fa-bed fa-3x text-success"></i>
                    </div>
                    <h5>Quality Accommodation</h5>
                    <p class="text-muted">Single and double occupancy options available</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-tie fa-3x text-warning"></i>
                    </div>
                    <h5>Expert Guides</h5>
                    <p class="text-muted">Professional local guides with deep knowledge</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-info"></i>
                    </div>
                    <h5>Safe & Secure</h5>
                    <p class="text-muted">Your safety is our top priority</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
  <!-- Modern Gradient Footer -->
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
function quickFilter(type) {
    const form = document.getElementById('tourSearchForm');
    const today = new Date();
    
    // Reset form
    form.reset();
    
    switch(type) {
        case 'weekend':
            // Set date to next Saturday
            const nextSaturday = new Date(today);
            nextSaturday.setDate(today.getDate() + (6 - today.getDay()));
            document.getElementById('departure_date').value = nextSaturday.toISOString().split('T')[0];
            break;
            
        case 'budget':
            document.getElementById('max_price').value = '250';
            break;
            
        case 'luxury':
            document.getElementById('min_price').value = '400';
            break;
            
        case 'available':
            // Set departure date to today or tomorrow
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            document.getElementById('departure_date').value = tomorrow.toISOString().split('T')[0];
            break;
    }
    
    // Submit form
    form.submit();
}

// Auto-submit form when date changes
document.getElementById('departure_date').addEventListener('change', function() {
    if(this.value) {
        document.getElementById('tourSearchForm').submit();
    }
});
</script>
</body>
</html>
