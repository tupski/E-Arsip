# 📚 Panduan Penggunaan E-Arsip SIPAK PMDTK

## 🚀 Akses Aplikasi

**URL**: `http://localhost/E-arsip/`

## 🔐 Login Default

### Admin
- **Username**: `admin`
- **Password**: `password`
- **Akses**: Semua fitur (CRUD semua data)

### User Biasa
- **Username**: `user`
- **Password**: `password`
- **Akses**: Terbatas (sesuai role)

> ⚠️ **PENTING**: Segera ganti password default setelah login pertama!

## 📋 Fitur Utama

### 1. **Manajemen Kendaraan**
- ➕ Tambah data kendaraan
- ✏️ Edit informasi kendaraan
- 🗑️ Hapus data kendaraan
- 📊 Lihat daftar kendaraan
- 🔍 Pencarian dan filter

### 2. **Berita Acara**
- 📝 Buat berita acara baru
- ✏️ Edit berita acara
- 🗑️ Hapus berita acara
- 📄 Generate PDF berita acara
- 📊 Laporan berita acara

### 3. **Manajemen User** (Admin Only)
- 👥 Kelola user
- 🔐 Reset password
- 🎭 Atur role user
- ✅ Aktivasi/deaktivasi user

### 4. **Pengaturan Instansi**
- 🏢 Update informasi instansi
- 🖼️ Upload logo instansi
- 📧 Konfigurasi kontak

## 🛠️ Langkah Pertama Setelah Login

### Untuk Admin:
1. **Ganti Password**
   - Masuk ke Profil → Ganti Password
   
2. **Update Informasi Instansi**
   - Menu Pengaturan → Data Instansi
   - Isi nama instansi, alamat, kepala dinas, dll.
   - Upload logo instansi
   
3. **Buat User Baru** (jika diperlukan)
   - Menu User → Tambah User
   - Tentukan role (Admin/User)

### Untuk User:
1. **Ganti Password**
   - Masuk ke Profil → Ganti Password
   
2. **Mulai Input Data**
   - Tambah data kendaraan
   - Buat berita acara

## 📊 Tips Penggunaan

### Data Kendaraan:
- Pastikan nomor polisi unik
- Isi semua field yang wajib
- Upload foto kendaraan jika tersedia

### Berita Acara:
- Gunakan nomor berita acara yang sistematis
- Pastikan data pemakai lengkap
- Cek kembali sebelum generate PDF

### Keamanan:
- Logout setelah selesai menggunakan
- Jangan share kredensial login
- Backup data secara berkala

## 🔧 Troubleshooting

### Masalah Login:
- Pastikan username/password benar
- Clear browser cache
- Cek caps lock

### Error Database:
- Pastikan MySQL service berjalan
- Cek koneksi database di config

### File Upload Error:
- Cek ukuran file (max 1MB)
- Format file yang diizinkan: jpg, jpeg, png, gif, pdf
- Pastikan folder upload writable

## 📞 Support

Jika mengalami masalah:
1. Cek log error di folder `logs/`
2. Restart AMPPS services
3. Hubungi administrator sistem

## 🔄 Maintenance

### Backup Rutin:
- Export database secara berkala
- Backup folder upload
- Simpan file konfigurasi

### Update:
- Cek update aplikasi secara berkala
- Test di environment development dulu
- Backup sebelum update

---

**Selamat menggunakan E-Arsip SIPAK PMDTK!** 🎉
