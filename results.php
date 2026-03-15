<?php
declare(strict_types=1);
session_start();

require_once 'includes/config.php';
require_once 'includes/database.php';

require_once 'test_engine.php';

// Проверяем наличие результатов в сессии
if (!isset($_SESSION['last_result'])) {
    header('Location: index.php');
    exit;
}

$results = $_SESSION['last_result'];
$testFile = $_SESSION['last_test_file'] ?? 'results.json';

// Обработка скачивания
if (isset($_GET['download']) && $_GET['download'] === '1') {
    downloadResults($results, $testFile);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты теста</title>
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
        
        .result-summary {
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
            margin-bottom: 30px;
        }
        
        .result-summary h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .score {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .percentage {
            font-size: 24px;
            opacity: 0.9;
        }
        
        .details {
            margin-top: 30px;
        }
        
        .detail-item {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.3s;
        }
        
        .detail-item:hover {
            transform: translateX(5px);
        }
        
        .correct {
            background: #d4edda;
            border-left: 5px solid #28a745;
        }
        
        .wrong {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
        }
        
        .question-text {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .answer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
        }
        
        .score-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .correct .score-badge {
            background: #28a745;
            color: white;
        }
        
        .wrong .score-badge {
            background: #dc3545;
            color: white;
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .actions {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📊 Результаты теста</h1>
            <div class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
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
            <div class="result-summary">
                <h2><?= htmlspecialchars($results['category']) ?></h2>
                <div class="score"><?= $results['total_score'] ?> / <?= $results['max_score'] ?></div>
                <div class="percentage"><?= $results['percentage'] ?>% правильных ответов</div>
            </div>
            
            <h3>Детализация по вопросам:</h3>
            
            <div class="details">
                <?php foreach ($results['details'] as $detail): ?>
                    <div class="detail-item <?= $detail['is_correct'] ? 'correct' : 'wrong' ?>">
                        <div class="question-text"><?= htmlspecialchars($detail['question_text']) ?></div>
                        <div class="answer-info">
                            <span>Баллы: <?= $detail['earned_score'] ?> / <?= $detail['max_score'] ?></span>
                            <span class="score-badge">
                                <?= $detail['is_correct'] ? '✓ Верно' : '✗ Неверно' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="actions">
                <a href="index.php" class="btn">К списку тестов</a>
                <a href="?download=1" class="btn btn-secondary">📥 Скачать результаты</a>
            </div>
        </div>
    </div>
</body>
</html>