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
 * Cache frontend
 *
 * Provides facility methods to interact with a backend.
 */
class Cache implements Backend
{
    /** @var string */
    public static $namespaceSeparator = ':';

    /** @var string */
    protected $namespace;

    /** @var Backend */
    protected $backend;

    /** @var int */
    protected $defaultTTL;

    /** @var int */
    protected $ttlVariation = 0;

    /** @var array */
    protected $stack = array();

    /** @var int */
    protected $capturing = 0;

    /**
     * @param Backend $backend
     * @param string $namespace
     * @param int $defaultTTL
     * @param int $ttlVariation
     */
    public function __construct(Backend $backend, $namespace = '', $defaultTTL = null, $ttlVariation = 0)
    {
        $this->backend = $backend;
        $this->namespace = $namespace;
        $this->defaultTTL = $defaultTTL;
        $this->ttlVariation = $ttlVariation;
    }

    /**
     * Sets the backend of this cache
     * 
     * @param Backend $backend
     */
    public function setBackend(Backend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Sets the namespace for cache ids
     * 
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Default time to live value in seconds for all data modification queries
     *
     * @param int $ttl
     */
    public function setDefaultTTL($ttl)
    {
        $this->defaultTTL = $tll;
    }

    /**
     * @return int
     */
    public function getDefaultTTL()
    {
        return $this->defaultTTL;
    }

    /**
     * To avoid that all values invalidates at the same time, a small
     * variation can be added to TTL values of all data modification queries.
     *
     * @param int $amplitude Maximum value in seconds the variation can be
     */
    public function setTTLVariation($amplitude)
    {
        $this->ttlVariation = $amplitude;
    }

    /**
     * @return int
     */
    public function getTTLVariation()
    {
        return $this->ttlVariation;
    }

    /**
     * Computes the final TTL taking into account the default ttl
     * and the ttl variation
     *
     * @param int $ttl
     * @return int
     */
    public function computeTTL($ttl = null)
    {
        $ttl = $ttl ?: $this->defaultTTL;
        if ($ttl === null) {
            return null;
        }
        return $ttl + rand(0, $this->ttlVariation);
    }

    /**
     * Computes and id as it will be inserted into the backend
     * (ie. taking into account the namespace)
     *
     * Any number of parameters can be used with this method.
     * They will all be concatenated with the $namespaceSeparator.
     *
     * @param string $id
     * @return string
     */
    public function id($id)
    {
        $parts = array_merge(array($this->namespace), func_get_args());
        return trim(implode(self::$namespaceSeparator, $parts), self::$namespaceSeparator);
    }

    /**
     * Returns a {@see Cache} object for a sub-namespace.
     *
     * @param string $namespace
     * @param int $defaultTTL
     * @return Cache
     */
    public function ns($namespace, $defaultTTL = null)
    {
        $namespace = $this->id($namespace);
        $defaultTTL = $defaultTTL ?: $this->defaultTTL;
        return new Cache($this->backend, $namespace, $defaultTTL, $this->ttlVariation);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        $id = $this->id($id);
        return $this->backend->exists($id);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $default = null)
    {
        $id = $this->id($id);
        if (($value = $this->backend->get($id)) === null) {
            return $default;
        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $ids = array_map(array($this, 'id'), $ids);
        return $this->backend->getMulti($ids);
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        $id = $this->id($id);
        $ttl = $this->computeTTL($ttl);
        return $this->backend->add($id, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $value, $ttl = null)
    {
        $id = $this->id($id);
        $ttl = $this->computeTTL($ttl);
        return $this->backend->set($id, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        $ids = array_map(array($this, 'id'), array_keys($items));
        $items = array_combine($ids, array_values($items));
        $ttl = $this->computeTTL($ttl);
        return $this->backend->setMulti($items, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $id = $this->id($id);
        return $this->backend->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function flushAll()
    {
        return $this->backend->flushAll();
    }

    /**
     * Returns the value of $id if it exists. Sets $id to $value otherwise.
     *
     * $value can be a closure that will only be called if $id does not exists.
     * It must return the value to be added to the cache.
     *
     * @param string $id
     * @param mixed|Closure $value
     * @param int $ttl
     * @return mixed
     */
    public function getset($id, $value, $ttl = null)
    {
        if (($v = $this->get($id)) === null) {
            if ($value instanceof \Closure) {
                $v = $value($this);
            } else {
                $v = $value;
            }
            $this->add($id, $v, $ttl);
        }
        return $v;
    }

    /**
     * Tries to fetch the $id entry. If it does not exist, returns false
     * and saves the $id for a later call to {@see save()}.
     *
     * Nested calls can be performed.
     *
     * <code>
     *      if (!($data = $cache->load('myid'))) {
     *          // do heavy stuff
     *          // $data = ...
     *          $cache->save($data);
     *      }
     * </code>
     *
     * @param string $id
     * @return mixed
     */
    public function load($id)
    {
        if (($value = $this->get($id)) !== null) {
            return $value;
        }
        $this->stack[] = $id;
        return false;
    }

    /**
     * Saves some $data in the cache using the last $id
     * provided to the {@see load()} method.
     *
     * @param mixed $data
     * @param int $ttl
     */
    public function save($data, $ttl = null)
    {
        if (empty($this->stack)) {
            throw new CacheException("Cache::load() must be called before Cache::save()");
        }
        $id = array_pop($this->stack);
        return $this->add($id, $data, $ttl);
    }

    /**
     * Similar to {@see load()} but starts capturing the output if $id is not
     * found or echoes (unless $echo is set to false) the retreived value.
     *
     * Nested calls can be performed.
     *
     * <code>
     *      if (!$cache->start('myid')) {
     *          echo "lots of data";
     *          $cache->end();
     *      }
     * </code>
     *
     * @param string $id
     * @param bool $echo
     * @return mixed
     */
    public function start($id, $echo = true)
    {
        if (($output = $this->load($id)) === false) {
            ob_start();
            $this->capturing++;
            return false;
        }
        if ($echo) {
            echo $output;
        }
        return $output;
    }

    /** 
     * Similar to {@see save()} but saves the output since the last call
     * to {@see start()}. Also echoes the output unless $echo is set to false.
     *
     * @param int $ttl
     * @param bool $echo
     * @return string The captured output
     */
    public function end($ttl = null, $echo = true)
    {
        if (!empty($this->stack) && $this->capturing > 0) {
            $output = ob_get_clean();
            $this->save($output, $ttl);
            $this->capturing--;
            if ($echo) {
                echo $output;
            }
            return $output;
        }
        return false;
    }

    /**
     * Checks if a capture started by {@see start()} is currently being performed.
     *
     * @return bool
     */
    public function isCapturing()
    {
        return $this->capturing > 0;
    }

    /**
     * Cancels the last call to either {@see load()} or {@see start()}. Further
     * calls to {@see save()} or {@see end()} will be ignored.
     */
    public function cancel()
    {
        if (!empty($this->stack)) {
            array_pop($this->stack);
            if ($this->capturing > 0) {
                $this->capturing--;
                ob_end_flush();
            }
        }
    }

    /**
     * Similar to {@see start()} followed by {@see end()} but will capture
     * all the output done while $callback is executed.
     *
     * <code>
     *      $cache->capture('myid', function() {
     *          echo "lots of data";
     *      })
     * </code>
     *
     * @param string $id
     * @param callback $callback
     * @param int $ttl
     * @param bool $echo
     * @return string
     */
    public function capture($id, $callback, $ttl = null, $echo = true)
    {
        if (($output = $this->start($id, $echo)) === false) {
            call_user_func($callback, $this);
            return $this->end($ttl, $echo);
        }
        return $output;
    }

    /**
     * Captures the whole output of the script until it ends.
     *
     * If no $id is specified, it will be computed from a combination
     * of the $_SERVER['REQUEST_URI'] and the $_REQUEST variables.
     *
     * If $id is found, the script will exit unless $exit is set to false.
     *
     * @param string $id
     * @param int $ttl
     * @param bool $exit
     * @return bool
     */
    public function capturePage($id = null, $ttl = null, $exit = true)
    {
        if ($id === null) {
            $id = md5(serialize($_SERVER['REQUEST_URI']) . serialize($_REQUEST));
        }

        if ($this->start($id)) {
            if ($exit) {
                exit;
            }
            return true;
        }
        $self = $this;
        register_shutdown_function(function() use ($self) { $self->end($ttl); });
        return false;
    }

    /**
     * Works the same way as {@see call_user_func_array()} but calls are cached.
     *
     * The cache id will be computed from the $callback and the $args.
     *
     * <code>
     *      function do_heavy_computing($data) { }
     *      $result = $cache->call('do_heavy_computing', array($data));
     * </code>
     *
     * @param callback $callback Any callbacks unless it is a closure, in this case use {@see getset()}
     * @param array $args
     * @param int $ttl
     * @return mixed
     */
    public function call($callback, array $args, $ttl = null)
    {
        $id = md5(serialize($callback) . serialize($args));
        if (($value = $this->get($id)) === null) {
            $value = call_user_func_array($callback, $args);
            $this->add($id, $value, $ttl);
        }
        return $value;
    }

    /**
     * Wraps an object in {@see ObjectWrapper}.
     *
     * @see ObjectWrapper
     * @param object $object
     * @param string $id If null, the object's class name will be used
     * @param int $ttl
     * @return ObjectWrapper
     */
    public function wrap($object, $id = null, $ttl = null)
    {
        $id = $id ?: get_class($object);
        return new ObjectWrapper($object, $this->ns($id, $ttl));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPipelines()
    {
        return true;
    }

    /**
     * Creates a pipeline for batching operations
     *
     * If the backend does not support pipelines, the
     * generic {@see Pipeline} implementation will be used.
     *
     * @return Pipeline
     */
    public function createPipeline()
    {
        if ($this->backend->supportsPipelines()) {
            return $this->backend->createPipeline();
        }
        return new Pipeline($this);
    }

    /**
     * Creates a pipeline, executes the callback which should use
     * the provided pipeline object as its only argument without
     * executing it. The pipeline will the be executed and its
     * results will be returned.
     *
     * If no callback is specified, the pipeline object will be
     * returned without being executed.
     *
     * <code>
     *      $results = $cache->pipeline(function($pipe) {
     *          $pipe->set('id1', 'value1');
     *          $pipe->set('id2', 'value2');
     *      })
     * </code>
     *
     * @param callback $callback
     * @return mixed
     */
    public function pipeline($callback = null)
    {
        $pipe = $this->createPipeline();
        if ($callback === null) {
            return $pipe;
        }
        call_user_func($callback, $pipe);
        return $pipe->execute();
    }
}
