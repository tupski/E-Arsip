<?php
include 'include/config.php';

if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    // Validate empty fields
    if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['nama']) || empty($_POST['nip'])) {
        $_SESSION['err'] = 'Semua field harus diisi!';
        header("Location: register.php");
        exit();
    }

    $username = mysqli_real_escape_string($config, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Use secure password hashing
    $nama = mysqli_real_escape_string($config, $_POST['nama']);
    $nip = mysqli_real_escape_string($config, $_POST['nip']);

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
                 <div class="input-field">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="nama" type="text" name="nama" required>
                    <label for="nama">Nama Lengkap</label>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">credit_card</i>
                    <input id="nip" type="text" name="nip" required>
                    <label for="nip">NIP</label>
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

<?php include 'include/footer.php'; ?>