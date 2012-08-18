<?php
/*
 * This file is part of the CacheCache package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheCache;

/**
 * Pipelines are inspired from Predis. 
 * They allow to easily perform multiple get and set commands.
 *
 * <code>
 *      $pipe = new Pipeline($backend);
 *      $pipe->set('id1', 'value1');
 *      $pipe->set('id2', 'value2');
 *      $pipe->get('id1');
 *      $pipe->get('id2');
 *      $results = $pipe->execute();
 *
 *      // is equivalent to:
 *
 *      $setResults = $backend->setMulti(array('id1' => 'value1', 'id2' => 'value2'));
 *      $getResults = $backend->getMulti(array('id1', 'id2'));
 *      $results = array_merge($setResults, $getResults);
 * </code>
 */
class Pipeline
{
    /** @var Backend */
    protected $backend;

    /** @var array */
    protected $commands = array();

    /** @var int */
    protected $ttl = null;

    /**
     * @param Backend $backend
     */
    public function __construct(Backend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Registers a GET command
     *
     * @param string $id
     */
    public function get($id)
    {
        $this->commands[] = array('get', $id);
    }

    /**
     * Registers a SET command
     *
     * @param string $id
     * @param mixed $value
     */
    public function set($id, $value)
    {
        $this->commands[] = array('set', $id, $value);
    }

    /**
     * Sets the ttl for all SET commands
     *
     * @param int $ttl
     */
    public function ttl($ttl = null)
    {
        $this->ttl = $ttl;
    }

    /**
     * Executes the pipeline and returns results of individual commands
     * as an array.
     *
     * @return array
     */
    public function execute()
    {
        $groups = array();
        $results = array();
        $currentOperation = null;
        $currentGroup = array();

        foreach ($this->commands as $command) {
            if ($currentOperation !== $command[0]) {
                $groups[] = array($currentOperation, $currentGroup);
                $currentOperation = $command[0];
                $currentGroup = array();
            }
            if ($currentOperation === 'get') {
                $currentGroup[] = $command[1];
            } else {
                $currentGroup[$command[1]] = $command[2];
            }
        }
        $groups[] = array($currentOperation, $currentGroup);
        array_shift($groups);

        foreach ($groups as $group) {
            list($op, $args) = $group;
            if ($op === 'set') {
                $result = $this->backend->setMulti($args, $this->ttl);
                $results = array_merge($results, array_fill(0, count($args), $result));
            } else {
                $results = array_merge($results, $this->backend->getMulti($args));
            }
        }

        $this->commands = array();
        return $results;
    }
}
