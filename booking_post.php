<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Required fields
    $required_fields = ['full_name', 'email', 'phone', 'address', 'tour_id', 'passengers', 'price', 'payment_method'];
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

    // Sanitize inputs
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
      throw new Exception("Not enough seats. Only " . $tour_details['available_seats'] . " left.");
    }

    // Check if customer exists
    $existing_customer = $customer->emailExists();
    if (!$existing_customer) {
      if (!$customer->create()) {
        throw new Exception("Failed to create customer.");
      }
    } else {
      $customer->id = $existing_customer['id'];
    }

    // Validate payment image
    if (!isset($_FILES['payment_image']) || $_FILES['payment_image']['error'] != 0) {
      throw new Exception("Payment image is required.");
    }

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($_FILES['payment_image']['type'], $allowed_types)) {
      throw new Exception("Invalid image type. Only JPG and PNG allowed.");
    }

    if ($_FILES['payment_image']['size'] > 2 * 1024 * 1024) {
      throw new Exception("Image size too large. Max 2MB allowed.");
    }

    // Set booking details
    $booking->customer_id = $customer->id;
    $booking->tour_id = $tour_id;
    $booking->number_of_passengers = $passengers;
    $booking->total_amount = $total_amount;
    $booking->payment_method = htmlspecialchars($_POST['payment_method']);
    $booking->payment_image = $_FILES['payment_image'];

    // Create booking
    if ($booking->create()) {
      if ($booking->updateSeats($tour_id, $passengers)) {
        $booking_reference = $booking->booking_reference;

        // Get booking status
        $stmt = $db->prepare("SELECT booking_status FROM bookings WHERE booking_reference = ?");
        $stmt->execute([$booking_reference]);
        $status_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $booking_status = $status_row['booking_status'] ?? 'Pending';

        // Redirect to booking.php with success message and booking reference
        header("Location: booking.php?success=1&reference={$booking_reference}&status={$booking_status}");
        exit(); // always call exit after header redirect
      } else {
        $error_message = "Booking created but failed to update seats.";
      }
    } else {
      $error_message = "Failed to create booking.";
    }
  } catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Booking Error: " . $e->getMessage());
  }
}
