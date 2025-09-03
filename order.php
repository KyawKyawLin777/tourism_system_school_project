<?php
// Start session
session_start();
include_once 'config/database.php';
include_once 'classes/Tour.php';

$database = new Database();
$db = $database->getConnection();
$current_page = basename($_SERVER['PHP_SELF']); // index.php, tours.php, etc.

// Get logged-in customer id
$customer_id = $_SESSION['customer_id'] ?? null;

try {
  if ($customer_id) {
    $stmt = $db->prepare("
            SELECT b.*, c.full_name, c.email, c.phone, t.tour_name, t.departure_date, t.return_date
            FROM bookings b
            LEFT JOIN customers c ON b.customer_id = c.id
            LEFT JOIN tours t ON b.tour_id = t.id
            WHERE b.customer_id = ?
            ORDER BY b.booking_date DESC
        ");
    $stmt->execute([$customer_id]);
    $all_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $all_bookings = [];
  }
} catch (Exception $e) {
  $all_bookings = [];
}
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

    #previewImage {
      max-width: 100%;
      max-height: 300px;
      margin-top: 10px;
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

  <!-- Bookings Table -->
  <div class="container-fluid my-5">
    <h3 class="mb-4">All Bookings</h3>
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Reference</th>
            <th>Customer</th>
            <th>Tour</th>
            <th>Passengers</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Message</th>
            <th>Date</th>
            <th>Payment Method</th>
            <th>Payment Image</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_bookings)): ?>
            <tr>
              <td colspan="9" class="text-center text-muted py-4">No bookings found</td>
            </tr>
          <?php else: ?>
            <?php foreach ($all_bookings as $b): ?>
              <tr>
                <td class="fw-bold"><?php echo htmlspecialchars($b['booking_reference']); ?></td>
                <td>
                  <?php echo htmlspecialchars($b['full_name']); ?><br>
                  <small class="text-muted"><?php echo htmlspecialchars($b['email']); ?></small>
                </td>
                <td>
                  <?php echo htmlspecialchars($b['tour_name']); ?><br>
                  <small class="text-muted">
                    <?php echo date('M d, Y', strtotime($b['departure_date'])); ?>
                    <?php if ($b['return_date']): ?> - <?php echo date('M d, Y', strtotime($b['return_date'])); ?><?php endif; ?>
                  </small>
                </td>
                <td><?php echo intval($b['number_of_passengers']); ?></td>
                <td class="text-end"><?php echo number_format($b['total_amount'], 2); ?> MMK</td>
                <td>
                  <?php if ($b['booking_status'] == 'Pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif ($b['booking_status'] == 'Confirmed'): ?>
                    <span class="badge bg-success">Confirmed</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Reject</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($b['admin_notes'] ?? ''); ?>
                </td>
                <td><?php echo date('M d, Y H:i', strtotime($b['booking_date'])); ?></td>
                <td><?php echo htmlspecialchars($b['payment_method']); ?></td>
                <td>
                  <img src="<?php echo htmlspecialchars($b['payment_image']); ?>"
                    class="img-fluid rounded shadow-sm"
                    alt="Payment Image"
                    style="max-height: 80px; object-fit: cover;">
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function previewImage(event, id) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('preview' + id);
        output.src = reader.result;
        output.style.display = "block";
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>

</html>