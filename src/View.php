<?php
declare(strict_types=1);

namespace App;

class View
{
    private string $viewsPath;

    public function __construct(string $viewsPath = '')
    {
        $this->viewsPath = $viewsPath ?: dirname(__DIR__) . '/views';
    }

    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->viewsPath . '/' . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View template not found: $templatePath");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $templatePath;
        return ob_get_clean() ?: '';
    }

    public function layout(string $layout, string $content, array $data = []): string
    {
        $layoutPath = $this->viewsPath . '/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout not found: $layoutPath");
        }

        $data['content'] = $content;
        extract($data, EXTR_SKIP);

        ob_start();
        include $layoutPath;
        return ob_get_clean() ?: '';
    }

    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    public static function formatCurrency(float $amount): string
    {
        return 'KES ' . number_format($amount, 2);
    }
}
