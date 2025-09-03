<?php
class Booking
{
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $booking_reference;
    public $customer_id;
    public $tour_id;
    public $number_of_passengers;
    public $total_amount;
    public $booking_status;
    public $payment_status;
    public $created_at;
    public $payment_image;
    public $payment_method;
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        try {
            // handle file upload first (if image exists)
            if (!empty($this->payment_image) && isset($_FILES['payment_image'])) {
                $uploadDir = __DIR__ . "/../uploads/payments/"; // folder path
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // create folder if not exist
                }

                $fileName = time() . "_" . basename($_FILES['payment_image']['name']);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['payment_image']['tmp_name'], $filePath)) {
                    // only save filename or relative path into DB
                    $this->payment_image = "uploads/payments/" . $fileName;
                } else {
                    throw new Exception("Failed to upload payment image.");
                }
            }

            $query = "INSERT INTO " . $this->table_name . "
                SET booking_reference=:booking_reference, 
                    customer_id=:customer_id, 
                    tour_id=:tour_id,
                    number_of_passengers=:number_of_passengers, 
                    total_amount=:total_amount,
                    payment_image=:payment_image,
                    payment_method=:payment_method";

            $stmt = $this->conn->prepare($query);

            // Generate unique booking reference
            $this->booking_reference = 'TUR' . date('Ymd') . rand(1000, 9999);
            while ($this->referenceExists($this->booking_reference)) {
                $this->booking_reference = 'TUR' . date('Ymd') . rand(1000, 9999);
            }

            // Bind params
            $stmt->bindParam(":booking_reference", $this->booking_reference);
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":tour_id", $this->tour_id);
            $stmt->bindParam(":number_of_passengers", $this->number_of_passengers);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":payment_image", $this->payment_image);
            $stmt->bindParam(":payment_method", $this->payment_method);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Booking creation error: " . $e->getMessage());
            return false;
        }
    }



    private function referenceExists($reference)
    {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE booking_reference = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $reference);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateSeats($tour_id, $seats_booked)
    {
        try {
            $query = "UPDATE tours SET available_seats = available_seats - ? WHERE id = ? AND available_seats >= ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $seats_booked);
            $stmt->bindParam(2, $tour_id);
            $stmt->bindParam(3, $seats_booked);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Seat update error: " . $e->getMessage());
            return false;
        }
    }

    public function read()
    {
        $query = "SELECT 
                    b.id, b.booking_reference, b.number_of_passengers, b.total_amount, 
                    b.booking_status, b.payment_status, b.created_at,
                    c.full_name, c.email, c.phone,
                    t.tour_name, t.departure_date, t.return_date,
                    p.package_name, l.name as location_name
                  FROM " . $this->table_name . " b
                  LEFT JOIN customers c ON b.customer_id = c.id
                  LEFT JOIN tours t ON b.tour_id = t.id
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getLatestBooking()
    {
        $query = "
            SELECT b.*, c.full_name, c.email, c.phone, c.address
            FROM " . $this->table_name . " b
            JOIN customers c ON b.customer_id = c.id
            ORDER BY b.id DESC
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: false;
    }

    public function readOne()
    {
        $query = "SELECT 
                    b.id, b.booking_reference, b.customer_id, b.tour_id, b.number_of_passengers, 
                    b.total_amount, b.booking_status, b.payment_status, b.created_at,
                    c.full_name, c.email, c.phone, c.address,
                    t.tour_name, t.departure_date, t.return_date,
                    p.package_name, p.package_type, p.price, l.name as location_name
                  FROM " . $this->table_name . " b
                  LEFT JOIN customers c ON b.customer_id = c.id
                  LEFT JOIN tours t ON b.tour_id = t.id
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  WHERE b.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->booking_reference = $row['booking_reference'];
            $this->customer_id = $row['customer_id'];
            $this->tour_id = $row['tour_id'];
            $this->number_of_passengers = $row['number_of_passengers'];
            $this->total_amount = $row['total_amount'];
            $this->booking_status = $row['booking_status'];
            $this->payment_status = $row['payment_status'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        return false;
    }

    public function update()
    {
        try {
            $query = "UPDATE " . $this->table_name . "
                    SET booking_status=:booking_status, payment_status=:payment_status
                    WHERE id=:id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":booking_status", $this->booking_status);
            $stmt->bindParam(":payment_status", $this->payment_status);
            $stmt->bindParam(":id", $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Booking update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete()
    {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Booking deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function count()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getTotalRevenue()
    {
        $query = "SELECT SUM(total_amount) as total_revenue FROM " . $this->table_name . " WHERE payment_status = 'Paid'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_revenue'] ?? 0;
    }
}
