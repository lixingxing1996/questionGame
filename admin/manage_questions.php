<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

// 获取所有分组
$groups = getGroups();

// 获取选定分组的问题
$selectedGroupId = $_GET['group_id'] ?? null;
$questions = $selectedGroupId ? getQuestionsByGroup($selectedGroupId) : [];

// 获取分组信息
$group = $selectedGroupId ? getGroupById($selectedGroupId) : null;
if (!$group) {
    header('Location: manage_groups.php');
    exit();
}

// 处理添加问题的表单提交

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    


    if (isset($_POST['action'])) {
        


        if ($_POST['action'] === 'delete') {
            $questionId = $_POST['question_id'] ?? 0;
            if ($questionId && deleteQuestion($questionId)) {
                header('Location: manage_questions.php?group_id=' . $selectedGroupId);
                exit();
            }
        } else if ($_POST['action'] === 'update') {
          
            $questionId = $_POST['question_id'] ?? 0;
            $question = $_POST['question'] ?? '';
            $optionA = $_POST['option_a'] ?? '';
            $optionB = $_POST['option_b'] ?? '';
            $optionC = $_POST['option_c'] ?? '';
            $correctOption = $_POST['correct_option'] ?? '';
            $points = $_POST['points'] ?? 1;

            if ($questionId && updateQuestion($questionId, $question, $optionA, $optionB, $optionC, $correctOption, $points)) {
                header('Location: manage_questions.php?group_id=' . $selectedGroupId);
                exit();
            }
        }
    } else {
        $question = $_POST['question'] ?? '';
        $optionA = $_POST['option_a'] ?? '';
        $optionB = $_POST['option_b'] ?? '';
        $optionC = $_POST['option_c'] ?? '';
        $optionD = $_POST['option_d'] ?? '';
        $correctOption = $_POST['correct_option'] ?? '';
        $points = $_POST['points'] ?? 1;
        
        if (!empty($question) && !empty($optionA) && !empty($optionB) && !empty($optionC) && !empty($correctOption)) {
            if (addQuestion($selectedGroupId, $question, $optionA, $optionB, $optionC, $correctOption, $points)) {
                header('Location: manage_questions.php?group_id=' . $selectedGroupId);
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>管理问题</title>
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
                    <h1 class="text-xl font-bold text-gray-800">问题管理</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">返回首页</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">退出登录</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                分组：<?php echo htmlspecialchars($group['name']); ?>
                <?php if ($group['alias']): ?>
                    <span class="text-gray-500 text-base">
                        (<?php echo htmlspecialchars($group['alias']); ?>)
                    </span>
                <?php endif; ?>
            </h2>
        </div>

        <!-- 添加问题表单 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">添加新问题</h3>
            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">问题内容</label>
                    <textarea name="question" required rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">选项A</label>
                        <input type="text" name="option_a" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">选项B</label>
                        <input type="text" name="option_b" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">选项C</label>
                        <input type="text" name="option_c" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">正确选项</label>
                        <select name="correct_option" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">分值</label>
                        <input type="number" name="points" value="1" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        添加问题
                    </button>
                </div>
            </form>
        </div>

        <!-- 问题列表 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">现有问题列表</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">问题内容</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选项</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">正确答案</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分值</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($questions as $question): ?>
                            <tr data-question-id="<?php echo $question['id']; ?>">
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-900">
                                    <div class="editable-content"><?php echo htmlspecialchars($question['question']); ?></div>
                                    <textarea class="hidden w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($question['question']); ?></textarea>
                                </td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-500">
                                    <div class="option-group">
                                        <div class="editable-content">A: <?php echo htmlspecialchars($question['option_a']); ?></div>
                                        <input type="text" class="hidden w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($question['option_a']); ?>">
                                    </div>
                                    <div class="option-group">
                                        <div class="editable-content">B: <?php echo htmlspecialchars($question['option_b']); ?></div>
                                        <input type="text" class="hidden w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($question['option_b']); ?>">
                                    </div>
                                    <div class="option-group">
                                        <div class="editable-content">C: <?php echo htmlspecialchars($question['option_c']); ?></div>
                                        <input type="text" class="hidden w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($question['option_c']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="editable-content"><?php echo htmlspecialchars($question['correct_option']); ?></div>
                                    <select class="hidden w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="A" <?php echo $question['correct_option'] === 'A' ? 'selected' : ''; ?>>A</option>
                                        <option value="B" <?php echo $question['correct_option'] === 'B' ? 'selected' : ''; ?>>B</option>
                                        <option value="C" <?php echo $question['correct_option'] === 'C' ? 'selected' : ''; ?>>C</option>
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="editable-content"><?php echo htmlspecialchars($question['points']); ?></div>
                                    <input type="number" class="hidden w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($question['points']); ?>" min="1">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button type="button" class="edit-btn text-blue-600 hover:text-blue-900 mr-2">编辑</button>
                                    <button type="button" class="save-btn hidden text-green-600 hover:text-green-900 mr-2">保存</button>
                                    <button type="button" class="cancel-btn hidden text-gray-600 hover:text-gray-900 mr-2">取消</button>
                                    <form method="post" onsubmit="return confirm('确定要删除这个问题吗？');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">删除</button>
                                    </form>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelectorAll('.editable-content').forEach(content => content.classList.add('hidden'));
            row.querySelectorAll('input, textarea, select').forEach(input => input.classList.remove('hidden'));
            this.classList.add('hidden');
            row.querySelector('.save-btn').classList.remove('hidden');
            row.querySelector('.cancel-btn').classList.remove('hidden');
        });
    });

    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelectorAll('.editable-content').forEach(content => content.classList.remove('hidden'));
            row.querySelectorAll('input, textarea, select').forEach(input => input.classList.add('hidden'));
            row.querySelector('.edit-btn').classList.remove('hidden');
            row.querySelector('.save-btn').classList.add('hidden');
            this.classList.add('hidden');
        });
    });

    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const questionId = row.dataset.questionId;
            const formData = new FormData();
            
            // 获取当前 URL 中的 group_id 参数
            const urlParams = new URLSearchParams(window.location.search);
            const groupId = urlParams.get('group_id');
            
            formData.append('action', 'update');
            formData.append('question_id', questionId);
            formData.append('group_id', groupId);  // 添加 group_id
            formData.append('question', row.querySelector('textarea').value);
            formData.append('option_a', row.querySelectorAll('input[type="text"]')[0].value);
            formData.append('option_b', row.querySelectorAll('input[type="text"]')[1].value);
            formData.append('option_c', row.querySelectorAll('input[type="text"]')[2].value);
            formData.append('correct_option', row.querySelector('select').value);
            formData.append('points', row.querySelector('input[type="number"]').value);

            // 显示加载状态
            const saveBtn = row.querySelector('.save-btn');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = '保存中...';
            saveBtn.disabled = true;

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) {
                    // 更新显示的内容而不刷新页面
                    row.querySelector('.editable-content:nth-child(1)').textContent = row.querySelector('textarea').value;
                    row.querySelectorAll('.option-group').forEach((group, index) => {
                        const letter = ['A', 'B', 'C'][index];
                        group.querySelector('.editable-content').textContent = 
                            letter + ': ' + row.querySelectorAll('input[type="text"]')[index].value;
                    });
                    row.querySelector('td:nth-child(3) .editable-content').textContent = 
                        row.querySelector('select').value;
                    row.querySelector('td:nth-child(4) .editable-content').textContent = 
                        row.querySelector('input[type="number"]').value;

                    // 切换回显示模式
                    row.querySelectorAll('.editable-content').forEach(content => 
                        content.classList.remove('hidden'));
                    row.querySelectorAll('input, textarea, select').forEach(input => 
                        input.classList.add('hidden'));
                    row.querySelector('.edit-btn').classList.remove('hidden');
                    row.querySelector('.save-btn').classList.add('hidden');
                    row.querySelector('.cancel-btn').classList.add('hidden');
                } else {
                    alert('保存失败，请重试');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('保存失败，请重试');
            }).finally(() => {
                // 恢复按钮状态
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        });
    });
});
</script>
</body>
</html>