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

/**
 * Memcached
 */
class Memcached extends AbstractBackend
{
    /** @var \Memcached */
    protected $memcached;

    /**
     * Constructor
     *
     * Possible options:
     *  - memcached: a \Memcached object
     *  - host
     *  - port
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (isset($options['memcached'])) {
            $this->memcached = $options['memcached'];
        } else {
            $host = isset($options['host']) ? $options['host'] : 'localhost';
            $port = isset($options['port']) ? $options['port'] : 11211;
            $this->memcached = new \Memcached();
            $this->memcached->addServer($host, $port);
        }
    }

    public function get($id)
    {
        if (($value = $this->memcached->get($id)) === false) {
            return null;
        }
        return $value;
    }

    public function getMulti(array $ids)
    {
        $null = null;
        return $this->memcached->getMulti($ids, $null, \Memcached::GET_PRESERVE_ORDER);
    }

    public function add($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->add($id, $value, $ttl);
    }

    public function set($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->set($id, $value, $ttl);
    }

    public function setMulti(array $items, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcached->setMulti($items, $ttl);
    }

    public function delete($id)
    {
        return $this->memcached->delete($id);
    }

    public function flushAll()
    {
        return $this->memcached->flush();
    }
}