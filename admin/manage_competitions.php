<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

// 获取所有比赛记录
$db = getDB();
$query = "
    SELECT 
        m.*,
        g.name as group_name,
        s1.name as student1_name,
        s2.name as student2_name,
        w.name as winner_name
    FROM matches m
    LEFT JOIN groups g ON m.group_id = g.id
    LEFT JOIN students s1 ON m.student1_id = s1.id
    LEFT JOIN students s2 ON m.student2_id = s2.id
    LEFT JOIN students w ON m.winner_id = w.id
    ORDER BY m.created_at DESC
";
$result = $db->query($query);

$matches = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // 添加调试输出
    error_log("Match ID: {$row['id']} - Student1 Score: {$row['student1_score']}, Student2 Score: {$row['student2_score']}");
    $matches[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>比赛管理</title>
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
                    <h1 class="text-xl font-bold text-gray-800">比赛管理</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">返回首页</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">退出登录</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 比赛记录表格 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">比赛记录</h2>
                <div>
                    <button onclick="deleteSelected()" 
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 disabled:opacity-50"
                            id="deleteSelectedBtn" 
                            disabled>
                        删除选中
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">时间</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">题库</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选手1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选手2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">得分</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">获胜者</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($matches as $match): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="match_ids[]" value="<?php echo $match['id']; ?>" 
                                       class="match-checkbox rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('Y-m-d H:i', strtotime($match['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($match['group_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($match['student1_name']); ?>
                                <span class="text-sm text-gray-500">
                                    (<?php echo number_format(floatval($match['student1_score']), 1); ?>分)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($match['student2_name']); ?>
                                <span class="text-sm text-gray-500">
                                    (<?php echo number_format(floatval($match['student2_score']), 1); ?>分)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClasses = [
                                    'waiting' => 'bg-gray-100 text-gray-800',
                                    'ready' => 'bg-yellow-100 text-yellow-800',
                                    'ongoing' => 'bg-blue-100 text-blue-800',
                                    'finished' => 'bg-green-100 text-green-800'
                                ];
                                $statusText = [
                                    'waiting' => '等待中',
                                    'ready' => '准备完成',
                                    'ongoing' => '进行中',
                                    'finished' => '已结束'
                                ];
                                $class = $statusClasses[$match['status']] ?? 'bg-gray-100 text-gray-800';
                                $text = $statusText[$match['status']] ?? '未知';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $class; ?>">
                                    <?php echo $text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format(floatval($match['student1_score']), 1); ?> : <?php echo number_format(floatval($match['student2_score']), 1); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($match['winner_name']): ?>
                                    <span class="text-green-600 font-medium">
                                        <?php echo htmlspecialchars($match['winner_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="viewDetails(<?php echo $match['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-2">
                                    详细记录
                                </button>
                                <button onclick="deleteMatch(<?php echo $match['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    删除
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 详细记录模态框 -->
    <div id="detailsModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">比赛详细记录</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="matchDetails" class="space-y-4">
                <!-- 详细信息将通过 JavaScript 填充 -->
            </div>
        </div>
    </div>

    <script>
    // 全选功能
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.getElementsByClassName('match-checkbox');
        Array.from(checkboxes).forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateDeleteButton();
    });

    // 更新删除按钮状态
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('match-checkbox')) {
            updateDeleteButton();
        }
    });

    function updateDeleteButton() {
        const checkboxes = document.getElementsByClassName('match-checkbox');
        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        deleteBtn.disabled = selectedCount === 0;
    }

    function deleteSelected() {
        const selectedIds = Array.from(document.getElementsByClassName('match-checkbox'))
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (!selectedIds.length) return;
        
        if (!confirm(`确定要删除选中的 ${selectedIds.length} 条比赛记录吗？此操作不可恢复。`)) {
            return;
        }

        const formData = new FormData();
        formData.append('match_ids', JSON.stringify(selectedIds));

        fetch('delete_matches.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || '删除成功');
                location.reload();
            } else {
                alert(data.message || '删除失败');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('删除失败：' + error.message);
        });
    }

    async function viewDetails(matchId) {
        try {
            const response = await fetch(`get_match_details.php?id=${matchId}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || '获取详情失败');
            }

            const match = data.match;
            const detailsHtml = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <h4 class="font-medium text-gray-700">基本信息</h4>
                        <p class="mt-1">题库：${match.group_name}</p>
                        <p>时间：${match.created_at}</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">选手1</h4>
                        <p class="mt-1">姓名：${match.student1_name}</p>
                        <p>得分：${(parseFloat(match.student1_score) || 0).toFixed(1)}</p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700">选手2</h4>
                        <p class="mt-1">姓名：${match.student2_name}</p>
                        <p>得分：${(parseFloat(match.student2_score) || 0).toFixed(1)}</p>
                    </div>
                    <div class="col-span-2">
                        <h4 class="font-medium text-gray-700">比赛记录</h4>
                        <pre class="mt-1 whitespace-pre-wrap bg-gray-50 p-3 rounded">${match.match_log || '无比赛记录'}</pre>
                    </div>
                </div>
            `;
            
            document.getElementById('matchDetails').innerHTML = detailsHtml;
            document.getElementById('detailsModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error:', error);
            alert('获取详情失败：' + error.message);
        }
    }

    function closeModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }
    </script>
</body>
</html> 