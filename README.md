# 🧋 MACU 飲料店 POS 點餐系統

一個基於 PHP 和 MySQL 開發的現代化飲料店點餐系統，具有美觀的 UI 介面和完整的訂單管理功能。

![MACU Logo](images/macuLOGO.jpg)

## ✨ 功能特色

### 🛍️ 顧客端功能
- **直觀的飲料瀏覽**：按分類展示所有飲料，附有高品質商品圖片
- **客製化點餐**：支援冰塊、甜度、加料等選項自訂
- **智能購物車**：即時計算價格，支援數量調整和商品移除
- **客戶資訊驗證**：訂購人姓名與台灣手機號碼格式驗證，確保資料正確
- **多種付款方式**：支援現金與 LINE Pay
- **訂單追蹤**：即時顯示訂單狀態與詳細資訊

### 🎨 使用者體驗
- **響應式設計**：支援桌面及行動裝置
- **現代化 UI**：漸層背景、卡片式設計、平滑動畫
- **直覺操作**：簡潔明了的流程，提升用戶體驗

## 🏗️ 系統架構
```bash
macu-pos/
├── index.php              # 系統入口，重定向到主頁
├── main_index.php         # 主頁：飲料展示與選購
├── cart_page.php          # 購物車頁面：檢視、編輯訂單
├── add_to_cart.php        # 加入購物車處理
├── update_cart.php        # 購物車更新處理
├── process_order.php      # 訂單處理與驗證
├── order_success.php      # 訂單成功頁面與詳細資訊
├── config.php             # 主配置檔，引用資料庫設定
├── config_database.php    # 資料庫連線設定與公用函數
├── macu_分類清單.csv      # 飲料分類與資料導入
├── images/                # 飲料與系統圖片資源
└── README.md              # 專案說明文件
```

## 🛠️ 技術棧
- **後端**：PHP 8.0+
- **資料庫**：MySQL 8.0+
- **前端**：HTML5, CSS3, JavaScript (ES6+)
- **伺服器**：Apache (XAMPP)

## 🚀 安裝與部署
1. 下載或複製專案到本機目錄：
   ```powershell
   git clone https://github.com/linchen1107/macu-pos.git
   cd macu-pos
   ```
2. 安裝並啟動 XAMPP (Apache & MySQL)
3. 將專案資料夾放入 `C:\xampp\htdocs`：
   ```powershell
   Copy-Item -Path "{your_path}\macu-pos" -Destination "C:\xampp\htdocs\" -Recurse
   ```
4. 建立資料庫與表：
   - 使用 phpMyAdmin 或 MySQL CLI 匯入 `macu_分類清單.csv`（或其他初始化 SQL）
   - 確保 `orders` 與 `order_items` 表結構包含 `customer_name` 欄位
5. 在瀏覽器開啟：
   ```text
   http://localhost/macu-pos
   ```

## ⚙️ 配置說明
- `config_database.php`：設定 `DB_HOST`、`DB_USER`、`DB_PASS`、`DB_NAME`
- `config.php`：引入資料庫設定
- 確保 `images/` 資源目錄與檔案名稱正確

## 📋 使用說明
1. 選擇飲料與客製化選項
2. 點擊「加入購物車」
3. 前往「購物車」頁面，輸入「訂購人姓名」與「聯絡電話」
4. 選擇付款方式並送出訂單
5. 訂單成功後查看訂單編號與詳情

## 🤝 貢獻指南
歡迎提交 issues 或 pull requests，共同完善功能。

## 📄 授權許可
本專案採用 MIT License，詳見 `LICENSE` 檔案。
