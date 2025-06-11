<?php
session_start();

// 檢查是否已登入
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 簡單的管理員驗證（實際應用中應使用資料庫和加密）
    if ($username === 'admin' && $password === 'macu2024') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $login_error = '帳號或密碼錯誤！';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理 - MACU</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 8px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🛠️ 後台管理</h1>
            <p>MACU 飲料店管理系統</p>
        </div>

        <?php if ($login_error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($login_error); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">管理員帳號：</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">密碼：</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">登入</button>
        </form>

        <div class="back-link">
            <a href="index.php">← 回到首頁</a>
        </div>
    </div>
</body>
</html>
