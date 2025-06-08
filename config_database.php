<?php
// 資料庫連接設定
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // 請根據您的設定修改
define('DB_PASS', '');      // 請根據您的設定修改
define('DB_NAME', 'macu_pos');

// 建立資料庫連接
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("資料庫連接失敗: " . $e->getMessage());
    }
}

// 生成訂單編號
function generateOrderNumber() {
    return 'MACU' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// 格式化價格
function formatPrice($price) {
    return number_format($price, 0);
}
?>