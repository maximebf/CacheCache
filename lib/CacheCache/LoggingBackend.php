<?php
/*
 * This file is part of the MetaP package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache;

use Monolog\Logger;

/**
 * Wraps a backend to provide insights on its usage
 */
class LoggingBackend implements Backend
{
    /** @var Backend */
    protected $backend;

    /** @var Logger */
    protected $logger;

    /** @var int */
    protected $logLevel;

    /**
     * @see Logger
     * @param Backend $backend
     * @param Logger $logger
     * @param int $logLevel
     */
    public function __construct(Backend $backend, Logger $logger, $logLevel = null)
    {
        $this->backend = $backend;
        $this->logger = $logger;
        $this->logLevel = $logLevel ?: Logger::DEBUG;
    }

    /**
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param int $level
     */
    public function setLogLevel($level)
    {
        $this->logLevel = $level;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * Logs an operation
     * 
     * @param string $operation
     * @param string|array $id
     * @param int $ttl
     * @param bool $hit
     */
    protected function log($operation, $id = null, $ttl = null, $hit = null)
    {
        $message = strtoupper($operation);
        if ($id !== null) {
            $id = implode(', ', (array) $id);
            if ($ttl !== null) {
                $message = sprintf('%s(%s, ttl=%s)', $message, $id, $ttl);
            } else {
                $message = sprintf('%s(%s)', $message, $id);
            }
        }
        if ($hit !== null) {
            $message .= ' = ' . ($hit ? 'HIT' : 'MISS');
        }
        $this->logger->addRecord($this->logLevel, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        $exists = $this->backend->exists($id);
        $this->log("exists", $id, null, $exists);
        return $exists;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $value = $this->backend->get($id);
        $this->log("get", $id, null, $value !== null);
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $values = $this->backend->getMulti($ids);
        $this->log("getMulti", $ids, null, $values !== null);
        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        $success = $this->backend->add($id, $value, $ttl);
        $this->log('add', $id, $ttl);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $value, $ttl = null)
    {
        $success = $this->backend->set($id, $value, $ttl);
        $this->log('set', $id, $ttl);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        $success = $this->backend->setMulti($items, $ttl);
        $this->log('setMulti', array_keys($items), $ttl);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $success = $this->backend->delete($id);
        $this->log('delete', $id);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function flushAll()
    {
        $success = $this->backend->flushAll();
        $this->log('flushAll');
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPipelines()
    {
        return $this->backend->supportsPipelines();
    }

    /**
     * {@inheritDoc}
     */
    public function createPipeline()
    {
        return $this->backend->createPipeline();
    }
}
