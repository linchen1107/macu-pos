<?php
session_start();
require_once 'config.php';

// 檢查是否已登入
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// 處理商品操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDBConnection();
        
        if (isset($_POST['toggle_availability'])) {
            $drink_id = intval($_POST['drink_id']);
            $stmt = $pdo->prepare("UPDATE drinks SET is_available = IF(is_available = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$drink_id]);
            $success_message = "商品狀態已更新";
        }
        
        if (isset($_POST['update_price'])) {
            $drink_id = intval($_POST['drink_id']);
            $new_price = floatval($_POST['new_price']);
            $stmt = $pdo->prepare("UPDATE drinks SET price = ? WHERE id = ?");
            $stmt->execute([$new_price, $drink_id]);
            $success_message = "價格已更新";
        }
        
    } catch (Exception $e) {
        $error_message = "操作失敗：" . $e->getMessage();
    }
}

// 獲取商品列表
try {
    $pdo = getDBConnection();
    
    $category_filter = $_GET['category'] ?? '';
    $availability_filter = $_GET['availability'] ?? '';
    
    $where_conditions = ["category != '🖼️ 圖片檔（介面與標誌）'"];
    $params = [];
    
    if (!empty($category_filter)) {
        $where_conditions[] = "category = ?";
        $params[] = $category_filter;
    }
    
    if ($availability_filter !== '') {
        $where_conditions[] = "is_available = ?";
        $params[] = intval($availability_filter);
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    $stmt = $pdo->prepare("SELECT * FROM drinks $where_clause ORDER BY category, name");
    $stmt->execute($params);
    $drinks = $stmt->fetchAll();
    
    // 獲取所有分類
    $stmt = $pdo->query("SELECT DISTINCT category FROM drinks WHERE category != '🖼️ 圖片檔（介面與標誌）' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    $error_message = "資料庫錯誤：" . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - MACU</title>
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

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-form {
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

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .drinks-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .drinks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .drink-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            background: #f9f9f9;
            transition: transform 0.3s ease;
        }

        .drink-card:hover {
            transform: translateY(-2px);
        }

        .drink-card.unavailable {
            opacity: 0.6;
            background: #f5f5f5;
        }

        .drink-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .drink-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .drink-info h3 {
            margin-bottom: 5px;
            color: #333;
        }

        .drink-category {
            color: #666;
            font-size: 0.9em;
        }

        .drink-price {
            font-size: 1.2em;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 15px;
        }

        .drink-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .price-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .price-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-toggle {
            background: #28a745;
            color: white;
        }

        .btn-toggle.unavailable {
            background: #dc3545;
        }

        .btn-update {
            background: #007bff;
            color: white;
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
            <h1>🧋 商品管理</h1>
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

        <!-- 篩選區域 -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="category">商品分類：</label>
                    <select id="category" name="category">
                        <option value="">全部分類</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" 
                                <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="availability">販售狀態：</label>
                    <select id="availability" name="availability">
                        <option value="">全部狀態</option>
                        <option value="1" <?php echo $availability_filter === '1' ? 'selected' : ''; ?>>上架中</option>
                        <option value="0" <?php echo $availability_filter === '0' ? 'selected' : ''; ?>>已下架</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">篩選</button>
            </form>
        </div>

        <!-- 商品列表 -->
        <div class="drinks-section">
            <h2>商品列表 (共 <?php echo count($drinks); ?> 項)</h2>
            <div class="drinks-grid">
                <?php foreach ($drinks as $drink): ?>
                <div class="drink-card <?php echo $drink['is_available'] ? '' : 'unavailable'; ?>">
                    <div class="drink-header">
                        <img class="drink-image" src="images/<?php echo htmlspecialchars($drink['image_name']); ?>" 
                             alt="<?php echo htmlspecialchars($drink['name']); ?>" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjZjBmMGYwIi8+Cjx0ZXh0IHg9IjMwIiB5PSIzNSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7msqHmnInlnZnniYc8L3RleHQ+Cjwvc3ZnPgo='">
                        <div class="drink-info">
                            <h3><?php echo htmlspecialchars($drink['name']); ?></h3>
                            <div class="drink-category"><?php echo htmlspecialchars($drink['category']); ?></div>
                        </div>
                    </div>
                    
                    <div class="drink-price">NT$ <?php echo number_format($drink['price']); ?></div>
                    
                    <!-- 價格更新 -->
                    <form method="POST" class="price-form">
                        <input type="hidden" name="drink_id" value="<?php echo $drink['id']; ?>">
                        <span>新價格：</span>
                        <input type="number" name="new_price" class="price-input" value="<?php echo $drink['price']; ?>" step="0.01" min="0">
                        <button type="submit" name="update_price" class="btn btn-update">更新價格</button>
                    </form>
                    
                    <!-- 上下架切換 -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="drink_id" value="<?php echo $drink['id']; ?>">
                        <button type="submit" name="toggle_availability" 
                                class="btn btn-toggle <?php echo $drink['is_available'] ? '' : 'unavailable'; ?>">
                            <?php echo $drink['is_available'] ? '下架商品' : '上架商品'; ?>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
