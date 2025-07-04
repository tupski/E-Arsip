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
    // Validate CSRF token
    if (!csrf_validate('login')) {
        $_SESSION['err'] = 'Token keamanan tidak valid. Silakan coba lagi.';
        header("Location: index.php");
        exit();
    }

    // Validate input
    $validator = validate($_POST);
    $validator->required('username', 'Username wajib diisi.')
             ->minLength('username', 3, 'Username minimal 3 karakter.')
             ->username('username', 'Format username tidak valid.')
             ->required('password', 'Password wajib diisi.')
             ->minLength('password', 6, 'Password minimal 6 karakter.');

    if ($validator->fails()) {
        $_SESSION['err'] = $validator->getFirstError();
        header("Location: index.php");
        exit();
    }

    $sanitized = $validator->getSanitizedData();
    $username = $sanitized['username'];
    $password = $_POST['password']; // Don't sanitize password, we'll hash it

    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($config, "SELECT id_user, username, password, nama, admin FROM tbl_user WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_array($result);

        // Verify password using password_verify for new hashes or fallback to MD5 for old hashes
        $password_valid = false;
        if (strlen($data['password']) === 32) {
            // Old MD5 hash - verify and update to new hash
            if (md5($password) === $data['password']) {
                $password_valid = true;
                // Update to new password hash
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($config, "UPDATE tbl_user SET password = ? WHERE id_user = ?");
                mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $data['id_user']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
        } else {
            // New password hash
            $password_valid = password_verify($password, $data['password']);
        }

        if ($password_valid) {
            // Regenerate session ID for security
            session_regenerate_id(true);

            $_SESSION['admin'] = $data['admin'];
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['login_time'] = time(); // Add login timestamp

            if ($data['admin'] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: halaman_user.php");
            }
            exit();
        }
    }

    $_SESSION['err'] = 'Username atau Password salah!';
    header("Location: index.php");
    exit();
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
                <?php echo csrf_field('login'); ?>
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