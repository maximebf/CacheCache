<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../lib',
    get_include_path()
)));

spl_autoload_register(function($className) {
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
    require_once $filename;
});

CacheCache\CacheManager::setup(
    new CacheCache\Backends\Memory(), 
    new CacheCache\Profilers\Text()
);