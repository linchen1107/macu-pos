<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => '購物車為空']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // 獲取表單數據
    $payment_method = $_POST['payment_method'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // 驗證付款方式
    if (!in_array($payment_method, ['現金', 'LinePay'])) {
        throw new Exception('無效的付款方式');
    }
    
    // 計算訂單總金額
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['subtotal'];
    }
    
    // 生成訂單編號
    $order_number = generateOrderNumber();
    
    // 插入訂單
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_number, total_amount, payment_method, customer_phone, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$order_number, $total_amount, $payment_method, $customer_phone, $notes]);
    
    $order_id = $pdo->lastInsertId();
    
    // 插入訂單項目
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, drink_id, drink_name, quantity, unit_price, subtotal, ice_level, sugar_level, toppings) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($_SESSION['cart'] as $item) {
        $stmt->execute([
            $order_id,
            $item['drink_id'],
            $item['drink_name'],
            $item['quantity'],
            $item['unit_price'] + $item['toppings_price'],
            $item['subtotal'],
            $item['ice_level'],
            $item['sugar_level'],
            $item['toppings']
        ]);
    }
    
    $pdo->commit();
    
    // 清空購物車
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => '訂單建立成功',
        'order_id' => $order_id,
        'order_number' => $order_number
    ]);
    
} catch (Exception $e) {
    $pdo->rollback();
    echo json_encode([
        'success' => false,
        'message' => '訂單建立失敗：' . $e->getMessage()
    ]);
}
?>