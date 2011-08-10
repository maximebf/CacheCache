<?php

namespace CacheCache\Adapters;

use Predis;

class Redis extends AbstractAdapter
{
    protected $redis;

    public function __construct(array $options)
    {
        if (isset($options['redis'])) {
            $this->redis = $options['redis'];
        } else {
            $this->redis = new Predis\Client($options);
        }
    }

    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function add($key, $value, $expire = null)
    {
        $this->redis->setnx($key, $value);
        if ($expire) {
            $this->redis->expire($key, $expire);
        }
    }

    public function set($key, $value, $expire = null)
    {
        $this->redis->set($key, $value);
        if ($expire) {
            $this->redis->expire($key, $expire);
        }
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function flushAll()
    {
        $this->redis->flushdb();
    }
}