<?php
/**
 * Base Model Class
 * Provides common database operations and utilities
 */

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Get all records
     */
    public function all($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $result = mysqli_query($this->db, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") VALUES ($placeholders)";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($this->db));
        }
        
        $types = str_repeat('s', count($data));
        $values = array_values($data);
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        
        $result = mysqli_stmt_execute($stmt);
        $insertId = mysqli_insert_id($this->db);
        mysqli_stmt_close($stmt);
        
        if (!$result) {
            throw new Exception("Execute failed: " . mysqli_error($this->db));
        }
        
        return $insertId;
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        $sql = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($this->db));
        }
        
        $values = array_values($data);
        $values[] = $id;
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$result) {
            throw new Exception("Execute failed: " . mysqli_error($this->db));
        }
        
        return $result;
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $stmt = mysqli_prepare($this->db, "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Find records with conditions
     */
    public function where($conditions, $limit = null) {
        $whereClause = [];
        $values = [];
        $types = '';
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "$field = ?";
            $values[] = $value;
            $types .= 's';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($values) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($conditions)) {
            $whereClause = [];
            $values = [];
            $types = '';

            foreach ($conditions as $field => $value) {
                if (strpos($field, '(') !== false) {
                    // Handle functions like MONTH(), YEAR()
                    $whereClause[] = "$field = ?";
                } else {
                    $whereClause[] = "$field = ?";
                }
                $values[] = $value;
                $types .= 's';
            }

            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        $stmt = mysqli_prepare($this->db, $sql);
        if (!empty($values)) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)$data['total'];
    }
    
    /**
     * Filter data based on fillable fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Execute raw query
     */
    public function query($sql, $params = []) {
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
        return $data;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        mysqli_autocommit($this->db, false);
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        mysqli_commit($this->db);
        mysqli_autocommit($this->db, true);
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        mysqli_rollback($this->db);
        mysqli_autocommit($this->db, true);
    }
}
?>
