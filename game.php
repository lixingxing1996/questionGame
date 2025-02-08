<?php
require_once 'common/config.php';
require_once 'common/functions.php';

$groupId = $_GET['group_id'] ?? 0;
$student1Id = $_GET['student1'] ?? 0;
$student2Id = $_GET['student2'] ?? 0;

// 验证参数
if (!$groupId) {
    error_log("Missing or invalid group_id: $groupId");
    header('Location: index.php?error=invalid_group');
    exit();
}
if (!$student1Id || !$student2Id) {
    header('Location: index.php');
    exit();
}

// 获取学生信息
$student1 = getStudentById($student1Id);
$student2 = getStudentById($student2Id);

// 获取随机题目
$questions = getRandomQuestions($groupId, 10);
error_log("Retrieved questions for group $groupId: " . print_r($questions, true));

// 确保 questions 不为空
if (empty($questions)) {
    error_log('No questions found for group_id: ' . $groupId);
    header('Location: index.php?error=no_questions');
    exit();
}

// 创建新比赛
$matchId = createMatch($groupId, $student1Id, $student2Id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>比赛进行中</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- 添加音效 -->
    <audio id="correctSound" src="sounds/correct.mp3" preload="auto"></audio>
    <audio id="wrongSound" src="sounds/wrong.mp3" preload="auto"></audio>
    <audio id="tickSound" src="sounds/tick.mp3" preload="auto"></audio>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- 左边选手 -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($student1['name']); ?></h2>
                    <img src="uploads/avatars/<?php echo $student1['avatar']; ?>" 
                         alt="头像" 
                         class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                    <div class="text-3xl font-bold text-blue-600" id="score1">0</div>
                    <div id="emotion1" class="text-6xl mt-4">🤔</div>
                    <button class="mt-4 bg-red-600 text-white px-6 py-2 rounded-lg" id="ready1" disabled>
                        按a键准备
                    </button>
                </div>
            </div>

            <!-- 右边选手 -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($student2['name']); ?></h2>
                    <img src="uploads/avatars/<?php echo $student2['avatar']; ?>" 
                         alt="头像" 
                         class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                    <div class="text-3xl font-bold text-blue-600" id="score2">0</div>
                    <div id="emotion2" class="text-6xl mt-4">🤔</div>
                    <button class="mt-4 bg-red-600 text-white px-6 py-2 rounded-lg" id="ready2" disabled>
                        按j键准备
                    </button>
                </div>
            </div>
        </div>

        <!-- 题目区域 -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6" id="questionArea" style="display: none;">
            <div class="text-center mb-4">
                <div id="countdown" class="text-4xl font-bold text-blue-600"></div>
            </div>
            <div id="questionContent" style="display: none;">
                <div class="flex justify-between items-center mb-4">
                    <div id="questionTimer" class="text-2xl font-bold text-blue-600">
                        <span id="timerSeconds">10</span>秒
                    </div>
                    <div class="h-2 bg-gray-200 rounded-full flex-grow mx-4">
                        <div id="timerBar" class="h-full bg-blue-600 rounded-full transition-all duration-100" style="width: 100%"></div>
                    </div>
                </div>
                <div id="question" class="text-6xl font-medium mb-6 p-4"></div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="options">
                    <!-- 选项会通过 JavaScript 动态添加，并显示对应的按键 -->
                </div>
            </div>
        </div>

        <!-- 结果区域 -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6 text-center" id="resultArea" style="display: none;">
            <h2 class="text-3xl font-bold mb-4">比赛结束</h2>
            <div id="winner" class="text-2xl text-green-600 mb-4"></div>
            <button onclick="location.href='index.php'" class="bg-blue-600 text-white px-6 py-2 rounded-lg">
                返回首页
            </button>
        </div>
    </div>

    <script>
    const matchId = <?php echo $matchId; ?>;
    const questions = <?php echo json_encode($questions); ?>;
    console.log('Initial questions:', questions); // 调试用
    let currentQuestion = 0;
    let readyCount = 0;
    let scores = {1: 0, 2: 0};
    let player1Answered = false;
    let player2Answered = false;
    let timerInterval = null;
    let questionStartTime = null;
    const QUESTION_TIME_LIMIT = 10000; // 10秒
    
    // 修改开始游戏函数，添加调试日志
    function startGame() {
        console.log('Game started');
        console.log('Questions at start:', questions); // 调试用
        document.getElementById('questionArea').style.display = 'block';
        document.getElementById('questionContent').style.display = 'none';
        
        // 开始倒计时
        let countdown = 3;
        document.getElementById('countdown').textContent = countdown;
        document.getElementById('countdown').style.display = 'block';
        
        const timer = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            
            if (countdown === 0) {
                clearInterval(timer);
                document.getElementById('countdown').style.display = 'none';
                document.getElementById('questionContent').style.display = 'block';
                currentQuestion = 0;  // 重置题目计数器
                // 更新比赛状态为进行中
                fetch('common/update_match_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `match_id=${matchId}&status=ongoing`
                }).then(response => response.json())
                .then(data => {
                    console.log('Match status update response:', data);
                    if (data.success) {
                        showQuestion(); // 状态更新后再显示题目
                    } else {
                        console.error('Failed to update match status:', data.message);
                    }
                });
            }
        }, 1000);
    }

    // 修改 DOMContentLoaded 事件处理器
    document.addEventListener('DOMContentLoaded', () => {
        currentQuestion = 0;
        player1Answered = false;
        player2Answered = false;
        timerInterval = null;
        questionStartTime = 0;
        scores = {1: 0, 2: 0};
        
        // 初始化准备按钮状态
        document.getElementById('ready1').disabled = false;
        document.getElementById('ready2').disabled = false;
        
        // 隐藏问题区域和结果区域
        document.getElementById('questionArea').style.display = 'none';
        document.getElementById('resultArea').style.display = 'none';
    });

    // 修改 ready 按钮的事件监听器
    document.addEventListener('keydown', function(event) {
        const key = event.key.toLowerCase();
        console.log('Key pressed:', key);
        
        // 准备阶段的按键处理
        if (readyCount < 2) {
            if (key === 'a') {
                console.log('Player 1 ready button clicked');
                if (!document.getElementById('ready1').disabled) {  // 修改这里
                    document.getElementById('ready1').textContent = '已准备';
                    document.getElementById('ready1').disabled = true;
                    readyCount++;
                    console.log('Ready count:', readyCount);
                }
            } else if (key === 'j') {
                console.log('Player 2 ready button clicked');
                if (!document.getElementById('ready2').disabled) {  // 修改这里
                    document.getElementById('ready2').textContent = '已准备';
                    document.getElementById('ready2').disabled = true;
                    readyCount++;
                    console.log('Ready count:', readyCount);
                }
            }
            
            if (readyCount === 2) {
                console.log('Both players ready, starting game...');
                startGame();
            }
        }
    });

    // 显示题目
    function showQuestion() {
        console.log('ShowQuestion called:', { currentQuestion, totalQuestions: questions.length });
        
        // 检查是否还有题目
        if (currentQuestion >= questions.length) {
            console.log('No more questions, ending game');
            endGame();
            return;
        }

        // 开始计时
        questionStartTime = Date.now();
        
        // 重置状态
        player1Answered = false;
        player2Answered = false;
        
        // 重置表情
        document.getElementById('emotion1').textContent = '🤔';
        document.getElementById('emotion2').textContent = '🤔';
        
        const question = questions[currentQuestion];
        console.log('Displaying question:', question);

        // 显示问题
        const questionElement = document.getElementById('question');
        questionElement.style.opacity = '0';
        questionElement.textContent = `第 ${currentQuestion + 1} 题：${question.question}`;
        
        // 添加淡入效果
        setTimeout(() => {
            questionElement.style.opacity = '1';
        }, 50);

        // 更新选项显示
        const optionsContainer = document.getElementById('options');
        optionsContainer.innerHTML = `
            <div class="bg-yellow-200 p-4 rounded-lg">
                <div class="text-lg">${question.option_a}</div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>玩家1: S</div>
                    <div>玩家2: K</div>
                </div>
            </div>
            <div class="bg-blue-200 p-4 rounded-lg">
                <div class="text-lg">${question.option_b}</div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>玩家1: D</div>
                    <div>玩家2: L</div>
                </div>
            </div>
            <div class="bg-green-200 p-4 rounded-lg">
                <div class="text-lg">${question.option_c}</div>
                <div class="flex justify-between text-sm text-gray-600 mt-2">
                    <div>玩家1: F</div>
                    <div>玩家2: ;</div>
                </div>
            </div>
        `;

        // 开始新的计时
        startTimer();
    }

    // 添加倒计时函数
    function startTimer() {
        let timeLeft = QUESTION_TIME_LIMIT / 1000; // 转换为秒
        const timerElement = document.getElementById('timerSeconds');
        const timerBar = document.getElementById('timerBar');
        
        questionStartTime = Date.now();
        
        // 清除可能存在的旧计时器
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        // 设置初始状态
        timerElement.textContent = timeLeft;
        timerBar.style.width = '100%';
        
        timerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft >= 0) {
                timerElement.textContent = timeLeft;
                // 更新进度条
                const percentage = (timeLeft / (QUESTION_TIME_LIMIT / 1000)) * 100;
                timerBar.style.width = `${percentage}%`;
            }
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                // 时间到，自动进入下一题
                goToNextQuestion();
            }
        }, 1000);
    }

    // 添加键盘事件监听
    document.addEventListener('keydown', function(event) {
        const key = event.key.toLowerCase();
        
        // 准备阶段的按键处理（红色按键）
        if (readyCount < 2) {
            if (key === 'a' && !document.getElementById('ready1').disabled) {  // 左边玩家用A键准备
                ready(1);
            } else if (key === 'j' && !document.getElementById('ready2').disabled) {  // 右边玩家用J键准备
                ready(2);
            }
            return;
        }

        if (document.getElementById('questionArea').style.display !== 'none') {
            let choice = null;
            let player = null;

            // 玩家1的按键映射（如果还没答过题）
            if (!player1Answered) {
                switch (key) {
                    case 's': // 黄色按键对应A选项
                        choice = 'A';
                        player = 1;
                        break;
                    case 'd': // 蓝色按键对应B选项
                        choice = 'B';
                        player = 1;
                        break;
                    case 'f': // 绿色按键对应C选项
                        choice = 'C';
                        player = 1;
                        break;
                }
            }

            // 玩家2的按键映射（如果还没答过题）
            if (!player2Answered) {
                switch (key) {
                    case 'k': // 黄色按键对应A选项
                        choice = 'A';
                        player = 2;
                        break;
                    case 'l': // 蓝色按键对应B选项
                        choice = 'B';
                        player = 2;
                        break;
                    case ';': // 绿色按键对应C选项
                        choice = 'C';
                        player = 2;
                        break;
                }
            }

            if (choice && player) {
                handleAnswer(player, choice);
            }
        }
    });

    // 处理答题
    function handleAnswer(player, choice) {
        if (player1Answered && player2Answered) return;
        if ((player === 1 && player1Answered) || (player === 2 && player2Answered)) return;
        
        const question = questions[currentQuestion];
        const isCorrect = choice === question.correct_option;
        const responseTime = Date.now() - questionStartTime;
        
        // 播放音效
        const sound = document.getElementById(isCorrect ? 'correctSound' : 'wrongSound');
        sound.currentTime = 0;
        sound.play();
        
        // 更新表情
        const emotionElement = document.getElementById(`emotion${player}`);
        emotionElement.style.transform = 'scale(1.2)';
        emotionElement.textContent = isCorrect ? '😄' : '😢';

        // 计算得分
        const timeBonus = Math.max(0, Math.floor((QUESTION_TIME_LIMIT - responseTime) / 1000));
        const baseScore = isCorrect ? parseInt(question.points) : 0;
        const totalScore = isCorrect ? (baseScore + timeBonus) : 0;
        
        // 记录答题结果
        fetch('common/record_answer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `match_id=${matchId}&question_id=${question.id}&student_id=${player === 1 ? <?php echo $student1Id; ?> : <?php echo $student2Id; ?>}` +
                  `&answer=${choice}&is_correct=${isCorrect ? 1 : 0}&response_time=${responseTime}` +
                  `&base_score=${baseScore}&time_bonus=${timeBonus}&total_score=${totalScore}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to record answer:', data.message);
            }
        })
        .catch(error => {
            console.error('Error recording answer:', error);
        });

        if (isCorrect) {
            // 答对题目处理
            player1Answered = true;
            player2Answered = true;
            
            // 计算并更新分数
            scores[player] += totalScore;
            document.getElementById(`score${player}`).textContent = scores[player];
            
            // 显示答对提示
            const playerName = player === 1 ? 
                '<?php echo htmlspecialchars($student1['name']); ?>' : 
                '<?php echo htmlspecialchars($student2['name']); ?>';
            
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
            notification.innerHTML = `
                <div class="font-bold">${playerName} 抢答成功！</div>
                <div class="text-sm">用时：${(responseTime / 1000).toFixed(2)}秒</div>
                <div class="text-sm">得分：${totalScore}分 (+${timeBonus}时间奖励)</div>
            `;
            document.body.appendChild(notification);

            // 延迟后进入下一题
            window.setTimeout(() => {
                notification.remove();
                goToNextQuestion();
            }, 1500);
        } else {
            // 答错直接进入下一题，不等待另一个玩家
            player1Answered = true;
            player2Answered = true;
            
            // 显示答错提示
            const playerName = player === 1 ? 
                '<?php echo htmlspecialchars($student1['name']); ?>' : 
                '<?php echo htmlspecialchars($student2['name']); ?>';
            
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
            notification.innerHTML = `
                <div class="font-bold">${playerName} 答错了！</div>
                <div class="text-sm">正确答案：${question.correct_option}</div>
            `;
            document.body.appendChild(notification);
            
            window.setTimeout(() => {
                notification.remove();
                goToNextQuestion();
            }, 1500);
        }
    }

    // 添加切换到下一题的函数
    function goToNextQuestion() {
        // 清除计时器
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        // 重置状态
        currentQuestion++;
        player1Answered = false;
        player2Answered = false;
        
        // 显示下一题
        showQuestion();
    }

    // 结束游戏
    function endGame() {
        document.getElementById('questionArea').style.display = 'none';
        document.getElementById('resultArea').style.display = 'block';
        
        const winner = scores[1] > scores[2] ? 1 : 2;
        const winnerId = winner === 1 ? <?php echo $student1Id; ?> : <?php echo $student2Id; ?>;
        
        document.getElementById('winner').textContent = 
            `获胜者：${winner === 1 ? '<?php echo htmlspecialchars($student1['name']); ?>' : '<?php echo htmlspecialchars($student2['name']); ?>'}`;
        
        // 更新比赛状态为已完成
        fetch('common/update_match_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `match_id=${matchId}&status=finished`
        });
        
        // 更新获胜者
        fetch('common/update_match_result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `match_id=${matchId}&winner_id=${winnerId}`
        });
    }
    </script>

    <style>
    /* 添加更多动画效果 */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }

    #question {
        transition: opacity 0.3s, transform 0.3s;
        transform: translateY(-20px);
    }

    .fixed {
        animation: slideIn 0.3s ease-out;
    }

    .fade-out {
        animation: fadeOut 0.3s ease-out;
    }

    #emotion1, #emotion2 {
        transition: transform 0.2s ease-out;
    }

    #timerBar {
        transition: width 0.1s linear;
    }
    </style>
</body>
</html>