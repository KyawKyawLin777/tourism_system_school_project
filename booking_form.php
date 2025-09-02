<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Tour.php';

// Check login
if (!isset($_SESSION['customer_id'])) {
  $redirect = "booking_form.php?tour_id=" . urlencode($_GET['tour_id']);
  header("Location: customer/login.php?redirect=" . urlencode($redirect));
  exit();
}

$current_page = basename($_SERVER['PHP_SELF']); // index.php, tours.php, etc.


// Get tour_id
if (!isset($_GET['tour_id'])) {
  die("ERROR: Tour ID not provided.");
}

$tour_id = $_GET['tour_id'];

$database = new Database();
$db = $database->getConnection();

$tour = new Tour($db);
$tour->id = $tour_id;

$tour_details = $tour->readOne();
if (!$tour_details) {
  die("ERROR: Tour not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book <?php echo htmlspecialchars($tour_details['tour_name']); ?> - Myanmar Tourism</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .tour-image {
      max-height: 200px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 15px;
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
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Book This Tour</h4>
          </div>
          <div class="card-body">

            <!-- Tour Image -->
            <?php if (!empty($tour_details['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($tour_details['image_url']); ?>" alt="<?php echo htmlspecialchars($tour_details['tour_name']); ?>" class="tour-image w-100">
            <?php endif; ?>

            <!-- Tour Name & Package -->
            <h3 class="text-center"><?php echo htmlspecialchars($tour_details['tour_name']); ?></h3>
            <p class="text-center">
              <span class="badge bg-<?php echo $tour_details['package_type'] == 'Single' ? 'warning' : 'info'; ?>">
                <?php echo htmlspecialchars($tour_details['package_type']); ?> Package
              </span>
            </p>

            <div class="text-center mb-4">
              <h2 class="text-primary">$<?php echo number_format($tour_details['price'], 2); ?></h2>
              <p class="text-muted">per person</p>
            </div>

            <form action="booking.php" method="POST">
              <input type="hidden" name="tour_id" value="<?php echo $tour_details['id']; ?>">
              <input type="hidden" name="price" value="<?php echo $tour_details['price']; ?>">

              <div class="mb-3">
                <label for="passengers" class="form-label">Number of Passengers</label>
                <select class="form-select" id="passengers" name="passengers" required>
                  <option value="">Select passengers</option>
                  <?php for ($i = 1; $i <= min(10, $tour_details['available_seats']); $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Person' : 'People'; ?></option>
                  <?php endfor; ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['customer_name']); ?>" required>
                <input type="hidden" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['customer_id']); ?>" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['customer_email']); ?>" required>
              </div>

              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
              </div>

              <div class="mb-4">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="fas fa-credit-card me-2"></i>Book Now
                </button>
              </div>
            </form>

            <div class="mt-3 text-center">
              <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>Secure booking • No hidden fees
              </small>
            </div>

          </div>
        </div>
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