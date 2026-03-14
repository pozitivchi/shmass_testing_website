<?php
require_once 'admin_manager.php';
$admin = new AdminManager();

$action = $_GET['action'] ?? 'list';
$file = $_GET['file'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tempQuestions = $_POST['questions'] ?? [];
    $tempCategory = $_POST['category'] ?? '';
    $fileName = $_POST['file_name'] ?: ($file ?: 'new_test');

    if (isset($_POST['add_q'])) {
        $tempQuestions[] = ['question' => '', 'options' => ['', '', ''], 'correct_indices' => [], 'points' => 1];
        $data = ['category' => $tempCategory, 'questions' => $tempQuestions];
    } 
    elseif (isset($_POST['remove_last_q'])) {
        if (count($tempQuestions) > 1) array_pop($tempQuestions);
        $data = ['category' => $tempCategory, 'questions' => $tempQuestions];
    }
    elseif (isset($_POST['save_test'])) {
        if ($admin->saveTest($fileName, $tempCategory, $tempQuestions)) {
            header("Location: ?action=list&msg=success"); exit;
        }
    }
    elseif (isset($_POST['delete_test_file'])) {
        $admin->deleteTest($_POST['file_to_del']);
        header("Location: ?action=list&msg=deleted"); exit;
    }
}

// Подготовка данных для формы
if ($action === 'edit' && $file) {
    $data = $admin->loadTest($file);
} elseif (!isset($data)) {
    $data = ['category' => '', 'questions' => [['question' => '', 'options' => ['', '', ''], 'correct_indices' => [], 'points' => 1]]];
}

$tests = $admin->listTests();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h1 { font-size: 1.5rem; margin: 0; }

        /* Таблица */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.9rem; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; }

        /* Кнопки */
        .btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer; text-decoration: none; font-size: 0.9rem; background: white; color: #1e293b; }
        .btn-primary { background: #4f46e5; color: white; border: none; }
        .btn-danger { color: #ef4444; background: none; border: none; font-size: 0.85rem; }

        /* Форма */
        .q-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8fafc; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; margin: 5px 0; box-sizing: border-box; }
        .opt-row { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
        .opt-row input[type="text"] { flex: 1; }

        .info-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">

    <?php if ($action === 'list'): ?>
        <div class="header">
            <h1>Все тесты</h1>
            <a href="?action=create" class="btn btn-primary">Создать новый</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Имя теста</th>
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tests as $t): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($t) ?></strong></td>
                        <td style="text-align:right">
                            <a href="?action=edit&file=<?= urlencode($t) ?>" class="btn">Правка</a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить?')">
                                <input type="hidden" name="file_to_del" value="<?= $t ?>">
                                <button type="submit" name="delete_test_file" class="btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <div class="header">
            <h1><?= $action === 'edit' ? "Редактирование" : "Новый тест" ?></h1>
            <a href="?action=list" class="btn">Назад</a>
        </div>

        <form method="POST">
            <label>ID файла:</label>
            <input type="text" name="file_name" value="<?= htmlspecialchars($file) ?>" <?= $action === 'edit' ? 'readonly' : 'required' ?>>
            
            <label>Категория:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($data['category']) ?>" required>

            <div id="questions-container" style="margin-top:20px">
                <?php foreach ($data['questions'] as $qIdx => $q): ?>
                    <div class="q-card">
                        <div style="display:flex; justify-content:space-between">
                            <b>Вопрос <?= $qIdx + 1 ?></b>
                            <input type="number" name="questions[<?= $qIdx ?>][points]" value="<?= $q['points'] ?>" style="width:60px" placeholder="Очки">
                        </div>
                        <input type="text" name="questions[<?= $qIdx ?>][question]" value="<?= htmlspecialchars($q['question']) ?>" placeholder="Текст вопроса" required>
                        
                        <?php foreach ($q['options'] as $oIdx => $opt): ?>
                            <div class="opt-row">
                                <input type="checkbox" name="questions[<?= $qIdx ?>][correct_indices][]" value="<?= $oIdx ?>" <?= in_array($oIdx, $q['correct_indices'] ?? []) ? 'checked' : '' ?>>
                                <input type="text" name="questions[<?= $qIdx ?>][options][<?= $oIdx ?>]" value="<?= htmlspecialchars($opt) ?>" placeholder="Вариант <?= $oIdx + 1 ?>" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex; gap:10px">
                <button type="submit" name="add_q" class="btn" style="flex:1">➕ Добавить вопрос</button>
                <button type="submit" name="remove_last_q" class="btn" style="flex:1">➖ Удалить последний</button>
            </div>

            <button type="submit" name="save_test" class="btn btn-primary" style="width:100%; margin-top:15px; padding:12px">СОХРАНИТЬ</button>
        </form>
    <?php endif; ?>

</div>
<div class="info-text">
        <a href="..\index.php">Назад</a>
    </div>
</body>
</html>
