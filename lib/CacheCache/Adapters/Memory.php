<?php

namespace CacheCache\Adapters;

use CacheCache\AbstractCacheAdapter;

class Memory extends AbstractCacheAdapter
{
    protected $cache;

    public function get($key)
    {
        if (!array_key_exists($key, $this->cache)) {
            return null;
        }
        return $this->cache[$key];
    }

    public function set($key, $value, $expire)
    {
        return $this->cache[$key] = $value;
    }
}