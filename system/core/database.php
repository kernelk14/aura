<?php
/**
 * Database Class
 * Supports multiple database drivers
 */

namespace AuraCore;

class Database extends Base {
    protected $db;
    protected $query_builder;
    protected $driver;
    
    public function __construct($config = null) {
        if ($config === null) {
            $config = $this->config('database.default');
        }
        
        $this->driver = $config['driver'] ?? 'mysqli';
        
        switch ($this->driver) {
            case 'mysqli':
                $this->db = new mysqli(
                    $config['host'],
                    $config['username'],
                    $config['password'],
                    $config['database']
                );
                
                if ($this->db->connect_error) {
                    die("Connection failed: " . $this->db->connect_error);
                }
                
                $this->db->set_charset($config['charset'] ?? 'utf8');
                break;
                
            case 'pdo_mysql':
            case 'pdo_pgsql':
            case 'pdo_sqlite':
                $dsn = $config['dsn'] ?? $this->buildDsn($config);
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                
                $this->db = new PDO($dsn, $config['username'], $config['password'], $options);
                break;
                
            default:
                throw new Exception("Unsupported database driver: {$this->driver}");
        }
        
        $this->query_builder = new QueryBuilder($this->db, $this->driver);
    }
    
    private function buildDsn($config) {
        switch ($this->driver) {
            case 'pdo_mysql':
                return sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    $config['host'],
                    $config['database'],
                    $config['charset'] ?? 'utf8'
                );
                
            case 'pdo_pgsql':
                return sprintf(
                    'pgsql:host=%s;dbname=%s',
                    $config['host'],
                    $config['database']
                );
                
            case 'pdo_sqlite':
                return sprintf(
                    'sqlite:%s',
                    $config['database']
                );
                
            default:
                throw new Exception("Unsupported PDO driver: {$this->driver}");
        }
    }
    
    public function query($sql) {
        return $this->query_builder->query($sql);
    }
    
    public function get($table) {
        return $this->query_builder->get($table);
    }
    
    public function getWhere($table, $where) {
        return $this->query_builder->getWhere($table, $where);
    }
    
    public function insert($table, $data) {
        return $this->query_builder->insert($table, $data);
    }
    
    public function update($table, $data, $where = null) {
        return $this->query_builder->update($table, $data, $where);
    }
    
    public function delete($table, $where = null) {
        return $this->query_builder->delete($table, $where);
    }
    
    public function lastInsertId() {
        if ($this->driver === 'mysqli') {
            return $this->db->insert_id;
        } else {
            return $this->db->lastInsertId();
        }
    }
    
    public function affectedRows() {
        if ($this->driver === 'mysqli') {
            return $this->db->affected_rows;
        } else {
            // For PDO, we need to track this differently
            return $this->query_builder->getAffectedRows();
        }
    }
}

/**
 * Query Builder Class
 * Handles SQL query building and execution
 */
class QueryBuilder {
    protected $db;
    protected $driver;
    protected $affected_rows = 0;
    
    public function __construct($db, $driver) {
        $this->db = $db;
        $this->driver = $driver;
    }
    
    public function query($sql) {
        if ($this->driver === 'mysqli') {
            $result = $this->db->query($sql);
            $this->affected_rows = $this->db->affected_rows;
        } else {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll();
            $this->affected_rows = $stmt->rowCount();
        }
        
        return $result;
    }
    
    public function get($table) {
        if ($this->driver === 'mysqli') {
            $result = $this->db->query("SELECT * FROM `$table`");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $this->affected_rows = $result->num_rows;
        } else {
            $stmt = $this->db->prepare("SELECT * FROM {$table}");
            $stmt->execute();
            $data = $stmt->fetchAll();
            $this->affected_rows = $stmt->rowCount();
        }
        
        return $data;
    }
    
    public function getWhere($table, $where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $where_clause = implode(' AND ', $conditions);
        $sql = "SELECT * FROM `$table` WHERE {$where_clause}";
        
        if ($this->driver === 'mysqli') {
            $stmt = $this->db->prepare($sql);
            if ($params) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $this->affected_rows = $result->num_rows;
            $stmt->close();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            $this->affected_rows = $stmt->rowCount();
        }
        
        return $data;
    }
    
    public function insert($table, $data) {
        $columns = implode('`, `', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $param_values = array_values($data);
        
        $sql = "INSERT INTO `$table` (`{$columns}`) VALUES ({$values})";
        
        if ($this->driver === 'mysqli') {
            $stmt = $this->db->prepare($sql);
            if ($param_values) {
                $types = str_repeat('s', count($param_values));
                $stmt->bind_param($types, ...$param_values);
            }
            $stmt->execute();
            $this->affected_rows = $stmt->affected_rows;
            $insert_id = $stmt->insert_id;
            $stmt->close();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($param_values);
            $this->affected_rows = $stmt->rowCount();
            $insert_id = $this->db->lastInsertId();
        }
        
        return $insert_id;
    }
    
    public function update($table, $data, $where = null) {
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $set_clause = implode(', ', $sets);
        $sql = "UPDATE `$table` SET {$set_clause}";
        
        if ($where) {
            $where_conditions = [];
            foreach ($where as $key => $value) {
                $where_conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
            $sql .= $where_clause;
        }
        
        if ($this->driver === 'mysqli') {
            $stmt = $this->db->prepare($sql);
            if ($params) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $this->affected_rows = $stmt->affected_rows;
            $stmt->close();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $this->affected_rows = $stmt->rowCount();
        }
        
        return $this->affected_rows;
    }
    
    public function delete($table, $where = null) {
        $sql = "DELETE FROM `$table`";
        
        if ($where) {
            $where_conditions = [];
            $params = [];
            
            foreach ($where as $key => $value) {
                $where_conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            
            $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
            $sql .= $where_clause;
        }
        
        if ($this->driver === 'mysqli') {
            $stmt = $this->db->prepare($sql);
            if ($params) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $this->affected_rows = $stmt->affected_rows;
            $stmt->close();
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params ?? []);
            $this->affected_rows = $stmt->rowCount();
        }
        
        return $this->affected_rows;
    }
    
    public function getAffectedRows() {
        return $this->affected_rows;
    }
}