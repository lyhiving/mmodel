<?php
spl_autoload_register(function ($className) {
    $namespace = 'lyhiving\\mmodel';
    if (strpos($className, $namespace) === 0) {
        $className = str_replace($namespace, '', $className);
        $fileName = __DIR__ . '/src/' . ltrim(str_replace('\\', '/', $className), "/") . '.php';
        if (file_exists($fileName)) {
            require ($fileName);
        }
    }
});
