<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update_quantity':
            $index = intval($_POST['index']);
            $change = intval($_POST['change']);
            
            if (isset($_SESSION['cart'][$index])) {
                $new_quantity = $_SESSION['cart'][$index]['quantity'] + $change;
                
                if ($new_quantity <= 0) {
                    // 如果數量為0或負數，移除商品
                    unset($_SESSION['cart'][$index]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // 重新索引
                } else {
                    $_SESSION['cart'][$index]['quantity'] = $new_quantity;
                    $_SESSION['cart'][$index]['subtotal'] = ($_SESSION['cart'][$index]['unit_price'] + $_SESSION['cart'][$index]['toppings_price']) * $new_quantity;
                }
                
                echo json_encode(['success' => true, 'message' => '數量已更新']);
            } else {
                echo json_encode(['success' => false, 'message' => '商品不存在']);
            }
            break;
            
        case 'remove_item':
            $index = intval($_POST['index']);
            
            if (isset($_SESSION['cart'][$index])) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // 重新索引
                echo json_encode(['success' => true, 'message' => '商品已移除']);
            } else {
                echo json_encode(['success' => false, 'message' => '商品不存在']);
            }
            break;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'message' => '購物車已清空']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => '無效的操作']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '操作失敗：' . $e->getMessage()]);
}
?>