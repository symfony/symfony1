<?php
/*
 *  $Id: Migration.php 1080 2007-02-10 18:17:08Z jwage $
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
 * Doctrine_Migration
 *
 * this class represents a database view
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Migration
{
    protected $_migrationTableName = 'migration_version',
              $_migrationClassesDirectory = array(),
              $_migrationClasses = array(),
              $_reflectionClass;

    /**
     * Specify the path to the directory with the migration classes.
     * The classes will be loaded and the migration table will be created if it does not already exist
     *
     * @param string $directory
     * @return void
     */
    public function __construct($directory = null)
    {
        $this->_reflectionClass = new ReflectionClass('Doctrine_Migration_Base');

        if ($directory != null) {
            $this->_migrationClassesDirectory = $directory;

            $this->loadMigrationClassesFromDirectory();

            $this->_createMigrationTable();
        }
    }

    /**
     * Get the table name for storing the version number for this migration instance
     *
     * @return string $migrationTableName
     */
    public function getTableName()
    {
        return $this->_migrationTableName;
    }

    /**
     * Set the table name for storing the version number for this migration instance
     *
     * @param string $tableName
     * @return void
     */
    public function setTableName($tableName)
    {
        $this->_migrationTableName = Doctrine_Manager::connection()
                ->formatter->getTableName($tableName);
    }

    /**
     * Load migration classes from the passed directory. Any file found with a .php
     * extension will be passed to the loadMigrationClass()
     *
     * @param string $directory  Directory to load migration classes from
     * @return void
     */
    public function loadMigrationClassesFromDirectory($directory = null)
    {
        $classes = get_declared_classes();
        $directory = $directory ? $directory:$this->_migrationClassesDirectory;
        foreach ((array) $directory as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($it as $file) {
                $info = pathinfo($file->getFileName());
                if (isset($info['extension']) && $info['extension'] == 'php') {
                    require_once($file->getPathName());

                    $array = array_diff(get_declared_classes(), $classes);
                    $className = end($array);

                    if ($className) {
                        $this->loadMigrationClass($className);
                    }
                }
            }
        }
    }

    /**
     * Load the specified migration class name in to this migration instances queue of
     * migration classes to execute. It must be a child of Doctrine_Migration in order
     * to be loaded.
     *
     * @param string $name
     * @return void
     */
    public function loadMigrationClass($name)
    {
        $class = new ReflectionClass($name);

        while ($class->isSubclassOf($this->_reflectionClass)) {

            $class = $class->getParentClass();
            if ($class === false) {
                break;
            }
        }

        if ($class === false) {
            return false;
        }

        if (empty($this->_migrationClasses)) {
            $classMigrationNum = 1;
        } else {
            $nums = array_keys($this->_migrationClasses);
            $num = end($nums);
            $classMigrationNum = $num + 1;
        }
        $this->_migrationClasses[$classMigrationNum] = $name;
    }

    /**
     * Get all the loaded migration classes. Array where key is the number/version
     * and the value is the class name.
     *
     * @return array $migrationClasses
     */
    public function getMigrationClasses()
    {
        return $this->_migrationClasses;
    }

    /**
     * Set the current version of the database
     *
     * @param integer $number
     * @return void
     */
    public function setCurrentVersion($number)
    {
        $conn = Doctrine_Manager::connection();

        if ($this->hasMigrated()) {
            $conn->exec("UPDATE " . $this->_migrationTableName . " SET version = $number");
        } else {
            $conn->exec("INSERT INTO " . $this->_migrationTableName . " (version) VALUES ($number)");
        }
    }

    /**
     * Get the current version of the database
     *
     * @return integer $version
     */
    public function getCurrentVersion()
    {
        $conn = Doctrine_Manager::connection();

        $result = $conn->fetchColumn("SELECT version FROM " . $this->_migrationTableName);

        return isset($result[0]) ? $result[0]:0;
    }

    /**
     * hReturns true/false for whether or not this database has been migrated in the past
     *
     * @return boolean $migrated
     */
    public function hasMigrated()
    {
        $conn = Doctrine_Manager::connection();

        $result = $conn->fetchColumn("SELECT version FROM " . $this->_migrationTableName);

        return isset($result[0]) ? true:false;
    }

    /**
     * Gets the latest possible version from the loaded migration classes
     *
     * @return integer $latestVersion
     */
    public function getLatestVersion()
    {
        $versions = array_keys($this->_migrationClasses);
        rsort($versions);

        return isset($versions[0]) ? $versions[0]:0;
    }

    /**
     * Get the next incremented version number based on the latest version number
     * using getLatestVersion()
     *
     * @return integer $nextVersion
     */
    public function getNextVersion()
    {
        return $this->getLatestVersion() + 1;
    }

    /**
     * Get the next incremented class version based on the loaded migration classes
     *
     * @return integer $nextMigrationClassVersion
     */
    public function getNextMigrationClassVersion()
    {
        if (empty($this->_migrationClasses)) {
            return 1;
        } else {
            $nums = array_keys($this->_migrationClasses);
            $num = end($nums) + 1;
            return $num;
        }
    }

    /**
     * Perform a migration process by specifying the migration number/version to
     * migrate to. It will automatically know whether you are migrating up or down
     * based on the current version of the database.
     *
     * @param  string $to Version to migrate to
     * @return string $to The version migrated to
     * @throws Doctrine_Migration_Exception $e When you try and migrate to the current version.
     */
    public function migrate($to = null)
    {
        $from = $this->getCurrentVersion();

        // If nothing specified then lets assume we are migrating from the current version to the latest version
        if ($to === null) {
            $to = $this->getLatestVersion();
        }

        if ($from == $to) {
            throw new Doctrine_Migration_Exception('Already at version # ' . $to);
        }

        $direction = $from > $to ? 'down':'up';

        if ($direction === 'up') {
            for ($i = $from + 1; $i <= $to; $i++) {
                $this->_doMigrateStep($direction, $i);
            }
        } else {
            for ($i = $from; $i > $to; $i--) {
                $this->_doMigrateStep($direction, $i);
            }
        }

        $this->setCurrentVersion($to);

        return $to;
    }

    /**
     * Get instance of migration class for number/version specified
     *
     * @param integer $num
     * @return return Doctrine_Migration_Base $class
     */
    public function getMigrationClass($num)
    {
        if (isset($this->_migrationClasses[$num])) {
            $className = $this->_migrationClasses[$num];
            return new $className();
        }

        throw new Doctrine_Migration_Exception('Could not find migration class for migration step: '.$num);
    }

    /**
     * Perform a single migration step. Executes a single migration class and
     * processes the changes
     *
     * @param string $direction Direction to go, 'up' or 'down'
     * @param integer $num
     * @return void
     */
    protected function _doMigrateStep($direction, $num)
    {
        $migration = $this->getMigrationClass($num);

        $method = 'pre' . $direction;
        $migration->$method();

        if (method_exists($migration, $direction)) {
            $migration->$direction();
        } else if (method_exists($migration, 'migrate')) {
            $migration->migrate($direction);
        }

        $changes = $migration->getChanges();
        if ( ! empty($changes)) {
            foreach ($changes as $type => $changes) {
                $process = new Doctrine_Migration_Process();
                $funcName = 'process' . Doctrine_Inflector::classify($type);

                if ( ! empty($changes)) {
                    $process->$funcName($changes);
                }
            }
        }

        $method = 'post' . $direction;
        $migration->$method();
    }

    /**
     * Create the migration table and return true. If it already exists it will
     * silence the exception and return false
     *
     * @return boolean $created Whether or not the table was created. Exceptions
     *                          are silenced when table already exists
     */
    protected function _createMigrationTable()
    {
        $conn = Doctrine_Manager::connection();

        try {
            $conn->export->createTable($this->_migrationTableName, array('version' => array('type' => 'integer', 'size' => 11)));

            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}