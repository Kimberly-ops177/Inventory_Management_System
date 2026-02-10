<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\View;
use App\Services\AuditLogger;

class AuthController
{
    private Auth $auth;
    private View $view;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
        $this->view = new View();
    }

    public function showLoginForm(): string
    {
        $content = $this->view->render('auth/login');
        return $this->view->layout('auth', $content, ['title' => 'Login']);
    }

    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $content = $this->view->render('auth/login', ['error' => 'Email and password required']);
            echo $this->view->layout('auth', $content, ['title' => 'Login']);
            return;
        }

        $ok = $this->auth->attempt($email, $password);
        if (!$ok) {
            $content = $this->view->render('auth/login', ['error' => 'Invalid credentials']);
            echo $this->view->layout('auth', $content, ['title' => 'Login']);
            return;
        }

        // Log successful login
        $user = $this->auth->user();
        if ($user) {
            AuditLogger::logLogin((int)$user['id'], $email);
        }

        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        $user = $this->auth->user();
        if ($user) {
            AuditLogger::logLogout((int)$user['id']);
        }

        $this->auth->logout();
        header('Location: /login');
        exit;
    }
}
