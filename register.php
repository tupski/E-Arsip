<?php
include 'include/config.php';

if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!csrf_validate('register')) {
        $_SESSION['err'] = 'Token keamanan tidak valid. Silakan coba lagi.';
        header("Location: register.php");
        exit();
    }

    // Validate input
    $validator = validate($_POST);
    $validator->required('username', 'Username wajib diisi.')
             ->minLength('username', 3, 'Username minimal 3 karakter.')
             ->maxLength('username', 30, 'Username maksimal 30 karakter.')
             ->username('username', 'Username hanya boleh berisi huruf, angka, dan underscore.')
             ->required('password', 'Password wajib diisi.')
             ->minLength('password', env_int('PASSWORD_MIN_LENGTH', 8), 'Password minimal 8 karakter.')
             ->required('nama', 'Nama lengkap wajib diisi.')
             ->maxLength('nama', 100, 'Nama maksimal 100 karakter.')
             ->required('nip', 'NIP wajib diisi.')
             ->nip('nip', 'Format NIP tidak valid (harus 18 digit angka).');

    if ($validator->fails()) {
        $_SESSION['err'] = $validator->getFirstError();
        header("Location: register.php");
        exit();
    }

    $sanitized = $validator->getSanitizedData();
    $username = $sanitized['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Use secure password hashing
    $nama = $sanitized['nama'];
    $nip = $sanitized['nip'];

    // Check if username exists using prepared statement
    $check_stmt = mysqli_prepare($config, "SELECT username FROM tbl_user WHERE username = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if(mysqli_num_rows($check_result) > 0) {
        $_SESSION['err'] = 'Username sudah terdaftar!';
        mysqli_stmt_close($check_stmt);
        header("Location: register.php");
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Insert new user using prepared statement (default admin=0 for regular user)
    $insert_stmt = mysqli_prepare($config, "INSERT INTO tbl_user (username, password, nama, nip, admin) VALUES (?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($insert_stmt, "ssss", $username, $password, $nama, $nip);
    $query = mysqli_stmt_execute($insert_stmt);
    mysqli_stmt_close($insert_stmt);

    if($query) {
        $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['err'] = 'Gagal melakukan registrasi. Silakan coba lagi.';
        header("Location: register.php");
        exit();
    }
}

include 'include/head.php';
?>

<style>
    .register-wrapper {
        display: flex;
        min-height: 100vh;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: #f5f8fa;
    }
    
    .register-card {
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .register-header {
        text-align: center;
        padding: 20px;
        background: #1976d2;
        color: white;
    }
    
    .register-body {
        padding: 30px;
        background: white;
    }
    
    .btn-register {
        width: 100%;
        margin-top: 20px;
    }
</style>

<div class="register-wrapper">
    <div class="register-card">
        <div class="register-header">
            <h4>Daftar Akun Baru</h4>
        </div>
        
        <div class="register-body">
            <?php if (isset($_SESSION['err'])): ?>
                <div class="card-panel red lighten-4 red-text text-darken-4">
                    <?php echo $_SESSION['err']; unset($_SESSION['err']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <?php echo csrf_field('register'); ?>
                 <div class="input-field">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="nama" type="text" name="nama" required>
                    <label for="nama">Nama Lengkap</label>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">credit_card</i>
                    <input id="nip" type="text" name="nip" maxlength="18" pattern="[0-9]{18}" title="NIP harus berupa 18 digit angka" required>
                    <label for="nip">NIP (18 digit)</label>
                    <span class="helper-text" id="nip-helper">Masukkan 18 digit angka</span>
                </div>
                <div class="input-field">
                    <i class="material-icons prefix">person</i>
                    <input id="username" type="text" name="username" required>
                    <label for="username">Username</label>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">lock</i>
                    <input id="password" type="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                
                <button type="submit" name="submit" class="btn waves-effect waves-light blue darken-2 btn-register">
                    <i class="material-icons right">how_to_reg</i> Daftar
                </button>
                
                <div class="center" style="margin-top: 20px;">
                    <p>Sudah punya akun? <a href="index.php">Login disini</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validasi NIP - hanya angka dan maksimal 18 digit
document.getElementById('nip').addEventListener('input', function(e) {
    const nipHelper = document.getElementById('nip-helper');

    // Hapus karakter non-angka
    this.value = this.value.replace(/[^0-9]/g, '');

    // Batasi maksimal 18 digit
    if (this.value.length > 18) {
        this.value = this.value.slice(0, 18);
    }

    // Update helper text dengan feedback
    const length = this.value.length;
    if (length === 0) {
        nipHelper.textContent = 'Masukkan 18 digit angka';
        nipHelper.style.color = '#9e9e9e';
    } else if (length < 18) {
        nipHelper.textContent = `${length}/18 digit (kurang ${18 - length} digit)`;
        nipHelper.style.color = '#ff9800';
    } else if (length === 18) {
        nipHelper.textContent = '✓ NIP valid (18 digit)';
        nipHelper.style.color = '#4caf50';
    }
});

// Validasi form sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const nip = document.getElementById('nip').value;

    if (nip.length !== 18) {
        e.preventDefault();
        alert('NIP harus berupa 18 digit angka!');
        document.getElementById('nip').focus();
        return false;
    }

    if (!/^[0-9]{18}$/.test(nip)) {
        e.preventDefault();
        alert('NIP hanya boleh berisi angka!');
        document.getElementById('nip').focus();
        return false;
    }
});

// Auto-focus ke field berikutnya setelah NIP lengkap
document.getElementById('nip').addEventListener('input', function(e) {
    if (this.value.length === 18) {
        document.getElementById('username').focus();
    }
});
</script>

<?php include 'include/footer.php'; ?>