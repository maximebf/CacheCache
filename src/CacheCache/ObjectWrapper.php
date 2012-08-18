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
 * Wraps an object and cache all the calls to its methods.
 *
 * Cached methods include __toString() and __invoke().
 * Access to properties will be directly forwarded to the wrapped
 * object without being cached.
 *
 * <code>
 *      $obj = new ObjectWrapper(new MyClass(), $backend);
 *      $data = $obj->myMethod();
 * </code>
 */
class ObjectWrapper
{
    /** @var Backend */
    protected $backend;

    /** @var object */
    protected $object;

    /**
     * @param object $object
     * @param Backend $backend
     */
    public function __construct($object, Backend $backend)
    {
        $this->object = $object;
        $this->backend = $backend;
    }

    public function __get($name)
    {
        return $this->object->$name;
    }

    public function __set($name, $value)
    {
        $this->object->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->object->$name);
    }

    public function __unset($name)
    {
        unset($this->object->$name);
    }

    public function __toString()
    {
        return $this->__call('__toString', func_get_args());
    }

    public function __invoke()
    {
        return $this->__call('__invoke', func_get_args());
    }

    public function __call($method, $args)
    {
        $id = md5($method . serialize($args) . serialize(get_object_vars($this->object)));
        if (($value = $this->backend->get($id)) === null) {
            $value = call_user_func_array(array($this->object, $method), $args);
            $this->backend->add($id, $value);
        }
        return $value;
    }
}
