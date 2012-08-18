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
 * Memory
 *
 * Caches data in memory for the time of the script's execution
 */
class Memory extends AbstractBackend
{
    protected $cache = array();

    protected $ttls = array();

    public function get($id)
    {
        if (!array_key_exists($id, $this->cache)) {
            return null;
        } else if (isset($this->ttls[$id]) && $this->ttls[$id] < time()) {
            unset($this->cache[$id]);
            unset($this->ttls[$id]);
            return null;
        }
        return $this->cache[$id];
    }

    public function add($id, $value, $ttl = null)
    {
        if (!array_key_exists($id, $this->cache)) {
            return $this->set($id, $value, $ttl);
        }
        return true;
    }

    public function set($id, $value, $ttl = null)
    {
        $this->cache[$id] = $value;
        if ($ttl) {
            $this->ttls[$id] = time() + $ttl;
        }
        return true;
    }

    public function delete($id)
    {
        if (!array_key_exists($id, $this->cache)) {
            return false;
        }
        unset($this->cache[$id]);
        if (isset($this->ttls[$id])) {
            unset($this->ttls[$id]);
        }
        return true;
    }

    public function flushAll()
    {
        $this->cache = array();
        $this->ttls = array();
        return true;
    }

    public function toArray()
    {
        return $this->cache;
    }
}
