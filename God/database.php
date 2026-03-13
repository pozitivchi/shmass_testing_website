<?php
declare(strict_types=1);

require_once 'config.php';

/**
 * Заглушка для подключения к базе данных
 * @return array
 */
function getDB(): array
{
    // Возвращаем заглушку вместо реального PDO
    return ['dummy' => 'connection'];
}

/**
 * Заглушка для инициализации базы данных
 */
function initializeDatabase(): void
{
    // Ничего не делаем, просто заглушка
}

/**
 * Заглушка для получения пользователя
 */
function getUserByUsername(string $username): ?array
{
    // Для тестирования возвращаем тестового пользователя
    if ($username === 'admin') {
        return [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@localhost',
            'role' => 'admin'
        ];
    }
    
    if ($username === 'user') {
        return [
            'id' => 2,
            'username' => 'user',
            'password' => password_hash('user123', PASSWORD_DEFAULT),
            'email' => 'user@localhost',
            'role' => 'user'
        ];
    }
    
    return null;
}

/**
 * Заглушка для сохранения результатов теста
 */
function saveTestResult(array $result, ?int $userId = null): bool
{
    // Просто заглушка, ничего не сохраняем
    return true;
}