<?php
require_once('../common/config.php');

// 清除session
session_destroy();

// 重定向到登录页面
header('Location: login.php');
exit(); 