<?php
// Clean PDF generator without session conflicts
require_once 'include/config.php';

// Check authentication
if (!is_logged_in()) {
    http_response_code(403);
    die('Akses ditolak - Silakan login terlebih dahulu');
}

// Validate ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('ID tidak valid');
}

// Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

$id = mysqli_real_escape_string($config, $_GET['id']);

// Get data based on user role
if ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
    $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara='$id'");
} else {
    $id_user = $_SESSION['id_user'];
    $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara='$id' AND id_user='$id_user'");
}

if (!$query || mysqli_num_rows($query) == 0) {
    http_response_code(404);
    die('Data tidak ditemukan atau Anda tidak memiliki akses');
}

$data = mysqli_fetch_array($query);

// Get instansi data
$instansi_query = mysqli_query($config, "SELECT * FROM tbl_instansi LIMIT 1");
$instansi = mysqli_fetch_array($instansi_query);

// Format tanggal
$tgl = date_create($data['tgl_pembuatan']);
$bulan = array(
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
);
$tgl_lengkap = date_format($tgl, 'd') . ' ' . $bulan[date_format($tgl, 'n')] . ' ' . date_format($tgl, 'Y');

// Load TCPDF
require_once 'include/TCPDF/tcpdf.php';

// Setup PDF
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 15, 25);
$pdf->AddPage();

// HTML content for PDF
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
.ttd { margin-top: 30px; }
.ttd-kiri { width: 50%; float: left; text-align: center; }
.ttd-kanan { width: 50%; float: right; text-align: center; }
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
    Nomor: '.$data['no_pakta_integritas'].'
</div>

<div class="paragraf">
    Pada hari ini <b>'.$tgl_lengkap.'</b>, kami yang bertanda tangan di bawah ini:
</div>

<table style="margin-top: 15px; margin-bottom: 15px;">
    <tr>
        <td width="3%">1.</td>
        <td width="25%">Nama</td>
        <td width="2%">:</td>
        <td width="70%"><b>'.$data['nama_pemakai'].'</b></td>
    </tr>
    <tr>
        <td></td>
        <td>NIP</td>
        <td>:</td>
        <td>'.$data['nip'].'</td>
    </tr>
    <tr>
        <td></td>
        <td>Unit Kerja</td>
        <td>:</td>
        <td>'.$data['unit_kerja'].'</td>
    </tr>
    <tr>
        <td></td>
        <td>Jabatan</td>
        <td>:</td>
        <td>'.$data['jabatan_pembina'].'</td>
    </tr>
</table>

<div class="paragraf">
    Dengan ini menyatakan telah menerima serah terima barang/kendaraan sebagai berikut:
</div>

<div class="paragraf">
    <b>Kendaraan:</b> '.$data['nama_kendaraan'].'<br>
    <b>Status BPKB:</b> '.$data['status_bpkb'].'<br>
    <b>No. BPKB:</b> '.$data['no_bpkb'].'
</div>';

// Add barang table if there are items
$has_barang = false;
$barang_html = '<table class="tabel-barang" style="margin-top: 15px;">
    <tr>
        <th width="10%">No</th>
        <th width="15%">Jumlah</th>
        <th width="75%">Nama Barang</th>
    </tr>';

$no = 1;
for ($i = 1; $i <= 4; $i++) {
    $qty_field = 'barang'.$i.'_qty';
    $nama_field = 'barang'.$i.'_nama';
    
    if (!empty($data[$qty_field]) && !empty($data[$nama_field])) {
        $has_barang = true;
        $barang_html .= '<tr>
            <td>'.$no.'</td>
            <td>'.$data[$qty_field].'</td>
            <td>'.$data[$nama_field].'</td>
        </tr>';
        $no++;
    }
}

$barang_html .= '</table>';

if ($has_barang) {
    $html .= '<div class="paragraf"><b>Barang-barang:</b></div>' . $barang_html;
}

// Add keterangan if exists
if (!empty($data['keterangan'])) {
    $html .= '<div class="paragraf"><b>Keterangan:</b><br>'.$data['keterangan'].'</div>';
}

// Add signature section
$html .= '
<div class="ttd">
    <table style="width: 100%;">
        <tr>
            <td width="50%" style="text-align: center;">
                <div>Yang Menyerahkan,</div>
                <div style="margin-top: 80px;">
                    <div style="text-decoration: underline; font-weight: bold;">'.$instansi['kepala_dinas'].'</div>
                    <div>NIP. '.$instansi['nip'].'</div>
                </div>
            </td>
            <td width="50%" style="text-align: center;">
                <div>Yang Menerima,</div>
                <div style="margin-top: 80px;">
                    <div style="text-decoration: underline; font-weight: bold;">'.$data['nama_pemakai'].'</div>
                    <div>NIP. '.$data['nip'].'</div>
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
exit();
?>
