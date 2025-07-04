<?php
require_once 'BaseModel.php';

/**
 * Kendaraan Model
 * Handles kendaraan related database operations
 */
class Kendaraan extends BaseModel {
    protected $table = 'tbl_kendaraan';
    protected $primaryKey = 'id_kendaraan';
    protected $fillable = [
        'jenis_kendaraan', 'merk_type', 'tahun', 'no_polisi', 'warna',
        'no_mesin', 'no_rangka', 'penanggung_jawab', 'pemakai',
        'keterangan', 'id_user', 'status'
    ];
    
    /**
     * Create new kendaraan
     */
    public function createKendaraan($data) {
        // Validate required fields
        $required = ['jenis_kendaraan', 'merk_type', 'tahun', 'no_polisi', 'no_mesin', 'no_rangka', 'id_user'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required");
            }
        }
        
        // Check for duplicate no_polisi
        if ($this->findByNoPolisi($data['no_polisi'])) {
            throw new Exception("Nomor polisi sudah ada");
        }
        
        // Check for duplicate no_mesin
        if ($this->findByNoMesin($data['no_mesin'])) {
            throw new Exception("Nomor mesin sudah ada");
        }
        
        // Check for duplicate no_rangka
        if ($this->findByNoRangka($data['no_rangka'])) {
            throw new Exception("Nomor rangka sudah ada");
        }
        
        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'Aktif';
        }
        
        $id = $this->create($data);
        
        if ($id) {
            log_activity('kendaraan_created', 'Kendaraan created', [
                'id' => $id,
                'no_polisi' => $data['no_polisi'],
                'merk_type' => $data['merk_type']
            ]);
        }
        
        return $id;
    }
    
    /**
     * Find kendaraan by nomor polisi
     */
    public function findByNoPolisi($noPolisi) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE no_polisi = ?");
        mysqli_stmt_bind_param($stmt, "s", $noPolisi);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Find kendaraan by nomor mesin
     */
    public function findByNoMesin($noMesin) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE no_mesin = ?");
        mysqli_stmt_bind_param($stmt, "s", $noMesin);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Find kendaraan by nomor rangka
     */
    public function findByNoRangka($noRangka) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE no_rangka = ?");
        mysqli_stmt_bind_param($stmt, "s", $noRangka);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Get kendaraan with user details
     */
    public function getWithUserDetails($id = null) {
        $sql = "SELECT k.*, u.nama as created_by_name, u.username as created_by_username 
                FROM {$this->table} k 
                LEFT JOIN tbl_user u ON k.id_user = u.id_user";
        
        if ($id) {
            $sql .= " WHERE k.{$this->primaryKey} = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $data;
        } else {
            $sql .= " ORDER BY k.created_at DESC";
            return $this->query($sql);
        }
    }
    
    /**
     * Update kendaraan
     */
    public function updateKendaraan($id, $data) {
        $kendaraan = $this->find($id);
        if (!$kendaraan) {
            throw new Exception("Kendaraan not found");
        }
        
        // Check for duplicate no_polisi (excluding current record)
        if (isset($data['no_polisi']) && $data['no_polisi'] !== $kendaraan['no_polisi']) {
            if ($this->findByNoPolisi($data['no_polisi'])) {
                throw new Exception("Nomor polisi sudah ada");
            }
        }
        
        // Check for duplicate no_mesin (excluding current record)
        if (isset($data['no_mesin']) && $data['no_mesin'] !== $kendaraan['no_mesin']) {
            if ($this->findByNoMesin($data['no_mesin'])) {
                throw new Exception("Nomor mesin sudah ada");
            }
        }
        
        // Check for duplicate no_rangka (excluding current record)
        if (isset($data['no_rangka']) && $data['no_rangka'] !== $kendaraan['no_rangka']) {
            if ($this->findByNoRangka($data['no_rangka'])) {
                throw new Exception("Nomor rangka sudah ada");
            }
        }
        
        // Remove id_user from update data to prevent unauthorized changes
        unset($data['id_user']);
        
        $result = $this->update($id, $data);
        
        if ($result) {
            log_activity('kendaraan_updated', 'Kendaraan updated', [
                'id' => $id,
                'no_polisi' => $kendaraan['no_polisi'],
                'updated_fields' => array_keys($data)
            ]);
        }
        
        return $result;
    }
    
    /**
     * Delete kendaraan
     */
    public function deleteKendaraan($id) {
        $kendaraan = $this->find($id);
        if (!$kendaraan) {
            throw new Exception("Kendaraan not found");
        }
        
        $result = $this->delete($id);
        
        if ($result) {
            log_activity('kendaraan_deleted', 'Kendaraan deleted', [
                'id' => $id,
                'no_polisi' => $kendaraan['no_polisi'],
                'merk_type' => $kendaraan['merk_type']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Search kendaraan
     */
    public function search($keyword, $limit = 10) {
        $sql = "SELECT k.*, u.nama as created_by_name 
                FROM {$this->table} k 
                LEFT JOIN tbl_user u ON k.id_user = u.id_user 
                WHERE (k.no_polisi LIKE ? OR k.merk_type LIKE ? OR k.pemakai LIKE ? OR k.penanggung_jawab LIKE ?) 
                ORDER BY k.created_at DESC 
                LIMIT ?";
        
        $searchTerm = "%$keyword%";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
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
     * Get kendaraan by status
     */
    public function getByStatus($status) {
        return $this->where(['status' => $status]);
    }
    
    /**
     * Get active kendaraan
     */
    public function getActive() {
        return $this->getByStatus('Aktif');
    }
    
    /**
     * Get kendaraan by jenis
     */
    public function getByJenis($jenis) {
        return $this->where(['jenis_kendaraan' => $jenis]);
    }
    
    /**
     * Get kendaraan by user
     */
    public function getByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE id_user = ? ORDER BY created_at DESC";
        return $this->query($sql, [$userId]);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total'] = $this->count();
        $stats['aktif'] = $this->count(['status' => 'Aktif']);
        $stats['tidak_aktif'] = $this->count(['status' => 'Tidak Aktif']);
        $stats['maintenance'] = $this->count(['status' => 'Maintenance']);
        
        // Get jenis kendaraan statistics
        $sql = "SELECT jenis_kendaraan, COUNT(*) as count FROM {$this->table} GROUP BY jenis_kendaraan";
        $jenisStats = $this->query($sql);
        $stats['jenis'] = [];
        foreach ($jenisStats as $stat) {
            $stats['jenis'][$stat['jenis_kendaraan']] = $stat['count'];
        }
        
        // Get tahun statistics
        $sql = "SELECT tahun, COUNT(*) as count FROM {$this->table} GROUP BY tahun ORDER BY tahun DESC";
        $tahunStats = $this->query($sql);
        $stats['tahun'] = [];
        foreach ($tahunStats as $stat) {
            $stats['tahun'][$stat['tahun']] = $stat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get recent kendaraan
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT k.*, u.nama as created_by_name 
                FROM {$this->table} k 
                LEFT JOIN tbl_user u ON k.id_user = u.id_user 
                ORDER BY k.created_at DESC 
                LIMIT ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $limit);
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
     * Check if user can access kendaraan
     */
    public function canUserAccess($kendaraanId, $userId, $isAdmin = false) {
        if ($isAdmin) {
            return true;
        }
        
        $kendaraan = $this->find($kendaraanId);
        return $kendaraan && $kendaraan['id_user'] == $userId;
    }
    
    /**
     * Update status kendaraan
     */
    public function updateStatus($id, $status) {
        $validStatuses = ['Aktif', 'Tidak Aktif', 'Maintenance'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Status tidak valid");
        }
        
        $result = $this->update($id, ['status' => $status]);
        
        if ($result) {
            log_activity('kendaraan_status_updated', 'Kendaraan status updated', [
                'id' => $id,
                'new_status' => $status
            ]);
        }
        
        return $result;
    }
    
    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 10, $search = null, $status = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT k.*, u.nama as created_by_name 
                FROM {$this->table} k 
                LEFT JOIN tbl_user u ON k.id_user = u.id_user";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} k";
        
        $params = [];
        $whereConditions = [];
        
        if ($search) {
            $whereConditions[] = "(k.no_polisi LIKE ? OR k.merk_type LIKE ? OR k.pemakai LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($status) {
            $whereConditions[] = "k.status = ?";
            $params[] = $status;
        }
        
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(' AND ', $whereConditions);
            $sql .= $whereClause;
            $countSql .= $whereClause;
        }
        
        $sql .= " ORDER BY k.created_at DESC LIMIT $perPage OFFSET $offset";
        
        $data = $this->query($sql, $params);
        $totalResult = $this->query($countSql, $params);
        $total = $totalResult[0]['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
?>
