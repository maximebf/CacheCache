<?php

include 'bootstrap.php';
$cache = CacheCache\CacheManager::get();

function sayhello($name)
{
    sleep(1);
    return "hello $name\n";
}

echo $cache->call('sayhello', array('peter'));
echo $cache->call('sayhello', array('paul'));
echo $cache->call('sayhello', array('peter'));