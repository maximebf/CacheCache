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
 * Wraps a backend to provide insights on its usage
 */
class ProfiledBackend implements Backend
{
    /** @var Backend */
    protected $backend;

    /** @var Profiler */
    protected $profiler;

    /**
     * @param Backend $backend
     * @param Profiler $profiler
     */
    public function __construct(Backend $backend, Profiler $profiler = null)
    {
        $this->backend = $backend;
        $this->profiler = $profiler;
    }

    /**
     * @return Backend
     */
    public function getProfiledBackend()
    {
        return $this->backend;
    }

    /**
     * @param Profiler $profiler
     */
    public function setProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        $this->profiler->start('exists', $id);
        $exists = $this->backend->exists($id);
        $this->profiler->stop($exists);
        return $exists;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $this->profiler->start('get', $id);
        $value = $this->backend->get($id);
        $this->profiler->stop($value !== null);
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $this->profiler->start('getMulti', $ids);
        $values = $this->backend->getMulti($ids);
        $this->profiler->stop($values !== null);
        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        $this->profiler->start('add', $id, $ttl);
        $success = $this->backend->add($id, $value, $ttl);
        $this->profiler->stop($success);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $value, $ttl = null)
    {
        $this->profiler->start('set', $id, $ttl);
        $success = $this->backend->set($id, $value, $ttl);
        $this->profiler->stop($success);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        $this->profiler->start('setMulti', array_keys($items), $ttl);
        $success = $this->backend->setMulti($items, $ttl);
        $this->profiler->stop($success);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $this->profiler->start('delete', $id);
        $success = $this->backend->delete($id);
        $this->profiler->stop($success);
        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function flushAll()
    {
        $this->profiler->start('flushAll');
        $success = $this->backend->flushAll();
        $this->profiler->stop($success);
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