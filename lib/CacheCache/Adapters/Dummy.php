<?php

namespace CacheCache\Adapters;

class Dummy extends AbstractAdapter
{
    public function get($key)
    {
        return null;
    }

    public function set($key, $value, $expire = null)
    {
        
    }

    public function delete($key)
    {
        
    }

    public function flushAll()
    {
        
    }
}