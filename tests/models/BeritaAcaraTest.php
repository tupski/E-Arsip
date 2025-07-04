<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * BeritaAcara Model Test
 */
class BeritaAcaraTest extends BaseTestCase {
    private $beritaAcaraModel;
    
    public function setUp() {
        parent::setUp();
        $this->beritaAcaraModel = new BeritaAcara($this->db);
    }
    
    public function testCreateBeritaAcara() {
        $data = [
            'no_berita_acara' => 'BA/001/01/2024',
            'nama_pemakai' => 'Test Pemakai',
            'nip' => '12345678901234567890',
            'unit_kerja' => 'Test Unit Kerja',
            'jabatan_pembina' => 'Test Jabatan',
            'no_pakta_integritas' => 'PI/001/2024',
            'tgl_pembuatan' => '2024-01-01',
            'id_user' => 1
        ];
        
        $id = $this->beritaAcaraModel->createBeritaAcara($data);
        
        $this->assertNotNull($id, 'BeritaAcara ID should not be null');
        $this->assertTrue($id > 0, 'BeritaAcara ID should be greater than 0');
        
        // Verify berita acara was created
        $beritaAcara = $this->beritaAcaraModel->find($id);
        $this->assertNotNull($beritaAcara, 'BeritaAcara should exist in database');
        $this->assertEquals('BA/001/01/2024', $beritaAcara['no_berita_acara']);
        $this->assertEquals('Test Pemakai', $beritaAcara['nama_pemakai']);
        $this->assertEquals('Test Unit Kerja', $beritaAcara['unit_kerja']);
    }
    
    public function testCreateBeritaAcaraWithDuplicateNumber() {
        $data = [
            'no_berita_acara' => 'BA/DUPLICATE/01/2024',
            'nama_pemakai' => 'Test Pemakai 1',
            'nip' => '12345678901234567890',
            'unit_kerja' => 'Test Unit Kerja',
            'jabatan_pembina' => 'Test Jabatan',
            'no_pakta_integritas' => 'PI/001/2024',
            'tgl_pembuatan' => '2024-01-01',
            'id_user' => 1
        ];
        
        // Create first berita acara
        $this->beritaAcaraModel->createBeritaAcara($data);
        
        // Try to create second with same number
        $data['nama_pemakai'] = 'Test Pemakai 2';
        
        $this->expectException('Exception', function() use ($data) {
            $this->beritaAcaraModel->createBeritaAcara($data);
        });
    }
    
    public function testFindByNoBeritaAcara() {
        $id = $this->createTestBeritaAcara(['no_berita_acara' => 'BA/FIND/01/2024']);
        
        $beritaAcara = $this->beritaAcaraModel->findByNoBeritaAcara('BA/FIND/01/2024');
        
        $this->assertNotNull($beritaAcara, 'BeritaAcara should be found');
        $this->assertEquals($id, $beritaAcara['id_berita_acara']);
        $this->assertEquals('BA/FIND/01/2024', $beritaAcara['no_berita_acara']);
    }
    
    public function testGetWithUserDetails() {
        $id = $this->createTestBeritaAcara();
        
        $beritaAcara = $this->beritaAcaraModel->getWithUserDetails($id);
        
        $this->assertNotNull($beritaAcara, 'BeritaAcara should be found');
        $this->assertArrayHasKey('created_by_name', $beritaAcara);
        $this->assertArrayHasKey('created_by_username', $beritaAcara);
        $this->assertEquals('Test Administrator', $beritaAcara['created_by_name']);
    }
    
    public function testUpdateBeritaAcara() {
        $id = $this->createTestBeritaAcara();
        
        $updateData = [
            'nama_pemakai' => 'Updated Pemakai',
            'unit_kerja' => 'Updated Unit Kerja'
        ];
        
        $result = $this->beritaAcaraModel->updateBeritaAcara($id, $updateData);
        $this->assertTrue($result, 'Update should succeed');
        
        // Verify update
        $beritaAcara = $this->beritaAcaraModel->find($id);
        $this->assertEquals('Updated Pemakai', $beritaAcara['nama_pemakai']);
        $this->assertEquals('Updated Unit Kerja', $beritaAcara['unit_kerja']);
    }
    
    public function testDeleteBeritaAcara() {
        $id = $this->createTestBeritaAcara();
        
        $result = $this->beritaAcaraModel->deleteBeritaAcara($id);
        $this->assertTrue($result, 'Delete should succeed');
        
        // Verify deletion
        $beritaAcara = $this->beritaAcaraModel->find($id);
        $this->assertNull($beritaAcara, 'BeritaAcara should be deleted');
    }
    
    public function testDeleteNonExistentBeritaAcara() {
        $this->expectException('Exception', function() {
            $this->beritaAcaraModel->deleteBeritaAcara(99999);
        });
    }
    
    public function testSearch() {
        $this->createTestBeritaAcara(['nama_pemakai' => 'Searchable Pemakai']);
        $this->createTestBeritaAcara(['no_berita_acara' => 'BA/SEARCH/01/2024']);
        
        $results = $this->beritaAcaraModel->search('Searchable');
        $this->assertTrue(count($results) >= 1, 'Should find at least 1 result');
        
        $results = $this->beritaAcaraModel->search('SEARCH');
        $this->assertTrue(count($results) >= 1, 'Should find at least 1 result');
    }
    
