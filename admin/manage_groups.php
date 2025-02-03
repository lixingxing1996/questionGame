<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

// 处理添加分组的表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $alias = $_POST['alias'] ?? '';
    
    if (!empty($name)) {
        if (addGroup($name, $alias)) {
            header('Location: manage_groups.php');
            exit();
        }
    }
}

// 获取所有分组
$groups = getGroups();
?>

<!DOCTYPE html>
<html>
<head>
    <title>管理分组</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .edit-mode input {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        .edit-buttons {
            display: none;
        }
        .edit-mode .edit-buttons {
            display: inline-flex;
        }
        .edit-mode .edit-trigger {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- 顶部导航栏 -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">分组管理</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">返回首页</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">退出登录</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 添加分组表单 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">添加新分组</h2>
            <form method="post" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            分组名称
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="请输入分组名称"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            分组别名
                        </label>
                        <input 
                            type="text" 
                            name="alias"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="可选"
                        >
                    </div>
                </div>
                <div class="flex justify-end">
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        添加分组
                    </button>
                </div>
            </form>
        </div>

        <!-- 分组列表 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">现有分组</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名称</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">别名</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($groups as $group): ?>
                        <tr class="hover:bg-gray-50" data-group-id="<?php echo $group['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($group['id']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="group-content">
                                    <span class="display-text"><?php echo htmlspecialchars($group['name']); ?></span>
                                    <input type="text" class="edit-input hidden" value="<?php echo htmlspecialchars($group['name']); ?>">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="group-content">
                                    <span class="display-text"><?php echo htmlspecialchars($group['alias']); ?></span>
                                    <input type="text" class="edit-input hidden" value="<?php echo htmlspecialchars($group['alias']); ?>">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="manage_questions.php?group_id=<?php echo $group['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 mr-2">
                                    管理问题
                                </a>
                                <button class="text-gray-600 hover:text-gray-900 edit-trigger">
                                    编辑
                                </button>
                                <span class="edit-buttons">
                                    <button class="text-green-600 hover:text-green-900 mr-2 save-edit">
                                        保存
                                    </button>
                                    <button class="text-red-600 hover:text-red-900 cancel-edit">
                                        取消
                                    </button>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-white border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> 问答系统管理后台
            </p>
        </div>
    </footer>

    <script>
    document.querySelectorAll('.edit-trigger').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.classList.add('edit-mode');
            row.querySelectorAll('.display-text').forEach(span => span.classList.add('hidden'));
            row.querySelectorAll('.edit-input').forEach(input => input.classList.remove('hidden'));
        });
    });

    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.classList.remove('edit-mode');
            row.querySelectorAll('.display-text').forEach(span => span.classList.remove('hidden'));
            row.querySelectorAll('.edit-input').forEach(input => {
                input.classList.add('hidden');
                input.value = input.closest('.group-content').querySelector('.display-text').textContent;
            });
        });
    });

    document.querySelectorAll('.save-edit').forEach(button => {
        button.addEventListener('click', async function() {
            const row = this.closest('tr');
            const groupId = row.dataset.groupId;
            const inputs = row.querySelectorAll('.edit-input');
            const name = inputs[0].value.trim();
            const alias = inputs[1].value.trim();

            if (!name) {
                alert('分组名称不能为空');
                return;
            }

            try {
                const response = await fetch('update_group.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${groupId}&name=${encodeURIComponent(name)}&alias=${encodeURIComponent(alias)}`
                });

                const data = await response.json();
                if (data.success) {
                    row.querySelectorAll('.display-text')[0].textContent = name;
                    row.querySelectorAll('.display-text')[1].textContent = alias;
                    row.classList.remove('edit-mode');
                    row.querySelectorAll('.display-text').forEach(span => span.classList.remove('hidden'));
                    row.querySelectorAll('.edit-input').forEach(input => input.classList.add('hidden'));
                } else {
                    alert('更新失败：' + (data.message || '未知错误'));
                }
            } catch (error) {
                alert('更新失败：' + error.message);
            }
        });
    });
    </script>
</body>
</html> 