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

namespace CacheCache;

/**
 * Same as {@see Cache} but with support for multiple backends
 */
class MultiCache extends Cache
{
    /** @var array */
    protected $backends = array();

    /**
     * @param array $backends Array of {@see Backend}'s
     * @param string $namespace
     * @param int $defaultTTL
     * @param int $ttlVariation
     */
    public function __construct($backends, $namespace = '', $defaultTTL = 0, $ttlVariation = 0)
    {
        if (!is_array($backends)) {
            $backends = array($backends);
        }

        foreach ($backends as $backend) {
            if (!($backend instanceof Backend)) {
                throw new CacheException("Backends must be instance of CacheCache\Backend in CacheCache\MultiCache");
            }
        }

        $this->backends = $backends;
        $this->namespace = $namespace;
        $this->defaultTTL = $defaultTTL;
        $this->ttlVariation = $ttlVariation;
    }

    /**
     * @return array
     */
    public function getBackends()
    {
        return $this->backends;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        $id = $this->id($id);
        foreach ($this->backends as $backend) {
            if ($backend->exists($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id, $default = null)
    {
        $id = $this->id($id);
        foreach ($this->backends as $backend) {
            if (($value = $backend->get($id)) !== null) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $ids = array_map(array($this, 'id'), $ids);
        foreach ($this->backends as $backend) {
            if (($values = $backend->getMulti($ids)) !== null) {
                return $values;
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        $id = $this->id($id);
        $ttl = $this->computeTTL($ttl);
        foreach ($this->backends as $backend) {
            $backend->add($id, $value, $ttl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $value, $ttl = null)
    {
        $id = $this->id($id);
        $ttl = $this->computeTTL($ttl);
        foreach ($this->backends as $backend) {
            $backend->set($id, $value, $ttl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        $ids = array_map(array($this, 'id'), array_keys($items));
        $items = array_combine($ids, array_values($items));
        $ttl = $this->computeTTL($ttl);
        foreach ($this->backends as $backend) {
            $backend->setMulti($items, $ttl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $id = $this->id($id);
        foreach ($this->backends as $backend) {
            $backend->delete($id);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function flushAll()
    {
        foreach ($this->backends as $backend) {
            $backend->flushAll();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createPipeline()
    {
        return new Pipeline($this);
    }
}