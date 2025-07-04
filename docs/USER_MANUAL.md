# E-Arsip User Manual

## Daftar Isi

1. [Pengenalan Sistem](#pengenalan-sistem)
2. [Login dan Logout](#login-dan-logout)
3. [Dashboard](#dashboard)
4. [Manajemen Berita Acara](#manajemen-berita-acara)
5. [Manajemen Kendaraan](#manajemen-kendaraan)
6. [Manajemen User (Admin)](#manajemen-user-admin)
7. [Pengaturan Instansi](#pengaturan-instansi)
8. [Tips dan Trik](#tips-dan-trik)
9. [Troubleshooting](#troubleshooting)

## Pengenalan Sistem

E-Arsip adalah sistem manajemen arsip elektronik yang dirancang untuk membantu instansi dalam mengelola dokumen berita acara serah terima dan data kendaraan dinas. Sistem ini menyediakan fitur-fitur modern dengan antarmuka yang user-friendly dan responsive.

### Fitur Utama
- **Manajemen Berita Acara**: Membuat, mengedit, dan mengelola dokumen berita acara
- **Manajemen Kendaraan**: Inventarisasi dan tracking kendaraan dinas
- **Manajemen User**: Kontrol akses dan hak pengguna
- **Dashboard Analytics**: Statistik dan laporan real-time
- **Export PDF**: Cetak dokumen dalam format PDF
- **Search & Filter**: Pencarian cepat dan filter data
- **Responsive Design**: Akses dari desktop, tablet, dan mobile

### Level Pengguna
1. **Super Admin (Level 2)**: Akses penuh ke semua fitur
2. **Admin (Level 1)**: Akses ke manajemen data dan laporan
3. **User (Level 0)**: Akses terbatas untuk melihat dan input data

## Login dan Logout

### Cara Login
1. Buka browser dan akses URL sistem E-Arsip
2. Masukkan **Username** dan **Password** yang telah diberikan
3. Klik tombol **"Login"**
4. Sistem akan mengarahkan ke dashboard sesuai level akses

### Lupa Password
1. Hubungi administrator sistem untuk reset password
2. Administrator akan memberikan password baru
3. Disarankan untuk mengganti password setelah login pertama

### Cara Logout
1. Klik menu **"Logout"** di sidebar atau header
2. Sistem akan mengarahkan kembali ke halaman login
3. Session akan otomatis berakhir setelah 1 jam tidak aktif

## Dashboard

Dashboard adalah halaman utama yang menampilkan ringkasan informasi sistem.

### Komponen Dashboard
- **Statistik Pengguna**: Jumlah total user, admin, dan user aktif
- **Statistik Berita Acara**: Total dokumen, dokumen bulan ini, dan tahun ini
- **Statistik Kendaraan**: Total kendaraan, status aktif, maintenance, dan tidak aktif
- **Aktivitas Terbaru**: Log aktivitas pengguna terkini
- **Quick Actions**: Tombol cepat untuk aksi umum

### Navigasi
- **Sidebar**: Menu utama di sebelah kiri
- **Header**: Informasi user dan logout
- **Breadcrumb**: Navigasi lokasi halaman
- **Search Bar**: Pencarian global (Ctrl+K)

## Manajemen Berita Acara

### Melihat Daftar Berita Acara
1. Klik menu **"Berita Acara"** di sidebar
2. Daftar berita acara akan ditampilkan dalam tabel
3. Gunakan **pagination** untuk navigasi halaman
4. Gunakan **search box** untuk pencarian cepat

### Membuat Berita Acara Baru
1. Klik tombol **"Tambah Berita Acara"**
2. Isi form dengan data yang diperlukan:
   - **No. Berita Acara**: Nomor unik dokumen
   - **Nama Pemakai**: Nama penerima barang/kendaraan
   - **NIP**: Nomor Induk Pegawai
   - **Unit Kerja**: Bagian/divisi tempat bekerja
   - **Jabatan Pembina**: Jabatan atasan langsung
   - **No. Pakta Integritas**: Nomor pakta integritas
   - **Nama Kendaraan**: Jenis/nama kendaraan (opsional)
   - **Status BPKB**: Ada/Tidak Ada
   - **No. BPKB**: Nomor BPKB jika ada
   - **Barang 1-4**: Daftar barang yang diserahkan
   - **Keterangan**: Catatan tambahan
   - **Tanggal Pembuatan**: Tanggal dokumen dibuat
3. Klik **"Simpan"** untuk menyimpan data

### Mengedit Berita Acara
1. Klik tombol **"Edit"** pada baris data yang ingin diubah
2. Ubah data sesuai kebutuhan
3. Klik **"Update"** untuk menyimpan perubahan

### Menghapus Berita Acara
1. Klik tombol **"Hapus"** pada baris data
2. Konfirmasi penghapusan dengan klik **"Ya, Hapus"**
3. Data akan dihapus permanen dari sistem

### Export PDF
1. Klik tombol **"PDF"** pada baris data
2. Dokumen PDF akan diunduh otomatis
3. PDF berisi format resmi berita acara serah terima

### Tips Berita Acara
- Gunakan nomor berita acara yang konsisten (contoh: BA/001/01/2024)
- Pastikan data NIP sesuai dengan database kepegawaian
- Isi keterangan dengan detail untuk referensi masa depan
- Backup dokumen PDF ke storage eksternal

## Manajemen Kendaraan

### Melihat Daftar Kendaraan
1. Klik menu **"Kendaraan"** di sidebar
2. Daftar kendaraan ditampilkan dengan informasi lengkap
3. Filter berdasarkan **status** (Aktif, Tidak Aktif, Maintenance)
4. Gunakan pencarian untuk menemukan kendaraan spesifik

### Menambah Kendaraan Baru
1. Klik tombol **"Tambah Kendaraan"**
2. Isi form dengan data kendaraan:
   - **Jenis Kendaraan**: Motor/Mobil/Lainnya
   - **Merk/Type**: Merk dan tipe kendaraan
   - **Tahun**: Tahun pembuatan
   - **No. Polisi**: Nomor plat kendaraan (unik)
   - **Warna**: Warna kendaraan
   - **No. Mesin**: Nomor mesin (unik)
   - **No. Rangka**: Nomor rangka/chassis (unik)
   - **Penanggung Jawab**: PIC kendaraan
   - **Pemakai**: User yang menggunakan
   - **Status**: Aktif/Tidak Aktif/Maintenance
   - **Keterangan**: Catatan tambahan
3. Klik **"Simpan"** untuk menyimpan data

### Mengubah Status Kendaraan
1. Klik tombol **"Edit Status"** pada kendaraan
2. Pilih status baru:
   - **Aktif**: Kendaraan dalam kondisi operasional
   - **Maintenance**: Kendaraan sedang diperbaiki
   - **Tidak Aktif**: Kendaraan tidak digunakan
3. Tambahkan keterangan jika diperlukan
4. Klik **"Update Status"**

### Tracking Kendaraan
- Sistem mencatat riwayat perubahan status
- Log aktivitas tersimpan untuk audit trail
- Laporan penggunaan dapat diakses melalui dashboard

## Manajemen User (Admin)

*Fitur ini hanya tersedia untuk Admin dan Super Admin*

### Melihat Daftar User
1. Klik menu **"User Management"** di sidebar
2. Daftar semua user ditampilkan dengan informasi:
   - Username dan nama lengkap
   - Level akses (Super Admin/Admin/User)
   - Status aktif/tidak aktif
   - Tanggal login terakhir

### Menambah User Baru
1. Klik tombol **"Tambah User"**
2. Isi form registrasi:
   - **Username**: Nama login (unik, minimal 3 karakter)
   - **Password**: Password (minimal 8 karakter)
   - **Nama Lengkap**: Nama lengkap user
   - **NIP**: Nomor Induk Pegawai
   - **Level Admin**: 
     - 0 = User biasa
     - 1 = Admin
     - 2 = Super Admin
3. Klik **"Daftar"** untuk membuat akun

### Mengedit User
1. Klik tombol **"Edit"** pada user yang ingin diubah
2. Ubah informasi yang diperlukan
3. Untuk mengubah password, isi field password baru
4. Klik **"Update"** untuk menyimpan

### Menonaktifkan User
1. Klik tombol **"Nonaktifkan"** pada user
2. User tidak dapat login tetapi data tetap tersimpan
3. Untuk mengaktifkan kembali, klik **"Aktifkan"**

### Reset Password
1. Klik tombol **"Reset Password"** pada user
2. Password akan direset ke default
3. Informasikan password baru kepada user
4. User disarankan mengganti password setelah login

## Pengaturan Instansi

*Fitur ini hanya tersedia untuk Super Admin*

### Mengubah Data Instansi
1. Klik menu **"Pengaturan"** → **"Data Instansi"**
2. Edit informasi instansi:
   - **Nama Instansi**: Nama resmi organisasi
   - **Alamat**: Alamat lengkap
   - **Kepala Dinas**: Nama pimpinan
   - **NIP Kepala Dinas**: NIP pimpinan
   - **Website**: URL website resmi
   - **Email**: Email resmi instansi
3. Klik **"Simpan"** untuk menyimpan perubahan

### Upload Logo Instansi
1. Klik **"Pilih File"** untuk memilih logo
2. Format yang didukung: JPG, PNG, GIF (maksimal 5MB)
3. Logo akan muncul di header dan dokumen PDF
4. Klik **"Upload"** untuk menyimpan

## Tips dan Trik

### Keyboard Shortcuts
- **Ctrl + K**: Buka pencarian cepat
- **Escape**: Tutup modal/dropdown
- **Tab**: Navigasi antar field form
- **Enter**: Submit form aktif

### Pencarian Efektif
- Gunakan kata kunci spesifik
- Pencarian tidak case-sensitive
- Gunakan sebagian nomor untuk hasil lebih luas
- Filter berdasarkan tanggal untuk pencarian temporal

### Mobile Usage
- Sistem responsive untuk semua device
- Swipe kanan untuk buka sidebar (mobile)
- Touch dan hold untuk context menu
- Pull to refresh untuk update data

### Backup Data
- Export data secara berkala ke PDF
- Simpan file penting di cloud storage
- Dokumentasikan nomor-nomor penting
- Backup database oleh administrator

### Performance Tips
- Tutup tab yang tidak digunakan
- Clear cache browser secara berkala
- Gunakan koneksi internet stabil
- Update browser ke versi terbaru

## Troubleshooting

### Masalah Login
**Problem**: Tidak bisa login
**Solusi**:
- Pastikan username dan password benar
- Check caps lock
- Clear cookies dan cache browser
- Hubungi administrator jika masih bermasalah

**Problem**: Session timeout
**Solusi**:
- Login ulang
- Sistem otomatis logout setelah 1 jam tidak aktif
- Simpan pekerjaan secara berkala

### Masalah Upload File
**Problem**: File tidak bisa diupload
**Solusi**:
- Pastikan ukuran file tidak melebihi batas (5MB untuk gambar)
- Check format file yang didukung
- Pastikan koneksi internet stabil
- Coba refresh halaman dan upload ulang

### Masalah Tampilan
**Problem**: Tampilan tidak normal
**Solusi**:
- Refresh halaman (F5 atau Ctrl+R)
- Clear cache browser
- Coba browser lain
- Pastikan JavaScript enabled

### Masalah Pencarian
**Problem**: Hasil pencarian tidak akurat
**Solusi**:
- Gunakan kata kunci yang lebih spesifik
- Check spelling
- Coba pencarian dengan kata kunci berbeda
- Gunakan filter untuk mempersempit hasil

### Masalah Performance
**Problem**: Sistem lambat
**Solusi**:
- Check koneksi internet
- Tutup aplikasi lain yang tidak perlu
- Clear cache browser
- Restart browser
- Hubungi administrator jika masalah berlanjut

### Kontak Support
- **Email**: admin@instansi.go.id
- **Telepon**: (021) 1234-5678
- **WhatsApp**: 0812-3456-7890
- **Jam Kerja**: Senin-Jumat, 08:00-16:00 WIB

### FAQ

**Q: Bagaimana cara mengganti password?**
A: Hubungi administrator untuk reset password. Fitur self-service akan ditambahkan di update mendatang.

**Q: Apakah data aman?**
A: Ya, sistem menggunakan enkripsi dan backup otomatis. Data disimpan dengan standar keamanan tinggi.

**Q: Bisakah akses dari HP?**
A: Ya, sistem fully responsive dan dapat diakses dari smartphone dan tablet.

**Q: Bagaimana cara export laporan?**
A: Gunakan fitur export PDF pada setiap data, atau hubungi administrator untuk laporan khusus.

**Q: Apakah ada batasan jumlah data?**
A: Tidak ada batasan khusus, namun untuk performance optimal disarankan melakukan archive data lama secara berkala.
