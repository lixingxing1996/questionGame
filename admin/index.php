<?php
require_once('../common/config.php');
require_once('../common/functions.php');

// 检查管理员权限
if (!is_admin()) {
    header('Location: login.php');
    exit();
}

// 获取统计数据
$stats = [
    'groups' => countGroups(),
    'questions' => countQuestions(),
    'students' => countStudents()
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>管理后台</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@heroicons/react@1.0.5/outline.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- 顶部导航栏 -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">问答系统管理后台</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4">欢迎，管理员</span>
                    <a href="logout.php" class="text-red-600 hover:text-red-700">退出登录</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 统计卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600">总分组数</h2>
                        <p class="text-2xl font-semibold text-gray-800"><?php echo $stats['groups']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600">总题目数</h2>
                        <p class="text-2xl font-semibold text-gray-800"><?php echo $stats['questions']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600">学生人数</h2>
                        <p class="text-2xl font-semibold text-gray-800"><?php echo $stats['students']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 功能网格 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="manage_groups.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex flex-col items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">分组管理</h3>
                    <p class="text-gray-600 text-sm text-center mt-2">管理问题分组和题目</p>
                </div>
            </a>

            <a href="manage_keys.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex flex-col items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">按键设置</h3>
                    <p class="text-gray-600 text-sm text-center mt-2">配置答题按键映射</p>
                </div>
            </a>

            <a href="manage_students.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex flex-col items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">学生管理</h3>
                    <p class="text-gray-600 text-sm text-center mt-2">管理学生信息和积分</p>
                </div>
            </a>

            <a href="manage_competitions.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex flex-col items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 mb-4">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">比赛管理</h3>
                    <p class="text-gray-600 text-sm text-center mt-2">查看比赛记录和结果</p>
                </div>
            </a>
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
</body>
</html> 