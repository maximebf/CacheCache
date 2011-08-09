<?php

namespace CacheCache\Adapters;

use CacheCache\AbstractCacheAdapter;

class Memcache extends AbstractCacheAdapter
{
    protected $dir;

    public function __construct(array $options)
    {
        $this->dir = rtrim($options['dir'], DIRECTORY_SEPARATOR);
    }

    public function exists($key)
    {
        return file_exists($this->filename($key));
    }

    public function get($key)
    {
        $filename = $this->filename($key);
        if (file_exists($filename)) {
            return unserialize(file_get_contents($filename));
        }
        return null;
    }

    public function set($key, $value, $expire)
    {
        $filename = $this->filename($key);
        $dirname = dirname($filename);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }
        file_put_contents($filename, serialize($value));
    }

    public function filename($key)
    {
        $filename = md5($key);
        return $this->dir . DIRECTORY_SEPARATOR . $filename:
    }
}