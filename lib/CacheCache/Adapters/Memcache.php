<?php

namespace CacheCache\Adapters;

class Memcache extends AbstractAdapter
{
    protected $memcache;

    public function __construct(array $options)
    {
        if (isset($options['memcache'])) {
            $this->memcache = $options['memcache'];
        } else {
            $host = isset($options['host']) ? $options['host'] : 'localhost';
            $port = isset($options['port']) ? $options['port'] : 11211;
            $this->memcache = new \Memcache();
            $this->memcache->addServer($host, $port);
        }
    }

    public function get($key)
    {
        if (($value = $this->memcache->get($key)) === false) {
            return null;
        }
        return $value;
    }

    public function add($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        if ($expire !== 0) {
            $expire = time() + $expire;
        }
        return $this->memcache->add($key, $value, 0, $expire);
    }

    public function set($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        if ($expire !== 0) {
            $expire = time() + $expire;
        }
        return $this->memcache->set($key, $value, 0, $expire);
    }

    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    public function flushAll()
    {
        $this->memcache->flush();
    }
}