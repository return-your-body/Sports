<?php
$link=mysqli_connect("127.0.0.1","Health24","yy7xQupQz","Health24") or die ("資料庫連線錯誤");
mysqli_query($link,"set charset utf8");
session_start();
?>