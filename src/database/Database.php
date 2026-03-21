<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli('localhost', 'admin', 'fantasyFencing', 'fencing');
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function execute($query, $types = "", $params = []) {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->connection->error);
        
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function fetchOne($query, $types = "", $params = []) {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->connection->error);

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result ? $result->fetch_assoc() : null;
        
        $stmt->close();
        return $data;
    }

    public function fetchAll($query, $types = "", $params = []) {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->connection->error);

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        $stmt->close();
        return $data;
    }
    
    public function fetchColumn($query, $types = "", $params = []) {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->connection->error);

        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_row() : null;
        
        $stmt->close();
        return $row ? $row[0] : null;
    }
}
?>