<?php
class Package {
    private $conn;
    private $table_name = "packages";

    public $id;
    public $package_name;
    public $package_type;
    public $location_id;
    public $duration_days;
    public $price;
    public $description;
    public $includes;
    public $image_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET package_name=:package_name, package_type=:package_type, 
                    location_id=:location_id, duration_days=:duration_days, 
                    price=:price, description=:description, includes=:includes, 
                    image_url=:image_url";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":package_name", $this->package_name);
        $stmt->bindParam(":package_type", $this->package_type);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":duration_days", $this->duration_days);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":includes", $this->includes);
        $stmt->bindParam(":image_url", $this->image_url);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT p.*, l.name as location_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN locations l ON p.location_id = l.id
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT p.*, l.name as location_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN locations l ON p.location_id = l.id
                  WHERE p.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->package_name = $row['package_name'];
            $this->package_type = $row['package_type'];
            $this->location_id = $row['location_id'];
            $this->duration_days = $row['duration_days'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->includes = $row['includes'];
            $this->image_url = $row['image_url'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET package_name=:package_name, package_type=:package_type, 
                    location_id=:location_id, duration_days=:duration_days, 
                    price=:price, description=:description, includes=:includes, 
                    image_url=:image_url WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":package_name", $this->package_name);
        $stmt->bindParam(":package_type", $this->package_type);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":duration_days", $this->duration_days);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":includes", $this->includes);
        $stmt->bindParam(":image_url", $this->image_url);
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
