<?php
require_once 'config.php';
session_start();

// 初始化購物車
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 獲取飲料數據
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM drinks WHERE category != '🖼️ 圖片檔（介面與標誌）' AND is_available = 1 ORDER BY category, name");
$drinks = $stmt->fetchAll();

// 按分類分組
$categories = [];
foreach ($drinks as $drink) {
    $categories[$drink['category']][] = $drink;
}

// 計算購物車數量
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MACU 點餐系統</title>
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

        .cart-info {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff6b6b;
            color: white;
            padding: 15px 20px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .cart-info:hover {
            transform: scale(1.05);
        }

        .menu-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            gap: 30px;
        }

        .category {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .category-title {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .drinks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .drink-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .drink-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .drink-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .drink-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .drink-price {
            font-size: 1.1em;
            color: #ff6b6b;
            font-weight: bold;
        }
        }

        .drink-item:hover .drink-content {
            opacity: 0.3;
        }

        .drink-content {
            transition: opacity 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .option-group {
            margin-bottom: 20px;
        }

        .option-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .option-group select, .option-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #5a67d8;
        }

        .quantity-display {
            font-size: 18px;
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .btn-secondary {
            background: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ccc;
        }

        @media (max-width: 768px) {
            .drinks-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .cart-info {
                top: 10px;
                right: 10px;
                padding: 10px 15px;
            }
            
            .header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧋 MACU 飲料店</h1>
        <p>歡迎光臨，請選擇您喜愛的飲品</p>
    </div>

    <div class="cart-info" onclick="window.location.href='cart_page.php'">
        🛒 購物車 (<span id="cart-count"><?php echo $cart_count; ?></span>)
    </div>

    <!-- 管理員入口 -->
    <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <a href="admin_login.php" style="background: rgba(0,0,0,0.7); color: white; padding: 10px 15px; border-radius: 25px; text-decoration: none; font-size: 12px; display: inline-block;">
            🛠️ 管理員
        </a>
    </div>

    <div class="menu-container">
        <?php foreach ($categories as $category => $drinks_in_category): ?>
        <div class="category">
            <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
            <div class="drinks-grid">
                <?php foreach ($drinks_in_category as $drink): ?>
                <div class="drink-item" onclick="openOrderModal(<?php echo $drink['id']; ?>, '<?php echo htmlspecialchars($drink['name']); ?>', <?php echo $drink['price']; ?>, '<?php echo htmlspecialchars($drink['image_name']); ?>')">
                    <img class="drink-image" src="images/<?php echo htmlspecialchars($drink['image_name']); ?>" alt="<?php echo htmlspecialchars($drink['name']); ?>" onerror="this.style.display='none'">
                    <div class="drink-name"><?php echo htmlspecialchars($drink['name']); ?></div>
                    <div class="drink-price">NT$ <?php echo formatPrice($drink['price']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 訂購Modal -->
    <div id="orderModal" class="order-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-drink-name"></h2>
                <p id="modal-drink-price" style="color: #ff6b6b; font-size: 1.2em; font-weight: bold;"></p>
            </div>
            
            <form id="orderForm">
                <input type="hidden" id="drink-id" name="drink_id">
                
                <div class="option-group">
                    <label for="ice-level">冰塊程度：</label>
                    <select id="ice-level" name="ice_level">
                        <option value="正常冰">正常冰</option>
                        <option value="少冰">少冰</option>
                        <option value="微冰">微冰</option>
                        <option value="去冰">去冰</option>
                        <option value="熱飲">熱飲</option>
                    </select>
                </div>

                <div class="option-group">
                    <label for="sugar-level">甜度：</label>
                    <select id="sugar-level" name="sugar_level">
                        <option value="正常糖">正常糖</option>
                        <option value="少糖">少糖</option>
                        <option value="半糖">半糖</option>
                        <option value="微糖">微糖</option>
                        <option value="無糖">無糖</option>
                    </select>
                </div>

                <div class="option-group">
                    <label for="toppings">加料 (可多選)：</label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                            <input type="checkbox" name="toppings[]" value="珍珠 (+10元)"> 珍珠 (+10元)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                            <input type="checkbox" name="toppings[]" value="椰果 (+10元)"> 椰果 (+10元)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                            <input type="checkbox" name="toppings[]" value="仙草 (+10元)"> 仙草 (+10元)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                            <input type="checkbox" name="toppings[]" value="布丁 (+15元)"> 布丁 (+15元)
                        </label>
                    </div>
                </div>

                <div class="quantity-controls">
                    <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                    <span class="quantity-display" id="quantity">1</span>
                    <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeOrderModal()">取消</button>
                    <button type="button" class="btn btn-primary" onclick="addToCart()">加入購物車</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentDrink = {};
        let quantity = 1;

        function openOrderModal(id, name, price, image) {
            currentDrink = {id, name, price, image};
            document.getElementById('drink-id').value = id;
            document.getElementById('modal-drink-name').textContent = name;
            document.getElementById('modal-drink-price').textContent = 'NT$ ' + price.toLocaleString();
            document.getElementById('quantity').textContent = '1';
            quantity = 1;
            
            // 重置表單
            document.getElementById('orderForm').reset();
            document.getElementById('drink-id').value = id;
            
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function changeQuantity(change) {
            quantity = Math.max(1, quantity + change);
            document.getElementById('quantity').textContent = quantity;
        }

        function addToCart() {
            const formData = new FormData(document.getElementById('orderForm'));
            formData.append('quantity', quantity);
            formData.append('drink_name', currentDrink.name);
            formData.append('unit_price', currentDrink.price);

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                    closeOrderModal();
                    alert('已加入購物車！');
                } else {
                    alert('加入購物車失敗：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('發生錯誤，請重試');
            });
        }

        // 點擊modal外部關閉
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>
</body>
</html>