    public function testGetByDateRange() {
        $this->createTestBeritaAcara(['tgl_pembuatan' => '2024-01-15']);
        $this->createTestBeritaAcara(['tgl_pembuatan' => '2024-01-20']);
        $this->createTestBeritaAcara(['tgl_pembuatan' => '2024-02-01']);
        
        $results = $this->beritaAcaraModel->getByDateRange('2024-01-01', '2024-01-31');
        $this->assertTrue(count($results) >= 2, 'Should find at least 2 results in January');
        
        foreach ($results as $result) {
            $this->assertTrue(
                $result['tgl_pembuatan'] >= '2024-01-01' && 
                $result['tgl_pembuatan'] <= '2024-01-31',
                'All results should be within date range'
            );
        }
    }
    
    public function testGetByUser() {
        $userId = $this->createTestUser();
        $this->createTestBeritaAcara(['id_user' => $userId]);
        $this->createTestBeritaAcara(['id_user' => $userId]);
        
        $results = $this->beritaAcaraModel->getByUser($userId);
        $this->assertTrue(count($results) >= 2, 'Should find at least 2 results for user');
        
        foreach ($results as $result) {
            $this->assertEquals($userId, $result['id_user'], 'All results should belong to the user');
        }
    }
    
    public function testGetStatistics() {
        // Create test data for different months/years
        $this->createTestBeritaAcara(['tgl_pembuatan' => date('Y-m-d')]);
        $this->createTestBeritaAcara(['tgl_pembuatan' => date('Y-m-d')]);
        $this->createTestBeritaAcara(['tgl_pembuatan' => '2023-12-01']);
        
        $stats = $this->beritaAcaraModel->getStatistics();
        
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('this_month', $stats);
        $this->assertArrayHasKey('this_year', $stats);
        $this->assertArrayHasKey('status_bpkb', $stats);
        
        $this->assertTrue($stats['total'] >= 3, 'Should have at least 3 total records');
        $this->assertTrue($stats['this_month'] >= 2, 'Should have at least 2 records this month');
    }
    
    public function testGetRecent() {
        $this->createTestBeritaAcara(['nama_pemakai' => 'Recent 1']);
        $this->createTestBeritaAcara(['nama_pemakai' => 'Recent 2']);
        $this->createTestBeritaAcara(['nama_pemakai' => 'Recent 3']);
        
        $results = $this->beritaAcaraModel->getRecent(2);
        $this->assertEquals(2, count($results), 'Should return exactly 2 results');
        
        // Results should be ordered by created_at DESC
        $this->assertTrue(
            $results[0]['created_at'] >= $results[1]['created_at'],
            'Results should be ordered by creation date descending'
        );
    }
    
    public function testGenerateNextNumber() {
        $nextNumber = $this->beritaAcaraModel->generateNextNumber('BA');
        
        $this->assertStringContains('BA/', $nextNumber, 'Number should contain prefix');
        $this->assertStringContains(date('m'), $nextNumber, 'Number should contain current month');
        $this->assertStringContains(date('Y'), $nextNumber, 'Number should contain current year');
        
        // Create a berita acara with this number
        $this->createTestBeritaAcara(['no_berita_acara' => $nextNumber]);
        
        // Generate next number should be incremented
        $nextNumber2 = $this->beritaAcaraModel->generateNextNumber('BA');
        $this->assertNotEquals($nextNumber, $nextNumber2, 'Next number should be different');
    }
    
    public function testCanUserAccess() {
        $userId = $this->createTestUser();
        $id = $this->createTestBeritaAcara(['id_user' => $userId]);
        
        // User should be able to access their own berita acara
        $canAccess = $this->beritaAcaraModel->canUserAccess($id, $userId, false);
        $this->assertTrue($canAccess, 'User should be able to access their own berita acara');
        
        // Other user should not be able to access
        $otherUserId = $this->createTestUser();
        $canAccess = $this->beritaAcaraModel->canUserAccess($id, $otherUserId, false);
        $this->assertFalse($canAccess, 'Other user should not be able to access');
        
        // Admin should be able to access any berita acara
        $canAccess = $this->beritaAcaraModel->canUserAccess($id, $otherUserId, true);
        $this->assertTrue($canAccess, 'Admin should be able to access any berita acara');
    }
    
    public function testPaginate() {
        // Create test data
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestBeritaAcara(['nama_pemakai' => "Pemakai $i"]);
        }
        
        $result = $this->beritaAcaraModel->paginate(1, 10);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('last_page', $result);
        
        $this->assertEquals(10, count($result['data']), 'Should return 10 items per page');
        $this->assertTrue($result['total'] >= 15, 'Should have at least 15 total items');
        $this->assertEquals(1, $result['current_page'], 'Current page should be 1');
        $this->assertTrue($result['last_page'] >= 2, 'Should have at least 2 pages');
        
        // Test search in pagination
        $searchResult = $this->beritaAcaraModel->paginate(1, 10, 'Pemakai 1');
        $this->assertTrue(count($searchResult['data']) >= 1, 'Search should return results');
    }
}
?>
