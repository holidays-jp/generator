<?php
function loader($class)
{
    $file = "app/{$class}.php";
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register('loader');
