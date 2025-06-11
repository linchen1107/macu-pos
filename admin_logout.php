<?php
session_start();

// 清除所有 session 資料
session_unset();
session_destroy();

// 重定向到登入頁面
header('Location: admin_login.php');
exit;
?>
