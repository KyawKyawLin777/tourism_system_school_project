<?php
class Tour {
    private $conn;
    private $table_name = "tours";

    public $id;
    public $tour_name;
    public $package_id;
    public $bus_type_id;
    public $departure_date;
    public $return_date;
    public $available_seats;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT 
                    t.id, t.tour_name, t.departure_date, t.return_date, t.available_seats, t.status,
                    p.package_name, p.package_type, p.price, p.duration_days, p.image_url,
                    l.name as location_name,
                    bt.type_name as bus_type, bt.amenities
                  FROM " . $this->table_name . " t
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  LEFT JOIN bus_types bt ON t.bus_type_id = bt.id
                  WHERE t.status = 'Active'
                  ORDER BY t.departure_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT 
                    t.id, t.tour_name, t.departure_date, t.return_date, t.available_seats, t.status,
                    p.package_name, p.package_type, p.price, p.duration_days, p.description, p.image_url, p.includes,
                    l.name as location_name, l.description as location_description,
                    bt.type_name as bus_type, bt.amenities, bt.capacity
                  FROM " . $this->table_name . " t
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  LEFT JOIN bus_types bt ON t.bus_type_id = bt.id
                  WHERE t.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->tour_name = $row['tour_name'];
            $this->departure_date = $row['departure_date'];
            $this->return_date = $row['return_date'];
            $this->available_seats = $row['available_seats'];
            return $row;
        }
        return false;
    }

    public function getByLocation($location_id) {
        $query = "SELECT 
                    t.id, t.tour_name, t.departure_date, t.return_date, t.available_seats,
                    p.package_name, p.package_type, p.price, p.duration_days, p.image_url,
                    l.name as location_name,
                    bt.type_name as bus_type
                  FROM " . $this->table_name . " t
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  LEFT JOIN bus_types bt ON t.bus_type_id = bt.id
                  WHERE l.id = ? AND t.status = 'Active'
                  ORDER BY t.departure_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $location_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET tour_name=:tour_name, package_id=:package_id, 
                    bus_type_id=:bus_type_id, departure_date=:departure_date, 
                    return_date=:return_date, available_seats=:available_seats";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":tour_name", $this->tour_name);
        $stmt->bindParam(":package_id", $this->package_id);
        $stmt->bindParam(":bus_type_id", $this->bus_type_id);
        $stmt->bindParam(":departure_date", $this->departure_date);
        $stmt->bindParam(":return_date", $this->return_date);
        $stmt->bindParam(":available_seats", $this->available_seats);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET tour_name=:tour_name, package_id=:package_id, 
                    bus_type_id=:bus_type_id, departure_date=:departure_date, 
                    return_date=:return_date, available_seats=:available_seats, 
                    status=:status WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":tour_name", $this->tour_name);
        $stmt->bindParam(":package_id", $this->package_id);
        $stmt->bindParam(":bus_type_id", $this->bus_type_id);
        $stmt->bindParam(":departure_date", $this->departure_date);
        $stmt->bindParam(":return_date", $this->return_date);
        $stmt->bindParam(":available_seats", $this->available_seats);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT 
                    t.id, t.tour_name, t.departure_date, t.return_date, t.available_seats, t.status,
                    p.package_name, p.package_type, p.price, p.duration_days,
                    l.name as location_name,
                    bt.type_name as bus_type, bt.amenities
                  FROM " . $this->table_name . " t
                  LEFT JOIN packages p ON t.package_id = p.id
                  LEFT JOIN locations l ON p.location_id = l.id
                  LEFT JOIN bus_types bt ON t.bus_type_id = bt.id
                  ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
