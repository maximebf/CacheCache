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

use CacheCache\Backend;

/**
 * Base class for backends
 */
abstract class AbstractBackend implements Backend
{
    /**
     * {@inheritDoc}
     */
    public function exists($id)
    {
        return $this->get($id) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti(array $ids)
    {
        $values = array();
        foreach ($ids as $id) {
            $values[] = $this->get($id);
        }
        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function add($id, $value, $ttl = null)
    {
        return $this->set($id, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti(array $items, $ttl = null)
    {
        foreach ($items as $id => $value) {
            if (!$this->set($id, $value, $ttl)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPipelines()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function createPipeline()
    {
        return null;
    }
}