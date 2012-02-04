<?php

include __DIR__ . '/../tests/bootstrap.php';

$logger = null;
if (class_exists('Monolog\Logger')) {
    $logger = new Monolog\Logger('CacheCache');
}

CacheCache\CacheManager::setup(new CacheCache\Backends\Memory(), $logger);
