<?php
//session
if(!isset($_SESSION['admin'])){
    $_SESSION['err'] = '<center>Anda harus login terlebih dahulu!</center>';
    echo '<script>alert("Anda harus login terlebih dahulu!"); window.location.href="./";</script>';
    die();
} else {

    if(isset($_REQUEST['submit'])){
        // Validate CSRF token
        if (!csrf_validate('tambah_user')) {
            $_SESSION['errEmpty'] = 'Token keamanan tidak valid. Silakan coba lagi.';
            echo '<script>window.history.back();</script>';
            die();
        }

        // Validate input
        $validator = validate($_REQUEST);
        $validator->required('username', 'Username wajib diisi.')
                 ->minLength('username', 3, 'Username minimal 3 karakter.')
                 ->maxLength('username', 30, 'Username maksimal 30 karakter.')
                 ->username('username', 'Username hanya boleh berisi huruf, angka, dan underscore.')
                 ->required('password', 'Password wajib diisi.')
                 ->minLength('password', env_int('PASSWORD_MIN_LENGTH', 8), 'Password minimal 8 karakter.')
                 ->required('nama', 'Nama lengkap wajib diisi.')
                 ->maxLength('nama', 100, 'Nama maksimal 100 karakter.')
                 ->required('nip', 'NIP wajib diisi.')
                 ->nip('nip', 'Format NIP tidak valid (harus 18 digit angka).')
                 ->required('admin', 'Level admin wajib dipilih.')
                 ->integer('admin', 'Level admin harus berupa angka.');

        if ($validator->fails()) {
            $_SESSION['errEmpty'] = $validator->getFirstError();
            echo '<script>window.history.back();</script>';
            die();
        }

        $sanitized = $validator->getSanitizedData();
        $username = $sanitized['username'];
        $password = password_hash($_REQUEST['password'], PASSWORD_DEFAULT); // Use secure password hashing
        $nama = $sanitized['nama'];
        $nip = $sanitized['nip'];
        $admin = (int)$sanitized['admin'];

        //validasi input username using prepared statement
        $check_stmt = mysqli_prepare($config, "SELECT username FROM tbl_user WHERE username = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if(mysqli_num_rows($check_result) > 0){
            $_SESSION['errUser'] = 'Username sudah terdaftar!';
            mysqli_stmt_close($check_stmt);
            echo '<script>window.history.back();</script>';
            die();
        } else {
            mysqli_stmt_close($check_stmt);

            $insert_stmt = mysqli_prepare($config, "INSERT INTO tbl_user(username, password, nama, nip, admin) VALUES(?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "ssssi", $username, $password, $nama, $nip, $admin);
            $query = mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);

            if($query == true){
                $_SESSION['succAdd'] = 'SUKSES! Data user berhasil ditambahkan';
                echo '<script>window.location.href="./admin.php?page=usr";</script>';
                die();
            } else {
                $_SESSION['errQ'] = 'ERROR! Ada masalah dengan query';
                echo '<script>window.history.back();</script>';
                die();
            }
        }
    }
    } else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --danger-color: #e74c3c;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            padding: 0;
        }

        .form-container {
            width: 100%;
            max-width: 100%;
            padding: 20px;
            box-sizing: border-box;
            flex: 1;
        }

        .form-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
            width: 100%;
        }

        .form-body {
            padding: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 15px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(72, 149, 239, 0.2);
        }

        select.form-control {
            height: 42px;
            padding: 10px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e91e63;
        }

        .icon-prefix {
            margin-right: 10px;
            color: #777;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-group {
                min-width: 100%;
            }
            
            .btn {
                padding: 8px 15px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="material-icons">person_add</i> TAMBAH USER</h1>
                </div>
                
                <div class="form-body">
                    <?php
                    if(isset($_SESSION['errEmpty'])){
                        echo '<div class="alert alert-danger">
                                <i class="material-icons">error</i>
                                '.$_SESSION['errEmpty'].'
                              </div>';
                        unset($_SESSION['errEmpty']);
                    }
                    if(isset($_SESSION['errUser'])){
                        echo '<div class="alert alert-danger">
                                <i class="material-icons">error</i>
                                '.$_SESSION['errUser'].'
                              </div>';
                        unset($_SESSION['errUser']);
                    }
                    if(isset($_SESSION['errQ'])){
                        echo '<div class="alert alert-danger">
                                <i class="material-icons">error</i>
                                '.$_SESSION['errQ'].'
                              </div>';
                        unset($_SESSION['errQ']);
                    }
                    ?>
                    
                    <form method="post" action="?page=usr&act=add">
                        <?php echo csrf_field('tambah_user'); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username"><i class="material-icons icon-prefix">account_circle</i> Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password"><i class="material-icons icon-prefix">lock</i> Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama"><i class="material-icons icon-prefix">person</i> Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip"><i class="material-icons icon-prefix">looks_one</i> NIP</label>
                                <input type="text" id="nip" name="nip" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="admin"><i class="material-icons icon-prefix">verified_user</i> Level User</label>
                                <select id="admin" name="admin" class="form-control" required>
                                    <option value="" disabled selected>Pilih Level User</option>
                                    <option value="1">Administrator</option>
                                    <option value="0">User Biasa</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="material-icons">save</i> Simpan
                            </button>
                            <a href="?page=usr" class="btn btn-danger">
                                <i class="material-icons">cancel</i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
    }
}
?>