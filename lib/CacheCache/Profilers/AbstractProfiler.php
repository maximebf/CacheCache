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

namespace CacheCache\Profilers;

use CacheCache\Profiler;

/**
 * Base class for profilers
 */
abstract class AbstractProfiler implements Profiler
{
    /** @var array */
    protected $current;

    /**
     * {@inheritDoc}
     */
    public function start($operation, $id = null, $ttl = null)
    {
        $this->current = array(
            microtime(true),
            $operation,
            $id,
            $ttl
        );
    }

    /**
     * {@inheritDoc}
     */
    public function stop($success)
    {
        list($startTime, $operation, $id, $ttl) = $this->current;
        $time = microtime(true) - $startTime;
        $this->log($operation, $id, $ttl, $success, $time);
        $this->current = null;
    }

    /**
     * Subclasses should implement this method to log the result
     * of an operation
     *
     * @param string $operation
     * @param string $id
     * @param int $ttl
     * @param bool $success
     * @param int $time
     */
    abstract function log($operation, $id, $ttl, $success, $time);

    /**
     * Utility method to format a profiling message
     *
     * @return string
     */
    protected function formatMessage($operation, $id, $ttl, $success, $time)
    {
        if ($id !== null) {
            $id = implode(', ', (array) $id);
            if ($ttl !== null) {
                $operation = sprintf('%s(%s, ttl=%s)', strtoupper($operation), $id, $ttl);
            } else {
                $operation = sprintf('%s(%s)', strtoupper($operation), $id);
            }
        }
        return sprintf('%s = %s in %s sec', $operation, $success ? 'HIT' : 'MISS', $time);
    }
}