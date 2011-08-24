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
 * Memcache
 */
class Memcache extends AbstractBackend
{
    /** @var \Memcache */
    protected $memcache;

    /**
     * Constructor
     *
     * Possible options:
     *  - memcache: a \Memcache object
     *  - host
     *  - port
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (isset($options['memcache'])) {
            $this->memcache = $options['memcache'];
        } else {
            $host = isset($options['host']) ? $options['host'] : 'localhost';
            $port = isset($options['port']) ? $options['port'] : 11211;
            $this->memcache = new \Memcache();
            $this->memcache->addServer($host, $port);
        }
    }

    public function get($id)
    {
        if (($value = $this->memcache->get($id)) === false) {
            return null;
        }
        return $value;
    }

    public function add($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcache->add($id, $value, 0, $ttl);
    }

    public function set($id, $value, $ttl = null)
    {
        $ttl = $ttl ?: 0;
        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }
        return $this->memcache->set($id, $value, 0, $ttl);
    }

    public function delete($id)
    {
        return $this->memcache->delete($id);
    }

    public function flushAll()
    {
        return $this->memcache->flush();
    }
}