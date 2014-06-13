<?php
// You should use composers autoloader!!! This is just for demo purposes...
spl_autoload_register(function ($class) {
    // strip our vendor
    $class = str_replace("stratease\ImagiFly\\", "", $class);
    // break into paths
    $classPath = __DIR__.'/src/' .str_replace("\\", "/", $class).".php";

    if(file_exists($classPath)) {
        require_once( $classPath );
    }
});