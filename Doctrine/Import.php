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
 * class Doctrine_Import
 * Main responsible of performing import operation. Delegates database schema
 * reading to a reader object and passes the result to a builder object which
 * builds a Doctrine data model.
 *
 * @package     Doctrine
 * @subpackage  Import
 * @link        www.phpdoctrine.org
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @author      Jukka Hassinen <Jukka.Hassinen@BrainAlliance.com>
 */
class Doctrine_Import extends Doctrine_Connection_Module
{
    protected $sql = array();

    /**
     * lists all databases
     *
     * @return array
     */
    public function listDatabases()
    {
        if ( ! isset($this->sql['listDatabases'])) {
            throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
        }

        return $this->conn->fetchColumn($this->sql['listDatabases']);
    }

    /**
     * lists all availible database functions
     *
     * @return array
     */
    public function listFunctions()
    {
        if ( ! isset($this->sql['listFunctions'])) {
            throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
        }

        return $this->conn->fetchColumn($this->sql['listFunctions']);
    }

    /**
     * lists all database triggers
     *
     * @param string|null $database
     * @return array
     */
    public function listTriggers($database = null)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists all database sequences
     *
     * @param string|null $database
     * @return array
     */
    public function listSequences($database = null)
    {
        if ( ! isset($this->sql['listSequences'])) {
            throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
        }

        return $this->conn->fetchColumn($this->sql['listSequences']);
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableConstraints($table)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableColumns($table)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists table constraints
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableIndexes($table)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists tables
     *
     * @param string|null $database
     * @return array
     */
    public function listTables($database = null)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists table triggers
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableTriggers($table)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists table views
     *
     * @param string $table     database table name
     * @return array
     */
    public function listTableViews($table)
    {
        throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
    }

    /**
     * lists database users
     *
     * @return array
     */
    public function listUsers()
    {
        if ( ! isset($this->sql['listUsers'])) {
            throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
        }

        return $this->conn->fetchColumn($this->sql['listUsers']);
    }

    /**
     * lists database views
     *
     * @param string|null $database
     * @return array
     */
    public function listViews($database = null)
    {
        if ( ! isset($this->sql['listViews'])) {
            throw new Doctrine_Import_Exception(__FUNCTION__ . ' not supported by this driver.');
        }

        return $this->conn->fetchColumn($this->sql['listViews']);
    }

    /**
     * checks if a database exists
     *
     * @param string $database
     * @return boolean
     */
    public function databaseExists($database)
    {
        return in_array($database, $this->listDatabases());
    }

    /**
     * checks if a function exists
     *
     * @param string $function
     * @return boolean
     */
    public function functionExists($function)
    {
        return in_array($function, $this->listFunctions());
    }

    /**
     * checks if a trigger exists
     *
     * @param string $trigger
     * @param string|null $database
     * @return boolean
     */
    public function triggerExists($trigger, $database = null)
    {
        return in_array($trigger, $this->listTriggers($database));
    }

    /**
     * checks if a sequence exists
     *
     * @param string $sequence
     * @param string|null $database
     * @return boolean
     */
    public function sequenceExists($sequence, $database = null)
    {
        return in_array($sequence, $this->listSequences($database));
    }

    /**
     * checks if a table constraint exists
     *
     * @param string $constraint
     * @param string $table     database table name
     * @return boolean
     */
    public function tableConstraintExists($constraint, $table)
    {
        return in_array($constraint, $this->listTableConstraints($table));
    }

    /**
     * checks if a table column exists
     *
     * @param string $column
     * @param string $table     database table name
     * @return boolean
     */
    public function tableColumnExists($column, $table)
    {
        return in_array($column, $this->listTableColumns($table));
    }

    /**
     * checks if a table index exists
     *
     * @param string $index
     * @param string $table     database table name
     * @return boolean
     */
    public function tableIndexExists($index, $table)
    {
        return in_array($index, $this->listTableIndexes($table));
    }

    /**
     * checks if a table exists
     *
     * @param string $table
     * @param string|null $database
     * @return boolean
     */
    public function tableExists($table, $database = null)
    {
        return in_array($table, $this->listTables($database));
    }

    /**
     * checks if a table trigger exists
     *
     * @param string $trigger
     * @param string $table     database table name
     * @return boolean
     */
    public function tableTriggerExists($trigger, $table)
    {
        return in_array($trigger, $this->listTableTriggers($table));
    }

    /**
     * checks if a table view exists
     *
     * @param string $view
     * @param string $table     database table name
     * @return boolean
     */
    public function tableViewExists($view, $table)
    {
        return in_array($view, $this->listTableViews($table));
    }

    /**
     * checks if a user exists
     *
     * @param string $user
     * @return boolean
     */
    public function userExists($user)
    {
        return in_array($user, $this->listUsers());
    }

    /**
     * checks if a view exists
     *
     * @param string $view
     * @param string|null $database
     * @return boolean
     */
    public function viewExists($view, $database = null)
    {
         return in_array($view, $this->listViews($database));
    }

    /**
     * importSchema
     *
     * method for importing existing schema to Doctrine_Record classes
     *
     * @param string $directory
     * @param array $databases
     * @return array                the names of the imported classes
     */
    public function importSchema($directory, array $databases = array(), array $options = array())
    {
        $options['singularize'] = ! isset($options['singularize']) ? 
                $this->conn->getAttribute('singularize_import'):$options['singularize'];

        $connections = Doctrine_Manager::getInstance()->getConnections();

        foreach ($connections as $name => $connection) {
          // Limit the databases to the ones specified by $databases.
          // Check only happens if array is not empty
          if ( ! empty($databases) && ! in_array($name, $databases)) {
            continue;
          }

          $builder = new Doctrine_Import_Builder();
          $builder->setTargetPath($directory);
          $builder->setOptions($options);

          $classes = array();
          foreach ($connection->import->listTables() as $table) {
              $definition = array();
              $definition['tableName'] = $table;

              if( ! isset($options['singularize']) || $options['singularize'] !== false) {
                  $e = explode('_', Doctrine_Inflector::tableize($table));
                  foreach ($e as $k => $v) {
                      $e[$k] = Doctrine_Inflector::singularize($v);
                  }
                  $classTable = implode('_', $e);
              } else {
                  $classTable = Doctrine_Inflector::tableize($table);
              }

              $definition['className'] = Doctrine_Inflector::classify($classTable);

              $definition['columns'] = $connection->import->listTableColumns($table);

              $builder->buildRecord($definition);

              $classes[] = $definition['className'];
          }
        }

        return $classes;
    }
}