<?php
class PackageRegistration {
    private $conn;
    private $table_name = "package_registrations";

    public $id;
    public $customer_id;
    public $package_id;
    public $tour_id;
    public $registration_date;
    public $preferred_date;
    public $number_of_passengers;
    public $special_requirements;
    public $registration_status;
    public $admin_notes;
    public $processed_by;
    public $processed_at;
    public $total_estimated_cost;
    public $payment_status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    SET customer_id=:customer_id, 
                        package_id=:package_id, 
                        tour_id=:tour_id,
                        preferred_date=:preferred_date,
                        number_of_passengers=:number_of_passengers,
                        special_requirements=:special_requirements,
                        total_estimated_cost=:total_estimated_cost";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
            $this->package_id = htmlspecialchars(strip_tags($this->package_id));
            $this->tour_id = htmlspecialchars(strip_tags($this->tour_id));
            $this->preferred_date = htmlspecialchars(strip_tags($this->preferred_date));
            $this->number_of_passengers = htmlspecialchars(strip_tags($this->number_of_passengers));
            $this->special_requirements = htmlspecialchars(strip_tags($this->special_requirements));
            $this->total_estimated_cost = htmlspecialchars(strip_tags($this->total_estimated_cost));

            // Bind data
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":package_id", $this->package_id);
            $stmt->bindParam(":tour_id", $this->tour_id);
            $stmt->bindParam(":preferred_date", $this->preferred_date);
            $stmt->bindParam(":number_of_passengers", $this->number_of_passengers);
            $stmt->bindParam(":special_requirements", $this->special_requirements);
            $stmt->bindParam(":total_estimated_cost", $this->total_estimated_cost);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Package registration creation error: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        $query = "SELECT pr.*, c.full_name, c.email, c.phone, 
                         p.package_name, p.package_type,
                         t.tour_name, t.departure_date, t.return_date, t.price,
                         l.location_name,
                         a.username as processed_by_name
                  FROM " . $this->table_name . " pr
                  LEFT JOIN customers c ON pr.customer_id = c.id
                  LEFT JOIN packages p ON pr.package_id = p.id
                  LEFT JOIN tours t ON pr.tour_id = t.id
                  LEFT JOIN locations l ON t.location_id = l.id
                  LEFT JOIN admins a ON pr.processed_by = a.id
                  ORDER BY pr.registration_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCustomer($customer_id) {
        $query = "SELECT pr.*, 
                         p.package_name, p.package_type,
                         t.tour_name, t.departure_date, t.return_date, t.price,
                         l.location_name
                  FROM " . $this->table_name . " pr
                  LEFT JOIN packages p ON pr.package_id = p.id
                  LEFT JOIN tours t ON pr.tour_id = t.id
                  LEFT JOIN locations l ON t.location_id = l.id
                  WHERE pr.customer_id = ?
                  ORDER BY pr.registration_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $customer_id);
        $stmt->execute();
        return $stmt;
    }

    public function readPending() {
        $query = "SELECT pr.*, c.full_name, c.email, c.phone, 
                         p.package_name, p.package_type,
                         t.tour_name, t.departure_date, t.return_date, t.price,
                         l.location_name
                  FROM " . $this->table_name . " pr
                  LEFT JOIN customers c ON pr.customer_id = c.id
                  LEFT JOIN packages p ON pr.package_id = p.id
                  LEFT JOIN tours t ON pr.tour_id = t.id
                  LEFT JOIN locations l ON t.location_id = l.id
                  WHERE pr.registration_status = 'Pending'
                  ORDER BY pr.registration_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $status, $admin_notes = '', $admin_id = null) {
        try {
            $query = "UPDATE " . $this->table_name . "
                    SET registration_status=:status, 
                        admin_notes=:admin_notes,
                        processed_by=:admin_id,
                        processed_at=NOW()
                    WHERE id=:id";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":admin_notes", $admin_notes);
            $stmt->bindParam(":admin_id", $admin_id);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Package registration status update error: " . $e->getMessage());
            return false;
        }
    }

    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN registration_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN registration_status = 'Approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN registration_status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN registration_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                      FROM " . $this->table_name;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Package registration stats error: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0];
        }
    }

    public function delete() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Package registration deletion error: " . $e->getMessage());
            return false;
        }
    }
}
?>
