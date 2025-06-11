<?php
session_start();
require_once 'config.php';

// 檢查是否已登入
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// 處理訂單狀態更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success_message = "訂單狀態已更新";
    } catch (Exception $e) {
        $error_message = "更新失敗：" . $e->getMessage();
    }
}

// 獲取訂單列表
try {
    $pdo = getDBConnection();
    
    // 搜尋條件
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "order_status = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $stmt = $pdo->prepare("SELECT * FROM orders $where_clause ORDER BY created_at DESC");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = "資料庫錯誤：" . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單管理 - MACU</title>
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

        .search-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .orders-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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

        .status-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }

        .update-btn {
            padding: 5px 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
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

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📋 訂單管理</h1>
            <nav class="header-nav">
                <a href="admin_dashboard.php">儀表板</a>
                <a href="admin_orders.php">訂單管理</a>
                <a href="admin_drinks.php">商品管理</a>
                <a href="index.php">回到前台</a>
                <a href="admin_logout.php">登出</a>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- 訊息顯示 -->
        <?php if (isset($success_message)): ?>
        <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- 搜尋區域 -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="search">搜尋訂單：</label>
                    <input type="text" id="search" name="search" placeholder="訂單編號、客戶姓名或電話" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label for="status">訂單狀態：</label>
                    <select id="status" name="status">
                        <option value="">全部狀態</option>
                        <option value="處理中" <?php echo $status_filter === '處理中' ? 'selected' : ''; ?>>處理中</option>
                        <option value="製作中" <?php echo $status_filter === '製作中' ? 'selected' : ''; ?>>製作中</option>
                        <option value="已完成" <?php echo $status_filter === '已完成' ? 'selected' : ''; ?>>已完成</option>
                        <option value="已取消" <?php echo $status_filter === '已取消' ? 'selected' : ''; ?>>已取消</option>
                    </select>
                </div>
                <button type="submit" class="search-btn">搜尋</button>
            </form>
        </div>

        <!-- 訂單列表 -->
        <div class="orders-section">
            <h2>訂單列表 (共 <?php echo count($orders); ?> 筆)</h2>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>訂單編號</th>
                        <th>客戶資訊</th>
                        <th>金額</th>
                        <th>付款方式</th>
                        <th>訂單狀態</th>
                        <th>下單時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['customer_name'] ?? '未提供'); ?></strong><br>
                            <?php echo htmlspecialchars($order['customer_phone'] ?? '未提供'); ?>
                        </td>
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
                        <td><?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="new_status" class="status-select">
                                    <option value="處理中" <?php echo $order['order_status'] === '處理中' ? 'selected' : ''; ?>>處理中</option>
                                    <option value="製作中" <?php echo $order['order_status'] === '製作中' ? 'selected' : ''; ?>>製作中</option>
                                    <option value="已完成" <?php echo $order['order_status'] === '已完成' ? 'selected' : ''; ?>>已完成</option>
                                    <option value="已取消" <?php echo $order['order_status'] === '已取消' ? 'selected' : ''; ?>>已取消</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">更新</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
