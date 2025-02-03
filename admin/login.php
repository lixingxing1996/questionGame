<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 如果已经登录，直接跳转到管理首页
if (is_admin()) {
    header('Location: index.php');
    exit();
}

$error = '';

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username AND role = "admin" LIMIT 1');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // 登录成功，设置session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = '用户名或密码错误';
        }
    } else {
        $error = '请输入用户名和密码';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>管理员登录</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">管理员登录</h2>
                <p class="text-gray-600 mt-2">请输入您的账号和密码</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded bg-red-50 text-red-600 text-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        用户名
                    </label>
                    <input 
                        type="text" 
                        name="username" 
                        required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                        placeholder="请输入用户名"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        密码
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                        placeholder="请输入密码"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    登录
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>默认账号：admin</p>
                <p>默认密码：admin123</p>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <div class="fixed bottom-4 text-center w-full text-gray-500 text-sm">
        &copy; <?php echo date('Y'); ?> 问答系统管理后台
    </div>
</body>
</html> 