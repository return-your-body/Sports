<?php
session_start(); // 啟動 session

// 清除所有 session 變數
$_SESSION = [];

// 銷毀 session
session_destroy();

// 使用 JavaScript alert 並返回登入頁面
echo "<script>
        alert('您已成功登出');
        window.location.href = 'index.html';
      </script>";
exit;
?>
