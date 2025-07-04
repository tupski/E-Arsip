<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Kesalahan - E-Arsip</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }
        
        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        
        .error-icon {
            font-size: 80px;
            color: #f44336;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 300;
            color: #333;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
            color: #333;
        }
        
        .error-code {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        @media (max-width: 600px) {
            .error-container {
                padding: 30px 20px;
            }
            
            .error-title {
                font-size: 24px;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">Error 500</div>
        
        <i class="material-icons error-icon">error_outline</i>
        
        <h1 class="error-title">Oops! Terjadi Kesalahan</h1>
        
        <p class="error-message">
            Maaf, terjadi kesalahan pada server. Tim teknis kami telah diberitahu dan sedang menangani masalah ini.
            Silakan coba lagi dalam beberapa saat.
        </p>
        
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn-custom btn-secondary">
                <i class="material-icons">arrow_back</i>
                Kembali
            </a>
            
            <a href="index.php" class="btn-custom">
                <i class="material-icons">home</i>
                Beranda
            </a>
            
            <a href="javascript:location.reload()" class="btn-custom btn-secondary">
                <i class="material-icons">refresh</i>
                Muat Ulang
            </a>
        </div>
    </div>
    
    <script>
        // Auto refresh after 30 seconds
        setTimeout(function() {
            if (confirm('Halaman akan dimuat ulang otomatis. Lanjutkan?')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
