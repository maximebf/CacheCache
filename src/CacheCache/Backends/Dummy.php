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
 * Dummy
 *
 * Does nothing. Can be used to disable caching
 */
class Dummy extends AbstractBackend
{
    public function get($id)
    {
        return null;
    }

    public function set($id, $value, $ttl = null)
    {
        return true;
    }

    public function delete($id)
    {
        return true;
    }

    public function flushAll()
    {
        return true;
    }
}
