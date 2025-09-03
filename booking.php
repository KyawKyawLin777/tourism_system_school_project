<?php
session_start();
include_once 'config/database.php';
include_once 'classes/Booking.php';

$database = new Database();
$db = $database->getConnection();

$bookingObj = new Booking($db);
$latestBooking = $bookingObj->getLatestBooking();

if (!$latestBooking) {
    die("No booking found in database.");
}

// Assign variables safely
$full_name = $latestBooking['full_name'] ?? '';
$email = $latestBooking['email'] ?? '';
$phone = $latestBooking['phone'] ?? '';
$address = $latestBooking['address'] ?? '';
$tour_id = $latestBooking['tour_id'] ?? '';
$passengers = $latestBooking['number_of_passengers'] ?? 0;
$price_per_person = $passengers ? ($latestBooking['total_amount'] / $passengers) : 0;
$total_amount = $latestBooking['total_amount'] ?? 0;
$payment_method = $latestBooking['payment_method'] ?? 'Not Specified';
$booking_reference = $latestBooking['booking_reference'] ?? '';
$booking_status = $latestBooking['booking_status'] ?? 'Pending';
$payment_image_url = $latestBooking['payment_image'] ?? null;

if (isset($booking_status)) {
    if ($booking_status === 'Pending') {
        // Booking is pending
        $success_message = "Your booking is pending. Reference: " . $booking_reference;
    } elseif ($booking_status === 'Confirmed') {
        // Booking is confirmed
        $success_message = "Your booking is confirmed! Reference: " . $booking_reference;
    } else {
        // Some other status
        $success_message = "Your booking status: " . $booking_status . ". Reference: " . $booking_reference;
    }
} else {
    $success_message = "Booking reference: " . $booking_reference;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow border-0">
                    <div class="card-header text-white text-center py-4 bg-info">
                        <h2><i class="fas fa-info-circle me-2"></i>Booking Receipt</h2>
                    </div>
                    <div class="card-body py-5">
                        <div class="text-center mb-5">
                            <i class="fas fa-ticket-alt mb-3" style="font-size:4rem;color:#0d6efd;"></i>
                            <h4 class="fw-bold text-primary"><?php echo $success_message; ?></h4>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3"><i class="fas fa-user me-2"></i>Customer Details</h5>
                                        <p><strong>Name:</strong> <?php echo $full_name; ?></p>
                                        <p><strong>Email:</strong> <?php echo $email; ?></p>
                                        <p><strong>Phone:</strong> <?php echo $phone; ?></p>
                                        <p><strong>Address:</strong> <?php echo $address; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3"><i class="fas fa-map-marker-alt me-2"></i>Booking Information</h5>
                                        <p><strong>Reference:</strong> <span class="badge bg-primary"><?php echo $booking_reference; ?></span></p>
                                        <p><strong>Passengers:</strong> <?php echo $passengers; ?></p>
                                        <p><strong>Price per Person:</strong> <?php echo number_format($price_per_person, 2); ?> MMK</p>
                                        <p><strong>Total Amount:</strong> <span class="text-success fw-bold"><?php echo number_format($total_amount, 2); ?> MMK</span></p>
                                        <p><strong>Payment Method:</strong> <?php echo $payment_method; ?></p>
                                        <?php if ($payment_image_url) { ?>
                                            <p><strong>Payment Image:</strong></p>
                                            <img src="<?php echo $payment_image_url; ?>" class="img-fluid rounded" style="max-height:150px;">
                                        <?php } ?>
                                        <p><strong>Status:</strong>
                                            <?php
                                            if ($booking_status === 'Pending') {
                                                echo '<span class="badge bg-warning">Pending</span>';
                                            } elseif ($booking_status === 'Confirmed') {
                                                echo '<span class="badge bg-success">Confirmed</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">' . htmlspecialchars($booking_status) . '</span>';
                                            }
                                            ?>
                                        </p>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                            <a href="index.php" class="btn btn-primary btn-lg"><i class="fas fa-home me-2"></i>Back to Home</a>
                            <a href="order.php" class="btn btn-success btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Order Check
                            </a>

                            <a href="tours.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-search me-2"></i>Browse Tours</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>