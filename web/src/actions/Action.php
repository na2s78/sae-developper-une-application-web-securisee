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
}