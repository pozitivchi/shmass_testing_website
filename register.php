<?php
require_once 'includes/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($name === '') {
        $errors[] = 'Введите имя';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }

    if (strlen($pass1) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }

    if ($pass1 !== $pass2) {
        $errors[] = 'Пароли не совпадают';
    }

    // 🔍 Проверка уникальности email
    if (empty($errors)) {
        $st = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }

    // ✅ Если ошибок нет — регистрируем
    if (empty($errors)) {
        $hash = password_hash($pass1, PASSWORD_BCRYPT);

        $st = $pdo->prepare(
            "INSERT INTO users(name, email, phone, password) VALUES(?,?,?,?)"
        );
        $st->execute([$name, $email, $phone, $hash]);

        header('Location: login.php');
        exit;
    }
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        /* Копируем стили из вашего login.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px; /* Добавлено для мобильных устройств */
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px; /* Немного расширил, так как полей больше */
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px; /* Уменьшил отступ, чтобы форма не была слишком длинной */
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .error-container {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-container ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .info-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .info-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h1>Регистрация</h1>

    <?php if (!empty($errors)): ?>
    <div class="error-container">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li>⚠️ <?=htmlspecialchars($e)?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Имя</label>
            <input type="text" name="name" placeholder="Иван Иванов"
                   value="<?=htmlspecialchars($_POST['name'] ?? '')?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="example@mail.com"
                   value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required>
        </div>

        <div class="form-group">
            <label>Телефон</label>
            <input type="text" name="phone" placeholder="+7 (999) 000-00-00"
                   value="<?=htmlspecialchars($_POST['phone'] ?? '')?>">
        </div>

        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" placeholder="Минимум 6 символов" required>
        </div>

        <div class="form-group">
            <label>Подтверждение пароля</label>
            <input type="password" name="password_confirm" placeholder="Повторите ваш пароль" required>
        </div>

        <button type="submit">Создать аккаунт</button>
    </form>

    <div class="info-text">
        Уже есть аккаунт? <a href="login.php">Войти</a>
    </div>
</div>

</body>
</html>
