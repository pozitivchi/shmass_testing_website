<?php
// Файл: Auth.php

class Auth {
    private $dbStub;

    public function __construct() {
        // Пока БД нет, используем заглушку прямо здесь
        $this->dbStub = [
            'admin' => ['password' => '123', 'role' => 'admin', 'name' => 'Иван (Админ)'],
            'user'  => ['password' => 'qwerty', 'role' => 'user', 'name' => 'Артем (Разработчик)']
        ];
    }

    /**
     * Пытается войти в систему
     */
    public function login(string $username, string $password): bool {
        // ЗАГЛУШКА: В будущем тут будет вызов $this->db->getUser($username)
        if (isset($this->dbStub[$username]) && $this->dbStub[$username]['password'] === $password) {
            
            // "Вешаем бейджик" — записываем данные в сессию
            $_SESSION['user'] = [
                'username' => $username,
                'role'     => $this->dbStub[$username]['role'],
                'name'     => $this->dbStub[$username]['name']
            ];
            return true;
        }
        return false;
    }

    /**
     * Проверяет, залогинен ли пользователь
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    /**
     * Проверяет роль (например, 'admin')
     */
    public static function hasRole(string $role): bool {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === $role;
    }

    /**
     * Выход из системы
     */
    public static function logout(): void {
        unset($_SESSION['user']);
        session_destroy();
    }
}