<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'include/config.php';
require_once 'include/tcpdf/tcpdf.php';

if (!isset($_SESSION['admin'])) {
    die('Akses ditolak');
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID tidak valid');
}

ob_clean();
$id = mysqli_real_escape_string($config, $_GET['id']);

if ($_SESSION['admin'] == 1 || $_SESSION['admin'] == 2) {
    $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara='$id'");
} else {
    $id_user = $_SESSION['id_user'];
    $query = mysqli_query($config, "SELECT * FROM tbl_berita_acara WHERE id_berita_acara='$id' AND id_user='$id_user'");
}

if (!$query || mysqli_num_rows($query) == 0) {
    die('Data tidak ditemukan');
}
$data = mysqli_fetch_array($query);

// Format tanggal
$hariIndo = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni',
    '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];
$tanggal = date('d', strtotime($data['tgl_pembuatan']));
$bulan_id = $bulan[date('m', strtotime($data['tgl_pembuatan']))];
$tahun = date('Y', strtotime($data['tgl_pembuatan']));
$hari = $hariIndo[date('l', strtotime($data['tgl_pembuatan']))];
$tgl_lengkap = "$hari tanggal $tanggal Bulan $bulan_id Tahun $tahun";

// Hitung total barang
$total_barang = 0;
for ($i = 1; $i <= 4; $i++) {
    if (!empty($data["barang{$i}_nama"])) {
        $total_barang += (int)$data["barang{$i}_qty"];
    }
}

// Setup PDF
$pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(25, 15, 25);
$pdf->AddPage();

// HTML isi PDF
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
.ttd { text-align: center; margin-top: 30px; }
.tabel-identitas { width: 100%; margin-bottom: 5px; }
.tabel-identitas td { vertical-align: top; padding-bottom: 2px; }
.tabel-identitas td:nth-child(1) { width: 15%; white-space: nowrap; }
.tabel-identitas td:nth-child(2) { width: 3%; }
.tabel-identitas td:nth-child(3) { width: 82%; }
.italic { font-style: italic; }
</style>

<table>
    <tr>
        <td width="15%"><img src="assets/img/logo3.png" width="70"></td>
        <td class="kop" width="85%">
            PEMERINTAH KOTA TANGERANG SELATAN<br>
            DINAS TENAGA KERJA<br>
            <span class="subkop">JL. RAYA PUSPITEK – SERPONG No.1 RT.018 RW.005 Gedung Depo Arsip Lt.4&5</span>
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

<div class="paragraf">
    <table class="tabel-identitas">
        <tr>
            <td>1. Nama</td>
            <td>: KHATRUDIN, SE</td>
            <td></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: 19760802 200901 1 005</td>
            <td></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td colspan="2">: Kasubag Umum, Kepegawaian, dan Keuangan Dinas Tenaga Kerja Kota Tangerang Selatan</td>
        </tr>
        Disebut PIHAK KESATU, sebagai Penyerah Barang.
    </table>
</div>

<div class="paragraf">
    <table class="tabel-identitas">
        <tr>
            <td>2. Nama</td>
            <td>: '.$data['nama_pemakai'].'</td>
            <td></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: '.$data['nip'].'</td>
            <td></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>: '.$data['unit_kerja'].'</td>
            <td></td>
        </tr>
        Disebut PIHAK KEDUA, sebagai Penerima Barang Milik Daerah.
    </table>
</div>
';

if (!empty($data['nama_kendaraan'])) {
    $html .= '
    <div class="paragraf">
        PIHAK KESATU menyerahkan 1 (satu) unit Kendaraan Dinas kepada PIHAK KEDUA dengan rincian sebagai berikut:
    </div>
    <table class="tabel-identitas">
        <tr>
            <td>Nama Kendaraan</td>
            <td>: '.$data['nama_kendaraan'].'</td>
        </tr>
        <tr>
            <td>Status BPKB</td>
            <td>: '.$data['status_bpkb'].'</td>
        </tr>
        <tr>
            <td>Nomor BPKB</td>
            <td>: '.$data['no_bpkb'].'</td>
        </tr>
    </table>';
}

$html .= '
<div class="paragraf">
    PIHAK KESATU menyerahkan <b>'.$total_barang.' Unit</b> Barang Milik Daerah kepada PIHAK KEDUA dan PIHAK KEDUA menyatakan telah menerima dengan rincian sebagai berikut:
</div>

<table class="tabel-barang">
    <tr>
        <th width="10%">No</th>
        <th width="60%">Nama Barang</th>
        <th width="30%">Jumlah</th>
    </tr>';
$no = 1;
for ($i = 1; $i <= 4; $i++) {
    if (!empty($data["barang{$i}_nama"])) {
        $html .= '<tr>
            <td>'.$no.'</td>
            <td>'.$data["barang{$i}_nama"].'</td>
            <td>'.$data["barang{$i}_qty"].'</td>
        </tr>';
        $no++;
    }
}
$html .= '</table>';

if (!empty($data['nama_kendaraan'])) {
    $html .= '
    <div class="paragraf">
        PIHAK KEDUA wajib memenuhi ketentuan berikut terkait kendaraan dinas:
        <br>1. Mempergunakan kendaraan hanya untuk keperluan dinas;
        <br>2. Memelihara dan merawat kendaraan agar tetap dalam kondisi baik;
        <br>3. Tidak mengalihfungsikan kendaraan tanpa izin;
        <br>4. Melaporkan kerusakan kendaraan kepada penanggung jawab;
        <br>5. Menyerahkan kembali kendaraan saat mutasi atau pensiun;
        <br>6. Bertanggung jawab penuh atas kendaraan yang diterima.
    </div>';
}

$html .= '
<div class="paragraf">
    Demikian berita acara ini dibuat dengan sebenarnya untuk digunakan sebagaimana mestinya.
</div>
<br>
<br>
<br>
<br>
<div style="text-align: right; margin-top: 15px;">
    Tangerang Selatan, '.date('d-m-Y', strtotime($data['tgl_pembuatan'])).'
</div>
<br>
<br>
<table>
    <tr>
        <td class="ttd" width="50%">
            Yang Menyerahkan<br>
            PIHAK KESATU<br><br><br><br>
            <b>KHATRUDIN, SE</b><br>
            Penata Tk I, III/d<br>
            NIP. 19760802 200901 1 005
        </td>
        <td class="ttd" width="50%">
            Yang Menerima<br>
            PIHAK KEDUA<br><br><br><br>
            <b>'.$data['nama_pemakai'].'</b><br>
            '.$data['jabatan_pembina'].'<br>
            NIP. '.$data['nip'].'
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('berita_acara_'.$data['no_berita_acara'].'.pdf', 'I');
?>