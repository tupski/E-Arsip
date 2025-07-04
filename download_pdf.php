<?php
// Simple PDF download without session conflicts
session_start();

// Check authentication
if (!isset($_SESSION['admin']) || !isset($_SESSION['id_user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Akses ditolak');
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID tidak valid');
}

// Database connection
$config = mysqli_connect('localhost', 'root', 'mysql', 'earsip_db');
if (!$config) {
    exit('Database connection failed');
}
mysqli_set_charset($config, 'utf8mb4');

$id = (int)$_GET['id'];

// Get data based on user role
if ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
    $stmt = mysqli_prepare($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara = ?");
} else {
    $stmt = mysqli_prepare($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION['id_user']);
}

if ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
    mysqli_stmt_bind_param($stmt, "i", $id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header('HTTP/1.1 404 Not Found');
    exit('Data tidak ditemukan');
}

$data = mysqli_fetch_assoc($result);

// Get instansi data
$instansi_result = mysqli_query($config, "SELECT * FROM tbl_instansi LIMIT 1");
$instansi = mysqli_fetch_assoc($instansi_result);

// Clean output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Load TCPDF
require_once 'include/TCPDF/tcpdf.php';

// Create PDF
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 15, 25);
$pdf->AddPage();

// Format date
$tgl = new DateTime($data['tgl_pembuatan']);
$bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$tgl_lengkap = $tgl->format('d') . ' ' . $bulan[(int)$tgl->format('n')] . ' ' . $tgl->format('Y');

// HTML content
$html = '
<style>
body { font-family: "times"; font-size: 12pt; }
table { width: 100%; border-collapse: collapse; }
hr.double { border: 0; border-top: 3px double black; }
.kop { text-align: center; font-weight: bold; }
.subkop { font-size: 10pt; }
.judul { text-align: center; font-weight: bold; font-size: 14pt; margin-top: 20px; }
.paragraf { text-align: justify; margin-top: 10px; line-height: 1.4; margin-bottom: 5px; }
.tabel-barang th, .tabel-barang td {
    border: 1px solid black; padding: 6px; text-align: center;
}
.tabel-barang th { background-color: #f0f0f0; font-weight: bold; }
</style>

<table>
    <tr>
        <td width="15%"><img src="assets/img/logo3.png" width="70"></td>
        <td class="kop" width="85%">
            PEMERINTAH KOTA TANGERANG SELATAN<br>
            DINAS TENAGA KERJA<br>
            <span class="subkop">JL. RAYA PUSPITEK – SERPONG No.1 RT.018 RW.005 Gedung Depo Arsip Lt.4&5</span><br>
            <span class="subkop">KELURAHAN SETU KECAMATAN SETU KOTA TANGERANG SELATAN Tlp/Fax : (021) 53869599</span>
        </td>
    </tr>
</table>
<hr class="double">

<div class="judul">BERITA ACARA SERAH TERIMA BARANG/KENDARAAN</div>
<div style="text-align: center; font-size: 12pt; margin-bottom: 15px;">
    Nomor: '.htmlspecialchars($data['no_pakta_integritas']).'
</div>

<div class="paragraf">
    Pada hari ini <b>'.$tgl_lengkap.'</b>, kami yang bertanda tangan di bawah ini:
</div>

<table style="margin-top: 15px; margin-bottom: 15px;">
    <tr>
        <td width="3%">1.</td>
        <td width="25%">Nama</td>
        <td width="2%">:</td>
        <td width="70%"><b>'.htmlspecialchars($data['nama_pemakai']).'</b></td>
    </tr>
    <tr>
        <td></td>
        <td>NIP</td>
        <td>:</td>
        <td>'.htmlspecialchars($data['nip']).'</td>
    </tr>
    <tr>
        <td></td>
        <td>Unit Kerja</td>
        <td>:</td>
        <td>'.htmlspecialchars($data['unit_kerja']).'</td>
    </tr>
    <tr>
        <td></td>
        <td>Jabatan</td>
        <td>:</td>
        <td>'.htmlspecialchars($data['jabatan_pembina']).'</td>
    </tr>
</table>

<div class="paragraf">
    Dengan ini menyatakan telah menerima serah terima barang/kendaraan sebagai berikut:
</div>

<div class="paragraf">
    <b>Kendaraan:</b> '.htmlspecialchars($data['nama_kendaraan']).'<br>
    <b>Status BPKB:</b> '.htmlspecialchars($data['status_bpkb']).'<br>
    <b>No. BPKB:</b> '.htmlspecialchars($data['no_bpkb']).'
</div>';

// Add items table if any
$has_items = false;
$items_html = '<table class="tabel-barang" style="margin-top: 15px;">
    <tr>
        <th width="10%">No</th>
        <th width="15%">Jumlah</th>
        <th width="75%">Nama Barang</th>
    </tr>';

$no = 1;
for ($i = 1; $i <= 4; $i++) {
    $qty = $data["barang{$i}_qty"];
    $nama = $data["barang{$i}_nama"];
    
    if (!empty($qty) && !empty($nama)) {
        $has_items = true;
        $items_html .= '<tr>
            <td>'.$no.'</td>
            <td>'.htmlspecialchars($qty).'</td>
            <td>'.htmlspecialchars($nama).'</td>
        </tr>';
        $no++;
    }
}

$items_html .= '</table>';

if ($has_items) {
    $html .= '<div class="paragraf"><b>Barang-barang:</b></div>' . $items_html;
}

if (!empty($data['keterangan'])) {
    $html .= '<div class="paragraf"><b>Keterangan:</b><br>'.htmlspecialchars($data['keterangan']).'</div>';
}

// Signature section
$html .= '
<div style="margin-top: 30px;">
    <table style="width: 100%;">
        <tr>
            <td width="50%" style="text-align: center;">
                <div>Yang Menyerahkan,</div>
                <div style="margin-top: 80px;">
                    <div style="text-decoration: underline; font-weight: bold;">'.htmlspecialchars($instansi['kepala_dinas'] ?? 'Kepala Dinas').'</div>
                    <div>NIP. '.htmlspecialchars($instansi['nip'] ?? '').'</div>
                </div>
            </td>
            <td width="50%" style="text-align: center;">
                <div>Yang Menerima,</div>
                <div style="margin-top: 80px;">
                    <div style="text-decoration: underline; font-weight: bold;">'.htmlspecialchars($data['nama_pemakai']).'</div>
                    <div>NIP. '.htmlspecialchars($data['nip']).'</div>
                </div>
            </td>
        </tr>
    </table>
</div>';

// Write HTML to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$filename = 'berita_acara_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['no_berita_acara']) . '.pdf';
$pdf->Output($filename, 'I');

mysqli_close($config);
exit();
?>
