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
 * Doctrine_Adapter_Mock
 *
 * This class is used for special testing purposes.
 *
 * @package     Doctrine
 * @subpackage  Adapter
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Adapter_Mock implements Doctrine_Adapter_Interface, Countable
{
    private $_name;

    private $_queries = array();

    private $_exception = array();

    private $_lastInsertIdFail = false;

    public function __construct($name = null)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function pop()
    {
        return array_pop($this->_queries);
    }

    public function forceException($name, $message = '', $code = 0)
    {
        $this->_exception = array($name, $message, $code);
    }

    public function prepare($query)
    {
        $mock = new Doctrine_Adapter_Statement_Mock($this, $query);
        $mock->queryString = $query;

        return $mock;
    }

    public function addQuery($query)
    {
        $this->_queries[] = $query;
    }

    public function query($query)
    {
        $this->_queries[] = $query;

        $e    = $this->_exception;

        if ( ! empty($e)) {
            $name = $e[0];

            $this->_exception = array();

            throw new $name($e[1], $e[2]);
        }

        $stmt = new Doctrine_Adapter_Statement_Mock($this, $query);
        $stmt->queryString = $query;

        return $stmt;
    }

    public function getAll()
    {
        return $this->_queries;
    }

    public function quote($input)
    {
        return "'" . addslashes($input) . "'";
    }

    public function exec($statement)
    {
        $this->_queries[] = $statement;

        $e    = $this->_exception;

        if ( ! empty($e)) {
            $name = $e[0];

            $this->_exception = array();

            throw new $name($e[1], $e[2]);
        }

        return 0;
    }

    public function forceLastInsertIdFail($fail = true)
    {
        if ($fail) {
            $this->_lastInsertIdFail = true;
        } else {
            $this->_lastInsertIdFail = false;
        }
    }

    public function lastInsertId()
    {
        $this->_queries[] = 'LAST_INSERT_ID()';
        if ($this->_lastInsertIdFail) {
            return null;
        } else {
            return 1;
        }
    }

    public function count()
    {
        return count($this->_queries);
    }

    public function beginTransaction()
    {
        $this->_queries[] = 'BEGIN TRANSACTION';
    }

    public function commit()
    {
        $this->_queries[] = 'COMMIT';
    }

    public function rollBack()
    {
        $this->_queries[] = 'ROLLBACK';
    }

    public function errorCode()
    { }

    public function errorInfo()
    { }

    public function getAttribute($attribute)
    {
        if ($attribute == Doctrine::ATTR_DRIVER_NAME) {
            return strtolower($this->_name);
        }
    }

    public function setAttribute($attribute, $value)
    { }

    public function sqliteCreateFunction()
    { }
}