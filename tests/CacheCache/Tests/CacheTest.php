<?php

namespace CacheCache\Tests;

use CacheCache\Cache,
    CacheCache\Backends;

class CacheTest extends CacheTestCase
{
    public function setUp()
    {
        $this->cache = new Cache(new Backends\Memory());
    }

    public function testComputeTTL()
    {
        $this->cache->setTTLVariation(10);
        $ttl = $this->cache->computeTTL(10);
        $this->assertInternalType('int', $ttl);
        $this->assertGreaterThanOrEqual(10, $ttl);
        $this->assertLessThanOrEqual(20, $ttl);
    }

    public function testId()
    {
        $this->assertEquals('foo', $this->cache->id('foo'));
        $this->assertEquals('foo:bar', $this->cache->id('foo', 'bar'));
        Cache::$namespaceSeparator = '-';
        $this->assertEquals('foo-bar', $this->cache->id('foo', 'bar'));
        Cache::$namespaceSeparator = ':';
    }

    public function testNs()
    {
        $cache = $this->cache->ns('foo');
        $this->assertEquals($this->cache->getBackend(), $cache->getBackend());
        $this->assertEquals('foo:bar', $cache->id('bar'));
    }

    public function testExists()
    {
        $this->assertFalse($this->cache->exists('foo'));
        $this->cache->set('foo', 'bar');
        $this->assertTrue($this->cache->exists('foo'));
        $this->cache->set('foo', false);
        $this->assertTrue($this->cache->exists('foo'));
        $this->cache->delete('foo');
        $this->assertFalse($this->cache->exists('foo'));
    }

    public function testGet()
    {
        $this->assertNull($this->cache->get('foo'));
        $this->assertEquals('default', $this->cache->get('foo', 'default'));
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testGetMulti()
    {
        $r = $this->cache->getMulti(array('foo', 'bar'));
        $this->assertCount(2, $r);
        $this->assertNull($r[0]);
        $this->assertNull($r[1]);

        $this->cache->set('foo', 'bar');
        $this->cache->set('bar', 'foo');
        $r = $this->cache->getMulti(array('foo', 'bar'));
        $this->assertCount(2, $r);
        $this->assertEquals('bar', $r[0]);
        $this->assertEquals('foo', $r[1]);
    }

    public function testAdd()
    {
        $this->cache->add('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->cache->add('foo', 'baz');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testSet()
    {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->cache->set('foo', 'baz');
        $this->assertEquals('baz', $this->cache->get('foo'));
    }

    public function testSetTTL()
    {
        $this->cache->set('foo', 'bar', 1);
        $this->assertEquals('bar', $this->cache->get('foo'));
        sleep(2);
        $this->assertFalse($this->cache->exists('foo'));
    }

    public function testSetMulti()
    {
        $this->cache->setMulti(array(
            'foo' => 'bar',
            'bar' => 'foo'
        ));

        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->assertEquals('foo', $this->cache->get('bar'));
    }

    public function testDelete()
    {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->cache->delete('foo');
        $this->assertFalse($this->cache->exists('foo'));
    }

    public function testFlushAll()
    {
        $this->cache->setMulti(array(
            'foo' => 'bar',
            'bar' => 'foo'
        ));
        $this->assertCount(2, $this->cache->getBackend()->toArray());
        $this->cache->flushAll();
        $this->assertEmpty($this->cache->getBackend()->toArray());
    }

    public function testGetSet()
    {
        $closure = function() {
            return 'bar';
        };

        $this->assertEquals('bar', $this->cache->getset('foo', $closure));
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->cache->set('foo', 'baz');
        $this->assertEquals('baz', $this->cache->getset('foo', $closure));
        $this->assertEquals('baz', $this->cache->get('foo'));
    }

    public function testLoadSave()
    {
        $this->assertFalse($this->cache->load('foo'));
        $this->cache->save('bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->assertEquals('bar', $this->cache->load('foo'));
    }

    public function testStartEnd()
    {
        $this->assertFalse($this->cache->start('foo'));
        echo 'bar';
        $this->assertTrue($this->cache->isCapturing());
        $this->cache->end();
        $this->expectOutputString('bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->assertEquals('bar', $this->cache->start('foo', false));
    }

    /**
     * @expectedException        CacheCache\CacheException
     * @expectedExceptionMessage Cache::load() must be called before Cache::save()
     */
    public function testCancel()
    {
        $this->assertFalse($this->cache->load('foo'));
        $this->cache->cancel();
        $this->cache->save('bar');
    }

    public function testCapture()
    {
        $closure = function() {
            echo 'bar';
        };
        $this->cache->capture('foo', $closure);
        $this->expectOutputString('bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
        $this->assertEquals('bar', $this->cache->capture('foo', $closure, null, false));
    }

    public function testCall()
    {
        $start = time();
        $this->cache->call('sleep', array(1));
        $this->assertGreaterThanOrEqual(1, time() - $start);
        $start = microtime(true);
        $this->cache->call('sleep', array(1));
        $this->assertLessThan(500, microtime(true)- $start);
    }
}
