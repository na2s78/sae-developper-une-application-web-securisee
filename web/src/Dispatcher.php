<?php

namespace touiteur;

use PDO;
use touiteur\actions\HomeAction;
use touiteur\actions\DetailAction;

class Dispatcher
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function dispatch(string $action): string
    {
        return match ($action) {
            'home' => (new HomeAction($this->pdo))->execute(),
            'detail' => (new DetailAction($this->pdo))->execute(),
            default => (new HomeAction($this->pdo))->execute(),
        };
    }
}