<?php
session_start();
include_once 'config/database.php';

// Create DB connection
$database = new Database();
$db = $database->getConnection(); // ✅ This is PDO

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $booking_id = $_POST['booking_id'] ?? null;

  if (!$booking_id) {
    die("Invalid booking request.");
  }

  // Validate file
  if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['payment_image']['tmp_name'];
    $fileName = $_FILES['payment_image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExtension, $allowedExtensions)) {
      die("Invalid file type. Only JPG, PNG, GIF allowed.");
    }

    // ✅ Check if old image exists for this booking
    $stmt = $db->prepare("SELECT payment_image 
                          FROM bookings 
                          WHERE id = :id AND customer_id = :customer_id");
    $stmt->execute([
      ':id' => $booking_id,
      ':customer_id' => $_SESSION['customer_id']
    ]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($old && !empty($old['payment_image'])) {
      $oldPath = __DIR__ . '/' . $old['payment_image'];
      if (file_exists($oldPath)) {
        unlink($oldPath); // ✅ delete old image
      }
    }

    // Rename new file
    $newFileName = uniqid("pay_", true) . '.' . $fileExtension;

    // Upload path
    $uploadDir = __DIR__ . '/uploads/payments/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
      // Save relative path to DB
      $relativePath = 'uploads/payments/' . $newFileName;

      // ✅ Update DB with new image
      $stmt = $db->prepare("UPDATE bookings 
                              SET payment_image = :payment_image, 
                                  payment_status = 'Pending' 
                            WHERE id = :id AND customer_id = :customer_id");
      $success = $stmt->execute([
        ':payment_image' => $relativePath,
        ':id' => $booking_id,
        ':customer_id' => $_SESSION['customer_id']
      ]);

      if ($success) {
        $_SESSION['success_message'] = "Payment image uploaded successfully. Awaiting admin approval.";
        header("Location: order.php");
        exit();
      } else {
        $errorInfo = $stmt->errorInfo();
        die("Database update failed: " . $errorInfo[2]);
      }
    } else {
      die("File upload failed.");
    }
  } else {
    die("Please select a valid image file.");
  }
} else {
  die("Invalid request.");
}
