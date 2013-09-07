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
 * Apc
 */
class Apc extends AbstractBackend
{
    public function exists($id)
    {
        return apc_exists($id);
    }

    public function get($id)
    {
        if (($value = apc_fetch($id)) === false) {
            return null;
        }
        return $value;
    }

    public function add($id, $value, $ttl = null)
    {
        return apc_add($id, $value, $ttl ?: 0);
    }

    public function set($id, $value, $ttl = null)
    {
        return apc_store($id, $value, $ttl ?: 0);
    }

    public function delete($id)
    {
        return apc_delete($id);
    }

    public function flushAll()
    {
        return apc_clear_cache('user');
    }
}
