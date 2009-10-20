<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Array cache driver
 *
 * @package     Doctrine
 * @subpackage  Cache
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Cache_Array extends Doctrine_Cache_Driver implements Countable
{
    /**
     * @var array $data         an array of cached data
     */
    protected $data;

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     * 
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     * 
     * @param string $id cache id
     * @param boolean $testCacheValidity        if set to false, the cache validity won't be tested
     * @return string cached datas (or false)
     */
    public function fetch($id, $testCacheValidity = true) 
    {
        $key = $this->_getKey($id);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function contains($id)
    {
        return isset($this->data[$this->_getKey($id)]);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always saved as a string
     *
     * @param string $data      data to cache
     * @param string $id        cache id
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @param boolean $saveKey  Whether or not to save the key in the cache key index
     * @return boolean true if no problem
     */
    public function save($id, $data, $lifeTime = false, $saveKey = true)
    {
        $key = $this->_getKey($id);

        if ($saveKey) {
            $this->_saveKey($key);
        }

        $this->data[$key] = $data;

        return true;
    }

    /**
     * Remove a cache record
     * 
     * @param string $id cache id
     * @return boolean true if no problem
     */
    public function delete($id)
    {
        $key = $this->_getKey($id);
        $this->_deleteKey($key);
        unset($this->data[$key]);

        return true;
    }

    /**
     * count
     *
     * @return integer
     */
    public function count() 
    {
        $count = count($this->data);
        return $count ? $count - 1 : 0;
    }
}