<?php
/*
 *  $Id: Hydrate.php 3192 2007-11-19 17:55:23Z romanb $
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
 * Its purpose is to populate object graphs.
 *
 *
 * @package     Doctrine
 * @subpackage  Hydrate
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 3192 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Doctrine_Hydrator
{
    protected static
      $_totalHydrationTime = 0;

    protected 
        $_rootAlias = null,
        $_hydrationMode = Doctrine::HYDRATE_RECORD,
        $_queryComponents = array();

    /**
     * Set the hydration mode
     *
     * @param mixed $hydrationMode  One of the Doctrine::HYDRATE_* constants or 
     *                              a string representing the name of the hydration mode
     */
    public function setHydrationMode($hydrationMode)
    {
        $this->_hydrationMode = $hydrationMode;
    }

    /**
     * Get the hydration mode
     *
     * @return mixed $hydrationMode One of the Doctrine::HYDRATE_* constants
     */
    public function getHydrationMode()
    {
        return $this->_hydrationMode;
    }

    /**
     * Set the array of query components
     *
     * @param array $queryComponents
     */
    public function setQueryComponents(array $queryComponents)
    {
        $this->_queryComponents = $queryComponents;
    }

    /**
     * Get the array of query components
     *
     * @return array $queryComponents
     */
    public function getQueryComponents()
    {
        return $this->_queryComponents;
    }

    /**
     * Hydrate the query statement in to its final data structure by one of the
     * hydration drivers.
     *
     * @param object $stmt 
     * @param array $tableAliases 
     * @return mixed $result
     */
    public function hydrateResultSet($stmt, $tableAliases)
    {
        $hydrators = Doctrine_Manager::getInstance()->getHydrators();
        if ( ! isset($hydrators[$this->_hydrationMode])) {
            throw new Doctrine_Hydrator_Exception('Invalid hydration mode specified.');
        }

        $driverClass = $hydrators[$this->_hydrationMode];
        $driver = new $driverClass($this->_queryComponents, $tableAliases);

        $result = $driver->hydrateResultSet($stmt);

        return $result;
    }
}