<?php

include 'bootstrap.php';
$cache = CacheCache\CacheManager::get();

class HelloWorld
{
    public function hello()
    {
        sleep(1);
        return "hello world\n";
    }
}

$obj = $cache->wrap(new HelloWorld());
echo $obj->hello();
echo $obj->hello();