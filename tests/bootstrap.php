<?php
spl_autoload_register(function ($className) {
    $filename = __DIR__ . '/../lib/' . strtr($className, '\\', '/') . '.php';
    if (file_exists($filename)) {
        include $filename;
    }
});
