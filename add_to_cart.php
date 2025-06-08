<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

// 初始化購物車
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    $drink_id = intval($_POST['drink_id']);
    $drink_name = $_POST['drink_name'];
    $unit_price = floatval($_POST['unit_price']);
    $quantity = intval($_POST['quantity']);
    $ice_level = $_POST['ice_level'] ?? '正常冰';
    $sugar_level = $_POST['sugar_level'] ?? '正常糖';
    
    // 處理加料
    $toppings = $_POST['toppings'] ?? [];
    $toppings_text = '';
    $toppings_price = 0;
    
    if (!empty($toppings)) {
        $toppings_text = implode('、', $toppings);
        foreach ($toppings as $topping) {
            if (strpos($topping, '+10元') !== false) {
                $toppings_price += 10;
            } elseif (strpos($topping, '+15元') !== false) {
                $toppings_price += 15;
            }
        }
    }
    
    // 計算總價
    $item_total_price = ($unit_price + $toppings_price) * $quantity;
    
    // 創建購物車項目
    $cart_item = [
        'drink_id' => $drink_id,
        'drink_name' => $drink_name,
        'unit_price' => $unit_price,
        'quantity' => $quantity,
        'ice_level' => $ice_level,
        'sugar_level' => $sugar_level,
        'toppings' => $toppings_text,
        'toppings_price' => $toppings_price,
        'subtotal' => $item_total_price,
        'cart_key' => uniqid() // 用於識別購物車中的唯一項目
    ];
    
    // 加入購物車
    $_SESSION['cart'][] = $cart_item;
    
    // 計算購物車總數量
    $cart_count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => '成功加入購物車',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '加入購物車失敗：' . $e->getMessage()
    ]);
}
?>