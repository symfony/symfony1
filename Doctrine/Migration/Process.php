<?php
/*
 *  $Id: Process.php 1080 2007-02-10 18:17:08Z jwage $
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
 * Doctrine_Migration_Process
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Migration_Process
{
    protected $_migration;

    public function __construct($migration)
    {
        $this->_migration = $migration;
    }

    /**
     * Get the connection for specified table name
     *
     * @param string $tableName 
     * @return Doctrine_Connection $conn
     */
    public function getConnection($tableName)
    {
        return Doctrine::getConnectionByTableName($tableName);
    }

    /**
     * Process the create table changes
     *
     * @param array $tables
     * @return void
     */
    public function processCreatedTables($tables)
    {
        foreach ($tables as $table) {
            try {
                $conn = $this->getConnection($table['tableName']);
                $conn->export->createTable($table['tableName'], $table['fields'], $table['options']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the drop table changes
     *
     * @param array $tables
     * @return void
     */
    public function processDroppedTables($tables)
    {
        foreach ($tables as $table) {
            try {
                $conn = $this->getConnection($table['tableName']);
                $conn->export->dropTable($table['tableName']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the rename table changes
     *
     * @param array $tables
     * @return void
     */
    public function processRenamedTables($tables)
    {
        foreach ($tables as $table) {
            try {
                $conn = $this->getConnection($table['newTableName']);
                $conn->export->alterTable($table['oldTableName'], array('name' => $table['newTableName']));
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the add column changes
     *
     * @param array $columns
     * @return void
     */
    public function processAddedColumns($columns)
    {
        foreach ($columns as $column) {
            try {
                $conn = $this->getConnection($column['tableName']);
                $conn->export->alterTable($column['tableName'], array('add' => array($column['columnName'] => $column)));
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the remove column changes
     *
     * @param array $columns
     * @return void
     */
    public function processRemovedColumns($columns)
    {
        foreach ($columns as $column) {
            try {
                $conn = $this->getConnection($column['tableName']);
                $conn->export->alterTable($column['tableName'], array('remove' => array($column['columnName'] => array())));
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the rename column changes
     *
     * @param array $columns
     * @return void
     */
    public function processRenamedColumns($columns)
    {
        foreach ($columns as $column) {
            try {
                $conn = $this->getConnection($column['tableName']);
                $columnList = $conn->import->listTableColumns($column['tableName']);
                if (isset($columnList[$column['oldColumnName']])) {
    	            $conn->export->alterTable($column['tableName'], 
    	                                      array('rename' => array($column['oldColumnName'] => array('name' => $column['newColumnName'],
    	                                      																													'definition'=>$columnList[$column['oldColumnName']]))));
                }
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the change column changes
     *
     * @param array $columns
     * @return void
     */
    public function processChangedColumns($columns)
    {
        foreach ($columns as $column) {
            try {
                $conn = $this->getConnection($column['tableName']);
            
                $options = array();
                $options = $column['options'];
                $options['type'] = $column['type'];
            
                $conn->export->alterTable($column['tableName'], array('change' => array($column['columnName'] => array('definition' => $options))));
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the add index changes
     *
     * @param array $indexes
     * @return void
     */
    public function processAddedIndexes($indexes)
    {
        foreach ($indexes as $index) {
            try {
                $conn = $this->getConnection($index['tableName']);
                $conn->export->createIndex($index['tableName'], $index['indexName'], $index['definition']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the remove index changes
     *
     * @param array $indexes
     * @return void
     */
    public function processRemovedIndexes($indexes)
    {
        foreach ($indexes as $index) {
            try {
                $conn = $this->getConnection($index['tableName']);
                $conn->export->dropIndex($index['tableName'], $index['indexName']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the create constraint changes
     *
     * @param array $constraints
     * @return void
     */
    public function processCreatedConstraints($constraints)
    {
        foreach ($constraints as $constraint) {
            try {
                $conn = $this->getConnection($constraint['tableName']);
                $conn->export->createConstraint($constraint['tableName'], $constraint['constraintName'],
                        $constraint['definition']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the drop constraint changes
     *
     * @param array $constraints
     * @return void
     */
    public function processDroppedConstraints($constraints)
    {
        foreach ($constraints as $constraint) {
            try {
                $conn = $this->getConnection($constraint['tableName']);
                $conn->export->dropConstraint($constraint['tableName'], $constraint['constraintName'],
                        $constraint['primary']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the create foreign key changes
     *
     * @param array $foreignKeys
     * @return void
     */
    public function processCreatedFks($foreignKeys)
    {
        foreach ($foreignKeys as $fk) {
            try {
                $conn = $this->getConnection($fk['tableName']);
                $conn->export->createForeignKey($fk['tableName'], $fk['definition']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }

    /**
     * Process the drop foreign key changes
     *
     * @param array $foreignKeys
     * @return void
     */
    public function processDroppedFks($foreignKeys)
    {
        foreach ($foreignKeys as $fk) {
            try {
                $conn = $this->getConnection($fk['tableName']);
                $conn->export->dropForeignKey($fk['tableName'], $fk['definition']['name']);
            } catch (Exception $e) {
                $this->_migration->addError($e);
            }
        }
    }
}