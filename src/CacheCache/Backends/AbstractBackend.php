<?php
/*
 * This file is part of the MetaP package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache\Backends;

use CacheCache\Backend;

/**
 * Base class for backends
 */
abstract class AbstractBackend implements Backend
{
    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        return $this->get($id) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $values = array();
        foreach ($ids as $id) {
            $values[] = $this->get($id);
        }
        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        return $this->set($id, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        foreach ($items as $id => $value) {
            if (!$this->set($id, $value, $ttl)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPipelines()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function createPipeline()
    {
        return null;
    }
}
