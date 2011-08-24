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
 * Apc
 */
class Apc extends AbstractBackend
{
    public function exists($id)
    {
        return apc_exists($id);
    }

    public function get($id)
    {
        if (($value = apc_fetch($id)) === false) {
            return null;
        }
        return $value;
    }

    public function add($id, $value, $ttl = null)
    {
        return apc_add($id, $value, $ttl ?: 0);
    }

    public function set($id, $value, $ttl = null)
    {
        return apc_store($id, $value, $ttl ?: 0);
    }

    public function delete($id)
    {
        return apc_delete($id);
    }

    public function flushAll()
    {
        return false;
    }
}