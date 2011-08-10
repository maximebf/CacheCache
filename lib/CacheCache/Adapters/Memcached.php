<?php

namespace CacheCache\Adapters;

class Memcached extends AbstractAdapter
{
    protected $memcached;

    public function __construct(array $options)
    {
        if (isset($options['memcached'])) {
            $this->memcached = $options['memcached'];
        } else {
            $host = isset($options['host']) ? $options['host'] : 'localhost';
            $port = isset($options['port']) ? $options['port'] : 11211;
            $this->memcached = new \Memcached();
            $this->memcached->addServer($host, $port);
        }
    }

    public function get($key)
    {
        if (($value = $this->memcached->get($key)) === false) {
            return null;
        }
        return $value;
    }

    public function getMulti(array $keys)
    {
        $null = null;
        return $this->memcached->getMulti($keys, $null, \Memcached::GET_PRESERVE_ORDER);
    }

    public function add($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        if ($expire !== 0) {
            $expire = time() + $expire;
        }
        return $this->memcached->add($key, $value, 0, $expire);
    }

    public function set($key, $value, $expire = null)
    {
        $expire = $expire ?: 0;
        if ($expire !== 0) {
            $expire = time() + $expire;
        }
        return $this->memcached->set($key, $value, 0, $expire);
    }

    public function setMulti(array $items, $expire = null)
    {
        $expire = $expire ?: 0;
        if ($expire !== 0) {
            $expire = time() + $expire;
        }
        $this->memcached->setMulti($items, $expire);
    }

    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    public function flushAll()
    {
        $this->memcached->flush();
    }
}