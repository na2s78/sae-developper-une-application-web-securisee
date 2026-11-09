<?php

spl_autoload_register(function ($classname) {
    $chemin = str_replace('touiteur\\', '', $classname);
    
    $chemin = str_replace('\\', DIRECTORY_SEPARATOR, $chemin);
    
    $filename = __DIR__ . DIRECTORY_SEPARATOR . $chemin . '.php';

    if (is_file($filename)) {
        require_once($filename);
    }
});