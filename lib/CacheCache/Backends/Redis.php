<?php
/**
 * CacheCache
 * Copyright (c) Maxime Bouroumeau-Fuseau
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Maxime Bouroumeau-Fuseau
 * @copyright (c) Maxime Bouroumeau-Fuseau
 * @license http://www.opensource.org/licenses/mit-license.php
 */

namespace CacheCache\Backends;

use Predis,
    CacheCache\Backend;

/**
 * Redis
 */
class Redis implements Backend
{
    /** @var Predis\Client */
    protected $redis;

    /**
     * Constructor
     *
     * If $options contains a single key named redis with
     * a Predis\Client instance, it will be used.
     * Otherwise, creates a Predis\Client using the $options
     * array as the constructor argument.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (isset($options['redis'])) {
            $this->redis = $options['redis'];
        } else {
            $this->redis = new Predis\Client($options);
        }
    }

    public function exists($id)
    {
        return $this->redis->exists($id);
    }

    public function get($id)
    {
        return $this->redis->get($id);
    }

    public function getMulti(array $ids)
    {
        $pipe = $this->redis->pipeline();
        array_map(array($pipe, 'get'), $ids);
        return $pipe->execute();
    }

    public function add($id, $value, $ttl = null)
    {
        try {
            $this->redis->setnx($id, $value);
            if ($ttl) {
                $this->redis->expire($id, $ttl);
            }
            return true;
        } catch (Predis\PredisException $e) {
            return false;
        }
    }

    public function set($id, $value, $ttl = null)
    {
        try {
            $this->redis->set($id, $value);
            if ($ttl) {
                $this->redis->expire($id, $ttl);
            }
            return true;
        } catch (Predis\PredisException $e) {
            return false;
        }
    }

    public function setMulti(array $items, $ttl = null)
    {
        $pipe = $this->redis->pipeline();
        foreach ($items as $id => $value) {
            $pipe->set($id, $value);
            if ($ttl) {
                $pipe->expire($id, $ttl);
            }
        }
        $pipe->execute();
        return true;
    }

    public function delete($id)
    {
        return $this->redis->del($id);
    }

    public function flushAll()
    {
        $this->redis->flushdb();
    }

    public function supportsPipelines()
    {
        return true;
    }

    public function createPipeline()
    {
        return $this->redis->pipeline();
    }
}