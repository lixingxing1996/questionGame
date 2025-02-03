<?php
require_once 'common/config.php';
require_once 'common/functions.php';

$groups = getGroups();
$students = getStudents();
?>

<!DOCTYPE html>
<html>
<head>
    <title>单词游戏记忆法</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.20/fullpage.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.20/fullpage.min.js"></script>
</head>
<body>
    <div id="fullpage">
        <!-- 第一屏：欢迎界面 -->
        <div class="section bg-gradient-to-r from-blue-500 to-purple-600">
            <div class="max-w-4xl mx-auto px-4 h-screen flex items-center justify-center">
                <div class="text-center text-white">
                    <h1 class="text-5xl font-bold mb-8">欢迎使用新单词游戏记忆法</h1>
                    <p class="text-xl mb-8">由春泽研发的创新学习方式</p>
                    <button onclick="confirmWelcome()" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors mb-8">
                        开始体验
                    </button>
                    <div class="animate-bounce">
                        <svg class="w-8 h-8 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- 第二屏：游戏模式选择 -->
        <div class="section bg-gray-100">
            <div class="max-w-4xl mx-auto px-4 h-screen flex items-center justify-center">
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-8">选择游戏模式</h2>
                    <div class="bg-white rounded-lg shadow-lg p-8 hover:shadow-xl transition-shadow">
                        <h3 class="text-2xl font-semibold mb-4">双人竞赛模式</h3>
                        <p class="text-gray-600 mb-6">两名同学进行抢答比赛，考验反应速度和知识掌握程度</p>
                        <button onclick="selectMode()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            开始游戏
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 第三屏：题库选择 -->
        <div class="section bg-gray-50">
            <div class="max-w-4xl mx-auto px-4 h-screen flex items-center justify-center">
                <div class="w-full">
                    <h2 class="text-3xl font-bold mb-8 text-center">选择题库</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($groups as $group): ?>
                        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer group-select" 
                             data-group-id="<?php echo $group['id']; ?>">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($group['name']); ?></h3>
                            <?php if ($group['alias']): ?>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($group['alias']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 第四屏：选择参赛学生 -->
        <div class="section bg-gray-100">
            <div class="max-w-4xl mx-auto px-4 h-screen flex items-center justify-center">
                <div class="w-full">
                    <h2 class="text-3xl font-bold mb-8 text-center">选择参赛学生</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- 左边选手 -->
                        <div>
                            <h3 class="text-xl font-semibold mb-4">选手1</h3>
                            <select id="student1" class="w-full p-3 border border-gray-300 rounded-lg">
                                <option value="">请选择...</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- 右边选手 -->
                        <div>
                            <h3 class="text-xl font-semibold mb-4">选手2</h3>
                            <select id="student2" class="w-full p-3 border border-gray-300 rounded-lg">
                                <option value="">请选择...</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-8">
                        <button onclick="startMatch()" 
                                class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors">
                            开始比赛
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let selectedGroupId = null;
    let welcomeConfirmed = false;
    let modeSelected = false;  // 保留模式选择标志
    
    new fullpage('#fullpage', {
        autoScrolling: true,
        navigation: true,
        navigationPosition: 'right',
        onLeave: function(origin, destination, direction) {
            // 从第一屏到第二屏
            if (origin.index === 0 && destination.index === 1) {
                return welcomeConfirmed;
            }
            // 从第二屏到第三屏
            if (origin.index === 1 && destination.index === 2) {
                return modeSelected;
            }
            // 从第三屏到第四屏
            if (origin.index === 2 && destination.index === 3) {
                return selectedGroupId !== null;
            }
            return true;
        }
    });

    function selectMode() {
        modeSelected = true;
        fullpage_api.moveSectionDown();
    }

    function confirmWelcome() {
        welcomeConfirmed = true;
        fullpage_api.moveSectionDown();
    }

    // 题库选择
    document.querySelectorAll('.group-select').forEach(group => {
        group.addEventListener('click', function() {
            document.querySelectorAll('.group-select').forEach(g => 
                g.classList.remove('ring-2', 'ring-blue-500'));
            this.classList.add('ring-2', 'ring-blue-500');
            selectedGroupId = this.dataset.groupId;
            setTimeout(() => fullpage_api.moveSectionDown(), 500);
        });
    });

    // 开始比赛
    function startMatch() {
        const student1 = document.getElementById('student1').value;
        const student2 = document.getElementById('student2').value;

        if (!selectedGroupId || !student1 || !student2) {
            alert('请选择题库和两名参赛学生');
            return;
        }

        if (student1 === student2) {
            alert('请选择不同的参赛学生');
            return;
        }

        // 跳转到比赛页面
        window.location.href = `game.php?group_id=${selectedGroupId}&student1=${student1}&student2=${student2}`;
    }
    </script>
</body>
</html> 