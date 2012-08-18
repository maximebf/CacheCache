<?php
/*
 * This file is part of the CacheCache package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache\Backends;

/**
 * Memcached
 */
class Memcached extends AbstractBackend
{
    /** @var \Memcached */
    protected $memcached;

    /**
     * Constructor
     *
     * Possible options:
     *  - memcached: a \Memcached object
     *  - host
     *  - port
     *
     * @param array $options
     */
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

    public function get($id)
    {
        if (($value = $this->memcached->get($id)) === false) {
            return null;
        }
        return $value;
    }

    public function getMulti(array $ids)
    {
        $null = null;
        return $this->memcached->getMulti($ids, $null, \Memcached::GET_PRESERVE_ORDER);
    }

    public function add($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->add($id, $value, $ttl);
    }

    public function set($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->set($id, $value, $ttl);
    }

    public function setMulti(array $items, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->setMulti($items, $ttl);
    }

    public function delete($id)
    {
        return $this->memcached->delete($id);
    }

    public function flushAll()
    {
        return $this->memcached->flush();
    }
}
