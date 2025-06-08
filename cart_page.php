<?php
session_start();
require_once 'config.php';

// 初始化購物車
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 計算總價
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['subtotal'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購物車 - MACU</title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #ff6b6b;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .back-btn:hover {
            transform: scale(1.05);
        }

        .cart-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .empty-cart h2 {
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .cart-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .item-name {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
        }

        .remove-btn {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .remove-btn:hover {
            background: #ff3742;
        }

        .item-details {
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .item-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #5a67d8;
        }

        .quantity-display {
            font-size: 16px;
            min-width: 30px;
            text-align: center;
        }

        .total-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
            text-align: center;
        }

        .total-amount {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .checkout-section {
            margin-top: 30px;
        }

        .customer-info {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .payment-option {
            background: white;
            border: 3px solid #ddd;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option:hover {
            border-color: #667eea;
        }

        .payment-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .checkout-btn {
            width: 100%;
            padding: 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            background: #218838;
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .back-btn {
                top: 10px;
                left: 10px;
                padding: 8px 15px;
            }
            
            .item-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .item-price {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">← 返回菜單</a>

    <div class="container">
        <div class="header">
            <h1>🛒 購物車</h1>
        </div>

        <div class="cart-container">
            <?php if (empty($_SESSION['cart'])): ?>
                <div class="empty-cart">
                    <h2>購物車是空的</h2>
                    <p>快去選擇您喜愛的飲品吧！</p>
                    <a href="index.php" style="display: inline-block; margin-top: 20px; padding: 12px 25px; background: #667eea; color: white; text-decoration: none; border-radius: 25px;">開始點餐</a>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <div class="cart-item">
                    <div class="item-header">
                        <div class="item-name"><?php echo htmlspecialchars($item['drink_name']); ?></div>
                        <button class="remove-btn" onclick="removeItem(<?php echo $index; ?>)">移除</button>
                    </div>
                    
                    <div class="item-details">
                        <div>冰塊：<?php echo htmlspecialchars($item['ice_level']); ?> | 甜度：<?php echo htmlspecialchars($item['sugar_level']); ?></div>
                        <?php if (!empty($item['toppings'])): ?>
                        <div>加料：<?php echo htmlspecialchars($item['toppings']); ?></div>
                        <?php endif; ?>
                        <div>單價：NT$ <?php echo formatPrice($item['unit_price'] + $item['toppings_price']); ?></div>
                    </div>
                    
                    <div class="item-price">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $index; ?>, -1)">-</button>
                            <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $index; ?>, 1)">+</button>
                        </div>
                        <div>小計：NT$ <?php echo formatPrice($item['subtotal']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="total-section">
                    <div class="total-amount">總計：NT$ <?php echo formatPrice($total_amount); ?></div>
                </div>

                <div class="checkout-section">
                    <form id="checkoutForm" onsubmit="processOrder(event)">
                        <div class="customer-info">
                            <h3 style="margin-bottom: 15px; color: #333;">客戶資訊</h3>
                            <div class="form-group">
                                <label for="customer_phone">聯絡電話：</label>
                                <input type="tel" id="customer_phone" name="customer_phone" placeholder="請輸入聯絡電話">
                            </div>
                            <div class="form-group">
                                <label for="notes">備註：</label>
                                <textarea id="notes" name="notes" rows="3" placeholder="特殊需求或備註事項"></textarea>
                            </div>
                        </div>

                        <h3 style="margin-bottom: 15px; color: white; text-align: center;">選擇付款方式</h3>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="現金" required>
                                <div class="payment-icon">💰</div>
                                <div>現金付款</div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="LinePay" required>
                                <div class="payment-icon">📱</div>
                                <div>LINE Pay</div>
                            </label>
                        </div>

                        <button type="submit" class="checkout-btn">確認訂單</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // 付款方式選擇
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        function updateQuantity(index, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&index=${index}&change=${change}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('更新失敗：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('發生錯誤，請重試');
            });
        }

        function removeItem(index) {
            if (confirm('確定要移除此商品嗎？')) {
                fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_item&index=${index}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('移除失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('發生錯誤，請重試');
                });
            }
        }

        function processOrder(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('checkoutForm'));
            
            fetch('process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('訂單建立成功！訂單編號：' + data.order_number);
                    window.location.href = 'order_success.php?order_id=' + data.order_id;
                } else {
                    alert('訂單建立失敗：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('發生錯誤，請重試');
            });
        }
    </script>
</body>
</html>