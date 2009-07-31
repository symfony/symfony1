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
    protected
        $_migration,
        $_connection;

    public function __construct(Doctrine_Migration $migration)
    {
        $this->_migration = $migration;
        $this->_connection = $migration->getConnection();
    }

    /**
     * Process a created table change
     *
     * @param string $table Table definition
     * @return void
     */
    public function processCreatedTable(array $table)
    {
        $this->_connection->export->createTable($table['tableName'], $table['fields'], $table['options']);
    }

    /**
     * Process a dropped table change
     *
     * @param array $table Table definition
     * @return void
     */
    public function processDroppedTable(array $table)
    {
        $this->_connection->export->dropTable($table['tableName']);
    }

    /**
     * Process a renamed table change
     *
     * @param array $table Renamed table definition
     * @return void
     */
    public function processRenamedTable(array $table)
    {
        $this->_connection->export->alterTable($table['oldTableName'], array('name' => $table['newTableName']));
    }

    /**
     * Process a created column change
     *
     * @param array $column Column definition
     * @return void
     */
    public function processCreatedColumn(array $column)
    {
        $this->_connection->export->alterTable($column['tableName'], array('add' => array($column['columnName'] => $column)));
    }

    /**
     * Process a dropped column change
     *
     * @param array $column Column definition
     * @return void
     */
    public function processDroppedColumn(array $column)
    {
        $this->_connection->export->alterTable($column['tableName'], array('remove' => array($column['columnName'] => array())));
    }

    /**
     * Process a renamed column change
     *
     * @param array $column Column definition
     * @return void
     */
    public function processRenamedColumn(array $column)
    {
        $columnList = $this->_connection->import->listTableColumns($column['tableName']);
        if (isset($columnList[$column['oldColumnName']])) {
            $this->_connection->export->alterTable($column['tableName'], array('rename' => array($column['oldColumnName'] => array('name' => $column['newColumnName'], 'definition' => $columnList[$column['oldColumnName']]))));
        }
    }

    /**
     * Process a changed column change
     *
     * @param array $column Changed column definition
     * @return void
     */
    public function processChangedColumn(array $column)
    {
        $options = array();
        $options = $column['options'];
        $options['type'] = $column['type'];
    
        $this->_connection->export->alterTable($column['tableName'], array('change' => array($column['columnName'] => array('definition' => $options))));
    }

    /**
     * Process a created index change
     *
     * @param array $index Index definition
     * @return void
     */
    public function processCreatedIndex(array $index)
    {
        $this->_connection->export->createIndex($index['tableName'], $index['indexName'], $index['definition']);
    }

    /**
     * Process a dropped index change
     *
     * @param array $index Index definition
     * @return void
     */
    public function processDroppedIndex(array $index)
    {
        $this->_connection->export->dropIndex($index['tableName'], $index['indexName']);
    }

    /**
     * Process a created constraint change
     *
     * @param array $constraint Constraint definition
     * @return void
     */
    public function processCreatedConstraint(array $constraint)
    {
        $this->_connection->export->createConstraint($constraint['tableName'], $constraint['constraintName'], $constraint['definition']);
    }

    /**
     * Process a dropped constraint change
     *
     * @param array $constraint Constraint definition
     * @return void
     */
    public function processDroppedConstraint(array $constraint)
    {
        $this->_connection->export->dropConstraint($constraint['tableName'], $constraint['constraintName'], $constraint['primary']);
    }

    /**
     * Process a created foreign key change
     *
     * @param array $foreignKey Foreign key definition
     * @return void
     */
    public function processCreatedForeignKey(array $foreignKey)
    {
        $this->_connection->export->createForeignKey($foreignKey['tableName'], $foreignKey['definition']);
    }

    /**
     * Process a dropped foreign key change
     *
     * @param array $foreignKey
     * @return void
     */
    public function processDroppedForeignKey(array $foreignKey)
    {
        $this->_connection->export->dropForeignKey($foreignKey['tableName'], $foreignKey['definition']['name']);
    }
}