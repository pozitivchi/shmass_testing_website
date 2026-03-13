<?php
declare(strict_types=1);
session_start();

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once 'config.php';
require_once 'test_engine.php';

$testsDir = __DIR__ . '/tests/';
$testsList = getTestsList($testsDir);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
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
            max-width: 1000px;
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
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .test-list th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }
        
        .test-list td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .test-list tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .info-text {
            text-align: center;
            color: #666;
            padding: 40px;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>👑 Админ-панель</h1>
            <div class="nav-links">
                <a href="index.php">Главная</a>
                <a href="logout.php">Выход</a>
            </div>
        </header>
        
        <div class="card">
            <div class="warning">
                <strong>⚠ Режим заглушки:</strong> База данных не используется. Управление тестами доступно через файловую систему.
            </div>
            
            <h2>Управление тестами</h2>
            <p>Всего тестов: <?= count($testsList) ?></p>
            
            <?php if (empty($testsList)): ?>
                <p class="info-text">Нет доступных тестов</p>
            <?php else: ?>
                <table class="test-list">
                    <thead>
                        <tr>
                            <th>Файл</th>
                            <th>Категория</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testsList as $file => $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($file) ?></td>
                                <td><?= htmlspecialchars($category) ?></td>
                                <td>
                                    <a href="?view=<?= urlencode($file) ?>" class="btn btn-small">👁️ Просмотр</a>
                                    <a href="?edit=<?= urlencode($file) ?>" class="btn btn-small">✏️ Редактировать</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <h3>📁 Работа с файлами тестов</h3>
                <p>Тесты хранятся в папке <strong>/tests/</strong> в формате JSON</p>
                <p>Для добавления нового теста создайте JSON-файл в папке tests</p>
            </div>
        </div>
    </div>
</body>
</html>