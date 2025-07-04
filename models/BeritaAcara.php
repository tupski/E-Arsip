<?php
require_once 'BaseModel.php';

/**
 * BeritaAcara Model
 * Handles berita acara related database operations
 */
class BeritaAcara extends BaseModel {
    protected $table = 'tbl_berita_acara';
    protected $primaryKey = 'id_berita_acara';
    protected $fillable = [
        'no_berita_acara', 'nama_pemakai', 'nip', 'unit_kerja', 'jabatan_pembina',
        'no_pakta_integritas', 'nama_kendaraan', 'status_bpkb', 'no_bpkb',
        'barang1_qty', 'barang1_nama', 'barang2_qty', 'barang2_nama',
        'barang3_qty', 'barang3_nama', 'barang4_qty', 'barang4_nama',
        'keterangan', 'tgl_pembuatan', 'id_user'
    ];
    
    /**
     * Create new berita acara
     */
    public function createBeritaAcara($data) {
        // Validate required fields
        $required = ['no_berita_acara', 'nama_pemakai', 'nip', 'unit_kerja', 'tgl_pembuatan', 'id_user'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field $field is required");
            }
        }
        
        // Check if no_berita_acara already exists
        if ($this->findByNoBeritaAcara($data['no_berita_acara'])) {
            throw new Exception("Nomor berita acara sudah ada");
        }
        
        $id = $this->create($data);
        
        if ($id) {
            log_activity('berita_acara_created', 'Berita acara created', [
                'id' => $id,
                'no_berita_acara' => $data['no_berita_acara'],
                'nama_pemakai' => $data['nama_pemakai']
            ]);
        }
        
        return $id;
    }
    
    /**
     * Find berita acara by nomor
     */
    public function findByNoBeritaAcara($noBeritaAcara) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM {$this->table} WHERE no_berita_acara = ?");
        mysqli_stmt_bind_param($stmt, "s", $noBeritaAcara);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $data;
    }
    
    /**
     * Get berita acara with user details
     */
    public function getWithUserDetails($id = null) {
        $sql = "SELECT ba.*, u.nama as created_by_name, u.username as created_by_username 
                FROM {$this->table} ba 
                LEFT JOIN tbl_user u ON ba.id_user = u.id_user";
        
        if ($id) {
            $sql .= " WHERE ba.{$this->primaryKey} = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $data;
        } else {
            $sql .= " ORDER BY ba.tgl_pembuatan DESC";
            return $this->query($sql);
        }
    }
    
    /**
     * Update berita acara
     */
    public function updateBeritaAcara($id, $data) {
        // Remove id_user from update data to prevent unauthorized changes
        unset($data['id_user']);
        
        $result = $this->update($id, $data);
        
        if ($result) {
            log_activity('berita_acara_updated', 'Berita acara updated', [
                'id' => $id,
                'updated_fields' => array_keys($data)
            ]);
        }
        
        return $result;
    }
    
    /**
     * Delete berita acara
     */
    public function deleteBeritaAcara($id) {
        $beritaAcara = $this->find($id);
        if (!$beritaAcara) {
            throw new Exception("Berita acara not found");
        }
        
        $result = $this->delete($id);
        
        if ($result) {
            log_activity('berita_acara_deleted', 'Berita acara deleted', [
                'id' => $id,
                'no_berita_acara' => $beritaAcara['no_berita_acara']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Search berita acara
     */
    public function search($keyword, $limit = 10) {
        $sql = "SELECT ba.*, u.nama as created_by_name 
                FROM {$this->table} ba 
                LEFT JOIN tbl_user u ON ba.id_user = u.id_user 
                WHERE (ba.no_berita_acara LIKE ? OR ba.nama_pemakai LIKE ? OR ba.nip LIKE ?) 
                ORDER BY ba.tgl_pembuatan DESC 
                LIMIT ?";
        
        $searchTerm = "%$keyword%";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $searchTerm, $searchTerm, $searchTerm, $limit);
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
     * Get berita acara by date range
     */
    public function getByDateRange($startDate, $endDate) {
        $sql = "SELECT ba.*, u.nama as created_by_name 
                FROM {$this->table} ba 
                LEFT JOIN tbl_user u ON ba.id_user = u.id_user 
                WHERE ba.tgl_pembuatan BETWEEN ? AND ? 
                ORDER BY ba.tgl_pembuatan DESC";
        
        return $this->query($sql, [$startDate, $endDate]);
    }
    
    /**
     * Get berita acara by user
     */
    public function getByUser($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE id_user = ? ORDER BY tgl_pembuatan DESC";
        return $this->query($sql, [$userId]);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $stats['total'] = $this->count();
        $stats['this_month'] = $this->count([
            'MONTH(tgl_pembuatan)' => date('n'),
            'YEAR(tgl_pembuatan)' => date('Y')
        ]);
        $stats['this_year'] = $this->count(['YEAR(tgl_pembuatan)' => date('Y')]);
        
        // Get status BPKB statistics
        $sql = "SELECT status_bpkb, COUNT(*) as count FROM {$this->table} GROUP BY status_bpkb";
        $statusStats = $this->query($sql);
        $stats['status_bpkb'] = [];
        foreach ($statusStats as $stat) {
            $stats['status_bpkb'][$stat['status_bpkb']] = $stat['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get recent berita acara
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT ba.*, u.nama as created_by_name 
                FROM {$this->table} ba 
                LEFT JOIN tbl_user u ON ba.id_user = u.id_user 
                ORDER BY ba.created_at DESC 
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
     * Generate next berita acara number
     */
    public function generateNextNumber($prefix = 'BA') {
        $year = date('Y');
        $month = date('m');
        
        $sql = "SELECT no_berita_acara FROM {$this->table} 
                WHERE no_berita_acara LIKE ? 
                ORDER BY no_berita_acara DESC 
                LIMIT 1";
        
        $pattern = "$prefix/%/$month/$year";
        $result = $this->query($sql, [$pattern]);
        
        if (empty($result)) {
            $nextNumber = 1;
        } else {
            $lastNumber = $result[0]['no_berita_acara'];
            $parts = explode('/', $lastNumber);
            $nextNumber = (int)$parts[1] + 1;
        }
        
        return sprintf("%s/%03d/%s/%s", $prefix, $nextNumber, $month, $year);
    }
    
    /**
     * Check if user can access berita acara
     */
    public function canUserAccess($beritaAcaraId, $userId, $isAdmin = false) {
        if ($isAdmin) {
            return true;
        }
        
        $beritaAcara = $this->find($beritaAcaraId);
        return $beritaAcara && $beritaAcara['id_user'] == $userId;
    }
    
    /**
     * Get paginated results
     */
    public function paginate($page = 1, $perPage = 10, $search = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT ba.*, u.nama as created_by_name 
                FROM {$this->table} ba 
                LEFT JOIN tbl_user u ON ba.id_user = u.id_user";
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} ba";
        
        $params = [];
        
        if ($search) {
            $whereClause = " WHERE (ba.no_berita_acara LIKE ? OR ba.nama_pemakai LIKE ? OR ba.nip LIKE ?)";
            $sql .= $whereClause;
            $countSql .= $whereClause;
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql .= " ORDER BY ba.tgl_pembuatan DESC LIMIT $perPage OFFSET $offset";
        
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
