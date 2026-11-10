<?php
namespace touiteur\actions;

use PDO;

abstract class Action
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract public function execute(): string;

    protected function renderView(string $name, array $data = []): string
    {
        $view = __DIR__ . '/../vues_html/' . $name . '.php';
        if (!is_file($view)) {
            throw new \RuntimeException('Vue introuvable.');
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $view;
        return (string) ob_get_clean();
    }

    protected function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function redirect(string $url = '?action=home'): void
    {
        header('Location: ' . $url, true, 303);
        exit;
    }
}
