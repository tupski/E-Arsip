<?php
include 'include/config.php';
if (isset($_SESSION['admin'])) {
    if ($_SESSION['admin'] == 1) {
        header("Location: admin.php");
    } else {
        header("Location: halaman_user.php");
    }
    exit();
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($config, $_POST['username']);
    $password = mysqli_real_escape_string($config, md5($_POST['password']));
    
    $query = mysqli_query($config, "SELECT * FROM tbl_user WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);
        $_SESSION['admin'] = $data['admin'];
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];
        
        if ($data['admin'] == 1) {
            header("Location: admin.php");
        } else {
            header("Location: halaman_user.php");
        }
        exit();
    } else {
        $_SESSION['err'] = 'Username atau Password salah!';
        header("Location: index.php");
        exit();
    }
}

include 'include/head.php';
?>

<style>
    body {
        display: flex;
        min-height: 100vh;
        flex-direction: column;
        background: #f5f8fa;
        margin: 0;
        padding: 0;
    }
    
    .login-wrapper {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .login-card {
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin: 0 auto;
    }
    
    .card-content {
        padding: 40px;
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-logo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e3f2fd;
        padding: 5px;
        background: white;
    }
    
    .input-field {
        margin-bottom: 25px;
    }
    
    .btn-login {
        width: 100%;
        border-radius: 8px;
        height: 48px;
        font-size: 1rem;
    }
    
    .error-message {
        border-radius: 8px;
        margin-bottom: 25px;
    }
    
    .register-link {
        text-align: center;
        margin-top: 20px;
    }
    
    .register-link a {
        color: #1976d2;
        font-weight: 500;
    }
</style>

<div class="login-wrapper">
    <div class="login-card card">
        <div class="card-content">
            <div class="login-header">
                <img src="assets/img/logo2.png" class="login-logo responsive-img">
                <h4 class="blue-text text-darken-2" style="margin-top: 15px;">SIPAK PMDTK</h4>
                <p class="grey-text">Dinas Tenaga Kerja</p>
                <div class="divider" style="margin: 20px 0;"></div>
            </div>

            <?php if (isset($_SESSION['err'])): ?>
                <div class="error-message card-panel red lighten-4 red-text text-darken-4">
                    <i class="material-icons left">error</i>
                    <?php echo $_SESSION['err']; unset($_SESSION['err']); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="input-field">
                    <i class="material-icons prefix blue-text">person</i>
                    <input id="username" type="text" class="validate" name="username" required>
                    <label for="username">Username</label>
                </div>

                <div class="input-field">
                    <i class="material-icons prefix blue-text">lock</i>
                    <input id="password" type="password" class="validate" name="password" required>
                    <label for="password">Password</label>
                </div>

                <div class="center-align" style="margin-top: 10px;">
                    <button type="submit" name="submit" class="btn waves-effect waves-light blue darken-2 btn-login">
                        <i class="material-icons right">login</i> LOGIN
                    </button>
                </div>

                <div class="register-link">
                    <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>