<?php

namespace CacheCache\Tests;

use CacheCache\Cache,
    CacheCache\Backends,
    CacheCache\ObjectWrapper;

class TestObject 
{
    public $foobar = 'foobar';
    public function longOp($time=1) {
        sleep($time);
    }
}

class ObjectWrapperTest extends CacheTestCase
{
    public function setUp()
    {
        $this->cache = new Cache(new Backends\Memory());
    }
    
    public function testProperties()
    {
        $obj = $this->cache->wrap(new TestObject());
        $this->assertEquals('foobar', $obj->foobar);
    }

    public function testCalls()
    {
        $obj = $this->cache->wrap(new TestObject());

        $start = time();
        $obj->longOp();
        $this->assertGreaterThanOrEqual(1, time() - $start);

        $start = microtime(true);
        $obj->longOp();
        $this->assertLessThan(500, microtime(true) - $start);

        $start = time();
        $obj->longOp(2);
        $this->assertGreaterThanOrEqual(2, time() - $start);
    }
}
