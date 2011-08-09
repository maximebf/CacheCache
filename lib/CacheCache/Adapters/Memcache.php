<?php

namespace CacheCache\Adapters;

use CacheCache\AbstractCacheAdapter,
    Memcache;

class Memcache extends AbstractCacheAdapter
{
    protected $memcache;

    public function __construct(array $options)
    {
        $this->memcache = new Memcache();
        $this->memcache->addServer($options['host'], $options['port']);
    }

    public function get($key)
    {
        if (($value = $this->memcache->get($key)) === false) {
            return null;
        }
        return $value;
    }

    public function set($key, $value, $expire)
    {
        return $this->memcache->set($key, $value, 0, time() + $expire);
    }
}