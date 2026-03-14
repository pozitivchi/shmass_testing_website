<?php
declare(strict_types=1);

require_once 'includes/config.php'; 
require_once 'test_engine.php';
require_once 'includes/database.php'; 


$testsDir =  __DIR__. "\\data\\";
$currentTest = null;
$testData = null;

// Получаем запрошенный тест
$requestedTest = $_GET['test'] ?? null;

if ($requestedTest) {
    $testData = loadTest($testsDir, $requestedTest);
    if (!$testData) {
        // Если тест не найден, перенаправляем на список
        header('Location: index.php');
        exit;
    }
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_file']) && isset($_POST['answers'])) {
    $testFile = $_POST['test_file'];
    $userAnswers = $_POST['answers'] ?? [];
    
    $testData = loadTest($testsDir, $testFile);
    
    if ($testData) {
        $results = processTestResults($testData, $userAnswers);
        
        // Сохраняем результаты в сессию
        $_SESSION['last_result'] = $results;
        $_SESSION['last_test_file'] = $testFile;
        
        // Редирект на страницу результатов
        header('Location: results.php');
        exit;
    }
}

$testsList = getTestsList($testsDir);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система тестирования</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        
        .nav-links a {
            margin-left: 15px;
            text-decoration: none;
            color: #667eea;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #764ba2;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .test-list {
            list-style: none;
        }
        
        .test-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .test-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .test-item a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
        }
        
        .test-category {
            color: #667eea;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .question {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .question h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .options {
            list-style: none;
        }
        
        .option {
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .option:hover {
            background: #f0f2f5;
            border-color: #667eea;
        }
        
        .option input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-back {
            background: #6c757d;
            display: inline-block;
            text-decoration: none;
            margin-top: 20px;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .info-text {
            text-align: center;
            color: #666;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📚 Система тестирования</h1>
            <div class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
                     <a href="results.php">Результаты последнего теста</a>
                    <?php if ($_SESSION['user']['role'] !== 'user'): ?>
                        <a href="admin/dashboard.php">Админ-панель</a>
                    <?php endif; ?>
                    <a href="logout.php">Выход</a>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="card">
            <?php if ($testData): ?>
                <!-- Форма теста -->
                <h2><?= htmlspecialchars($testData['category'] ?? 'Тест') ?></h2>
                <p class="description"><?= htmlspecialchars($testData['description'] ?? '') ?></p>
                
                <form method="POST" action="">
                    <input type="hidden" name="test_file" value="<?= htmlspecialchars($requestedTest) ?>">
                    
                    <?php foreach ($testData['questions'] as $idx => $question): ?>
                        <div class="question">
                            <h3><?= ($idx + 1) ?>. <?= htmlspecialchars($question['question']) ?></h3>
                            <ul class="options">
                                <?php foreach ($question['options'] as $optIdx => $option): ?>
                                    <li class="option">
                                        <label>
                                            <input type="checkbox" 
                                                   name="answers[<?= $idx ?>][]" 
                                                   value="<?= $optIdx ?>">
                                            <?= htmlspecialchars($option) ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn">Завершить тест</button>
                    <a href="index.php" class="btn btn-back">Вернуться к списку</a>
                </form>
                
            <?php else: ?>
                <!-- Список тестов -->
                <h2>Доступные тесты</h2>
                <?php if (empty($testsList)): ?>
                    <p class="info-text">Нет доступных тестов</p>
                <?php else: ?>
                    <ul class="test-list">
                        <?php foreach ($testsList as $file => $category): ?>
                            <li class="test-item">
                                <a href="?test=<?= urlencode($file) ?>">
                                    <strong><?= htmlspecialchars($category) ?></strong>
                                    <div class="test-category">Файл: <?= htmlspecialchars($file) ?></div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>