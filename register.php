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
    $password = mysqli_real_escape_string($config, md5($_POST['password']));
    $nama = mysqli_real_escape_string($config, $_POST['nama']);
    $nip = mysqli_real_escape_string($config, $_POST['nip']);

    // Check if username exists
    $check = mysqli_query($config, "SELECT username FROM tbl_user WHERE username='$username'");
    if(mysqli_num_rows($check) > 0) {
        $_SESSION['err'] = 'Username sudah terdaftar!';
        header("Location: register.php");
        exit();
    }

    // Insert new user (default admin=0 for regular user)
    $query = mysqli_query($config, "INSERT INTO tbl_user (username, password, nama, nip, admin) 
              VALUES ('$username', '$password', '$nama', '$nip', 0)");

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