<?php
session_start();
require_once 'config.php';

// 檢查是否已登入
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// 獲取統計數據
try {
    $pdo = getDBConnection();
    
    // 今日訂單數
    $stmt = $pdo->query("SELECT COUNT(*) as today_orders FROM orders WHERE DATE(created_at) = CURDATE()");
    $today_orders = $stmt->fetch()['today_orders'];
    
    // 今日營收
    $stmt = $pdo->query("SELECT SUM(total_amount) as today_revenue FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = '已付款'");
    $today_revenue = $stmt->fetch()['today_revenue'] ?? 0;
    
    // 總商品數
    $stmt = $pdo->query("SELECT COUNT(*) as total_drinks FROM drinks WHERE is_available = 1");
    $total_drinks = $stmt->fetch()['total_drinks'];
    
    // 待處理訂單數
    $stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE order_status = '處理中'");
    $pending_orders = $stmt->fetch()['pending_orders'];
    
    // 最近訂單
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
    $recent_orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = "資料庫錯誤：" . $e->getMessage();
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
            background: #f5f5f5;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.8em;
        }

        .header-nav {
            display: flex;
            gap: 20px;
        }

        .header-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .header-nav a:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 1.1em;
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section h2 {
            margin-bottom: 25px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .orders-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .orders-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .admin-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .admin-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .admin-btn:hover {
            transform: translateY(-2px);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🛠️ MACU 後台管理</h1>
            <nav class="header-nav">
                <a href="admin_dashboard.php">儀表板</a>
                <a href="admin_orders.php">訂單管理</a>
                <a href="admin_drinks.php">商品管理</a>
                <a href="index.php">回到前台</a>
                <a href="admin_logout.php" class="logout-btn">登出</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- 統計卡片 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?php echo $today_orders; ?></div>
                <div class="stat-label">今日訂單</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value">NT$ <?php echo number_format($today_revenue); ?></div>
                <div class="stat-label">今日營收</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧋</div>
                <div class="stat-value"><?php echo $total_drinks; ?></div>
                <div class="stat-label">商品總數</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-value"><?php echo $pending_orders; ?></div>
                <div class="stat-label">待處理訂單</div>
            </div>
        </div>

        <!-- 管理功能 -->
        <div class="admin-actions">
            <a href="admin_orders.php" class="admin-btn">📋 訂單管理</a>
            <a href="admin_drinks.php" class="admin-btn">🧋 商品管理</a>
            <a href="admin_reports.php" class="admin-btn">📈 營收報表</a>
        </div>

        <!-- 最近訂單 -->
        <div class="section">
            <h2>最近訂單</h2>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>客戶姓名</th>
                        <th>聯絡電話</th>
                        <th>金額</th>
                        <th>付款方式</th>
                        <th>狀態</th>
                        <th>下單時間</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?? '未提供'); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_phone'] ?? '未提供'); ?></td>
                        <td>NT$ <?php echo number_format($order['total_amount']); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                        <td>
                            <span class="status-badge 
                                <?php 
                                    switch($order['order_status']) {
                                        case '處理中': echo 'status-pending'; break;
                                        case '製作中': echo 'status-processing'; break;
                                        case '已完成': echo 'status-completed'; break;
                                        default: echo 'status-pending';
                                    }
                                ?>">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('m/d H:i', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
