<?php
require_once 'config.php';

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // 獲取訂單資訊
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: index.php');
        exit;
    }
    
    // 獲取訂單項目
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單成功 - MACU</title>
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
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .success-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4em;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 2.2em;
            color: #333;
            margin-bottom: 15px;
        }

        .order-number {
            font-size: 1.5em;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 30px;
            padding: 15px;
            background: #f0f4ff;
            border-radius: 10px;
        }

        .order-details {
            text-align: left;
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
        }

        .detail-value {
            color: #333;
        }

        .order-items {
            margin-top: 20px;
        }

        .item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .item-details {
            font-size: 0.9em;
            color: #666;
            line-height: 1.5;
        }

        .item-price {
            text-align: right;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }

        .total-amount {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .status-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .status-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }

        .status-text {
            color: #856404;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .success-card {
                padding: 25px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✅</div>
            <h1 class="success-title">訂單建立成功！</h1>
            
            <div class="order-number">
                訂單編號：<?php echo htmlspecialchars($order['order_number']); ?>
            </div>

            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">訂單時間：</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">付款方式：</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">付款狀態：</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['payment_status']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">訂單狀態：</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['order_status