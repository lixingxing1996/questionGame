<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

// 处理按键更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position = $_POST['position'] ?? '';
    $keyCode = $_POST['key_code'] ?? '';
    
    if (!empty($position) && !empty($keyCode)) {
        updateKeyMapping($position, $keyCode);
    }
}

// 获取当前按键映射
$keyMappings = getKeyMappings();
?>

<!DOCTYPE html>
<html>
<head>
    <title>按键设置</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- 顶部导航栏 -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">按键设置</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">返回首页</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">退出登录</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">按键配置说明</h2>
                <p class="text-gray-600">
                    点击输入框后按下键盘按键来设置对应的按钮映射。左侧ASDF对应红黄蓝绿按钮，右侧JKL;对应红黄蓝绿按钮。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- 左侧按钮 -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-4">左侧按钮</h3>
                    <div class="space-y-4">
                        <?php foreach (['red' => '红色', 'yellow' => '黄色', 'blue' => '蓝色', 'green' => '绿色'] as $color => $name): ?>
                        <div class="flex items-center">
                            <label class="block text-sm font-medium text-gray-700 w-20">
                                <?php echo $name; ?>：
                            </label>
                            <input 
                                type="text" 
                                data-position="left_<?php echo $color; ?>"
                                value="<?php echo htmlspecialchars($keyMappings["left_{$color}"] ?? ''); ?>"
                                class="ml-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                readonly
                            >
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 右侧按钮 -->
                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-4">右侧按钮</h3>
                    <div class="space-y-4">
                        <?php foreach (['red' => '红色', 'yellow' => '黄色', 'blue' => '蓝色', 'green' => '绿色'] as $color => $name): ?>
                        <div class="flex items-center">
                            <label class="block text-sm font-medium text-gray-700 w-20">
                                <?php echo $name; ?>：
                            </label>
                            <input 
                                type="text" 
                                data-position="right_<?php echo $color; ?>"
                                value="<?php echo htmlspecialchars($keyMappings["right_{$color}"] ?? ''); ?>"
                                class="ml-2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                readonly
                            >
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('input[data-position]').forEach(input => {
        input.addEventListener('focus', function() {
            this.value = '';
        });

        input.addEventListener('keydown', function(e) {
            e.preventDefault();
            const keyCode = e.key.toLowerCase();
            this.value = keyCode;
            
            // 发送更新请求
            fetch('update_key_mapping.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `position=${this.dataset.position}&key_code=${keyCode}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('更新按键映射失败');
                    this.value = '';
                }
            });
        });
    });
    </script>
</body>
</html> 