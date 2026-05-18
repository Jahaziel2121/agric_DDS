<?php
class sqlite_mysqli_result {
    private $rows = [];
    private $index = 0;
    public $num_rows = 0;

    public function __construct($stmt) {
        if ($stmt) {
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($this->rows) {
                $this->num_rows = count($this->rows);
            }
        }
    }

    public function fetch_assoc() {
        if ($this->index < $this->num_rows) {
            return $this->rows[$this->index++];
        }
        return null;
    }
}

class sqlite_mysqli_stmt {
    private $pdo;
    private $stmt;
    public $insert_id;
    private $params = [];

    public function __construct($pdo, $query) {
        $this->pdo = $pdo;
        $this->stmt = $pdo->prepare($query);
    }

    public function bind_param($types, ...$vars) {
        $this->params = [];
        foreach ($vars as $i => &$var) {
            $this->params[] = &$var;
        }
    }

    public function execute() {
        if ($this->stmt) {
            $values = [];
            foreach ($this->params as $val) {
                $values[] = $val;
            }
            if (!empty($values)) {
                $res = $this->stmt->execute($values);
            } else {
                $res = $this->stmt->execute();
            }
            $this->insert_id = $this->pdo->lastInsertId();
            return $res;
        }
        return false;
    }

    public function get_result() {
        return new sqlite_mysqli_result($this->stmt);
    }
}

class sqlite_mysqli {
    private $pdo;
    public $connect_error = null;
    public $insert_id;
    public $error = null;

    public function __construct($filename) {
        try {
            $this->pdo = new PDO("sqlite:" . $filename);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign keys if needed
            $this->pdo->exec("PRAGMA foreign_keys = ON;");
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    public function query($query) {
        try {
            $upper = strtoupper(ltrim($query));
            if (strpos($upper, 'SELECT') === 0 || strpos($upper, 'SHOW') === 0 || strpos($upper, 'DESCRIBE') === 0 || strpos($upper, 'PRAGMA') === 0) {
                $stmt = $this->pdo->query($query);
                if ($stmt) {
                    return new sqlite_mysqli_result($stmt);
                }
                return false;
            } else {
                $res = $this->pdo->exec($query);
                $this->insert_id = $this->pdo->lastInsertId();
                return $res !== false ? true : false;
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function prepare($query) {
        try {
            return new sqlite_mysqli_stmt($this->pdo, $query);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    public function real_escape_string($string) {
        // Simple escape since PDO quote adds quotes we might not want in string concat
        return str_replace("'", "''", $string);
    }
}

$conn = new sqlite_mysqli(__DIR__ . '/database.sqlite');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>