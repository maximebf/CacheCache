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
 * Backends provide a way to store cached data.
 */
interface Backend
{
    /**
     * Checks if the $id exists
     *
     * @param string $id
     * @return bool
     */
    function exists($id);

    /**
     * Retreives the value associated to the $id from the cache
     *
     * Must return NULL if the $id does not exists.
     *
     * @param string $id
     * @return mixed
     */
    function get($id);

    /**
     * Retreives multiple values at once
     *
     * An array will be returned, containing the values in the 
     * same order as the $ids.
     *
     * @param array $ids
     * @return array
     */
    function getMulti(array $ids);

    /**
     * Stores a $value in the cache under the specified $id only 
     * if it does not exist already.
     *
     * @param string $id
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     */
    function add($id, $value, $ttl = null);

    /**
     * Stores a $value in the cache under the specified $id.
     * Overwrite any existing $id.
     *
     * @param string $id
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     */
    function set($id, $value, $ttl = null);

    /**
     * Sets multiple $id/$value pairs at once
     *
     * @param array $items
     * @param int $ttl Time to live in seconds
     */
    function setMulti(array $items, $ttl = null);

    /**
     * Deletes an $id from the cache
     *
     * @param string $id
     */
    function delete($id);

    /**
     * Deletes all data from the cache
     */
    function flushAll();

    /**
     * Whether this backend supports pipelines
     *
     * @see Pipeline
     * @return bool
     */
    function supportsPipelines();

    /**
     * Creates a new pipeline
     *
     * Pipelines can be custom classes
     *
     * @return object
     */
    function createPipeline();
}