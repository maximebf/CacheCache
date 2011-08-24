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
 * Memory
 *
 * Caches data in memory for the time of the script's execution
 */
class Memory extends AbstractBackend
{
    protected $cache = array();

    protected $ttls = array();

    public function get($id)
    {
        if (!array_key_exists($id, $this->cache)) {
            return null;
        } else if (isset($this->ttls[$id]) && $this->ttls[$id] < time()) {
            unset($this->cache[$id]);
            unset($this->ttls[$id]);
            return null;
        }
        return $this->cache[$id];
    }

    public function add($id, $value, $ttl = null)
    {
        if (!array_key_exists($id, $this->cache)) {
            return $this->set($id, $value, $ttl);
        }
        return true;
    }

    public function set($id, $value, $ttl = null)
    {
        $this->cache[$id] = $value;
        if ($ttl) {
            $this->ttls[$id] = time() + $ttl;
        }
        return true;
    }

    public function delete($id)
    {
        if (!array_key_exists($id, $this->cache)) {
            return false;
        }
        unset($this->cache[$id]);
        if (isset($this->ttls[$id])) {
            unset($this->ttls[$id]);
        }
        return true;
    }

    public function flushAll()
    {
        $this->cache = array();
        $this->ttls = array();
        return true;
    }
}