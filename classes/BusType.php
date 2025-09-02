<?php
class BusType {
    private $conn;
    private $table_name = "bus_types";

    public $id;
    public $type_name;
    public $capacity;
    public $amenities;
    public $price_per_km;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET type_name=:type_name, capacity=:capacity, 
                    amenities=:amenities, price_per_km=:price_per_km";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":type_name", $this->type_name);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":price_per_km", $this->price_per_km);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY type_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->type_name = $row['type_name'];
            $this->capacity = $row['capacity'];
            $this->amenities = $row['amenities'];
            $this->price_per_km = $row['price_per_km'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET type_name=:type_name, capacity=:capacity, 
                    amenities=:amenities, price_per_km=:price_per_km
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":type_name", $this->type_name);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":price_per_km", $this->price_per_km);
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
}
?>
