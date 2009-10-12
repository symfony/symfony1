<?php
/*
 *  $Id: Cli.php 2761 2007-10-07 23:42:29Z zYne $
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
 * Command line interface class
 * Interface for easily executing Doctrine_Task classes from a 
 * command line interface
 *
 * @package     Doctrine
 * @subpackage  Cli
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 2761 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Cli
{
    /**
     * The name of the Doctrine Task base class
     * 
     * @var string
     */
    const TASK_BASE_CLASS = 'Doctrine_Task';

    protected
        $_taskInstance = null,
        $_formatter    = null,
        $_scriptName   = null,
        $_message      = null,
        $_config       = array();


    /**
     * An array containing the names of loaded tasks in the form "<class name> => <task name>"
     * 
     * @var array
     */
    private $_registeredTask = array();

    /**
     * __construct
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->_config = $config;
        $this->_formatter = new Doctrine_Cli_AnsiColorFormatter();

        $this->loadTasks();
    }


    /**
     * @param array $_registeredTask
     */
    public function setRegisteredTasks(array $_registeredTask)
    {
        $this->_registeredTask = $_registeredTask;
    }

    /**
     * @return array
     */
    public function getRegisteredTasks()
    {
        return $this->_registeredTask;
    }

    /**
     * Returns TRUE if the specified task is registered with the CLI, or FALSE otherwise
     * 
     * If the task is registered, $className is set with the name of the implementing class
     * 
     * @param string $taskName
     * @param string [&$className=null]
     * @return bool
     */
    public function taskIsRegistered($taskName, &$className = null)
    {
        if (($key = array_search($taskName, $this->getRegisteredTasks())) !== false)
        {
            $className = $key;
            return true;
        }

        return false;
    }

    /**
     * Notify the formatter of a message
     *
     * @param string $notification  The notification message
     * @param string $style         Style to format the notification with(INFO, ERROR)
     * @return void
     */
    public function notify($notification = null, $style = 'HEADER')
    {
        echo $this->_formatter->format($this->_taskInstance->getTaskName(), 'INFO') .
            ' - ' . $this->_formatter->format($notification, $style) . "\n";
    }

    /**
     * Notify the formatter of an exception
     *
     * @param  Exception $exception
     * @return void
     * @throws Doctrine_Cli_Exception
     */
    public function notifyException($exception)
    {
        $msg = $exception->getMessage();
        if (Doctrine_Core::debug()) {
            $msg .= "\n" . $exception->getTraceAsString();
        }
        echo $this->_formatter->format($msg, 'ERROR') . "\n";
    }

    /**
     * Public function to run the loaded task with the passed arguments
     *
     * @param  array $args
     * @return void
     * @throws Doctrine_Cli_Exception
     */
    public function run(array $args)
    {
        try {
            $this->_run($args);
        } catch (Exception $exception) {
            $this->notifyException($exception);
        }
    }

    /**
     * Creates, and returns, a new instance of the specified task class
     * 
     * @param string $className
     * @param object $oCli Doctrine_Cli
     * @param string $taskName
     * @return object Doctrine_Task
     */
    protected function createTaskInstance($className, Doctrine_Cli $oCli, $taskName = null)
    {
        $oTask = new $className($oCli);

        if ( ! is_null($taskName))
        {
            $oTask->taskName = $taskName;
        }

        return $oTask;
    }

    /**
     * Run the actual task execution with the passed arguments
     *
     * @param  array $args Array of arguments for this task being executed
     * @return void
     * @throws Doctrine_Cli_Exception $e
     */
    protected function _run(array $args)
    {        
        $this->_scriptName = $args[0];
        
        $taskName = isset($args[1]) ? $args[1] : null;
        
        if ( ! $taskName || $taskName == 'help') {
            echo $this->printTasks(null, $taskName == 'help' ? true : false);
            return;
        }
        
        if ($taskName && isset($args[2]) && $args[2] === 'help') {
            echo $this->printTasks($taskName, true);
            return;
        }
        
        $taskIsRegistered = $this->taskIsRegistered($taskName, $taskClass);

        if ( ! $taskIsRegistered) {
            throw new Doctrine_Cli_Exception("The task \"{$taskName}\" has not been registered");
        }
        
        unset($args[0]);
        unset($args[1]);
        
        $this->_taskInstance = $this->createTaskInstance($taskClass, $this);
        $this->_taskInstance->setArguments($this->prepareArgs($args));

        if ($this->_taskInstance->validate()) {
            $this->_taskInstance->execute();
        } else {
            echo $this->_formatter->format('Required arguments missing!', 'ERROR') . "\n\n";
            echo $this->printTasks($taskName, true);
        }
    }

    /**
     * Prepare the raw arguments for execution. Combines with the required and optional argument
     * list in order to determine a complete array of arguments for the task
     *
     * @param  array $args      Array of raw arguments
     * @return array $prepared  Array of prepared arguments
     */
    protected function prepareArgs(array $args)
    {
        $taskInstance = $this->_taskInstance;
        
        $args = array_values($args);
        
        // First lets load populate an array with all the possible arguments. required and optional
        $prepared = array();
        
        $requiredArguments = $taskInstance->getRequiredArguments();
        foreach ($requiredArguments as $key => $arg) {
            $prepared[$arg] = null;
        }
        
        $optionalArguments = $taskInstance->getOptionalArguments();
        foreach ($optionalArguments as $key => $arg) {
            $prepared[$arg] = null;
        }
        
        // If we have a config array then lets try and fill some of the arguments with the config values
        if (is_array($this->_config) && !empty($this->_config)) {
            foreach ($this->_config as $key => $value) {
                if (array_key_exists($key, $prepared)) {
                    $prepared[$key] = $value;
                }
            }
        }
        
        // Now lets fill in the entered arguments to the prepared array
        $copy = $args;
        foreach ($prepared as $key => $value) {
            if ( ! $value && !empty($copy)) {
                $prepared[$key] = $copy[0];
                unset($copy[0]);
                $copy = array_values($copy);
            }
        }
        
        return $prepared;
    }

    /**
     * Prints an index of all the available tasks in the CLI instance
     * 
     * @return void
     */
    public function printTasks($task = null, $full = false)
    {
        echo $this->_formatter->format("Doctrine Command Line Interface", 'HEADER') . "\n\n";
        
        foreach ($this->getRegisteredTasks() as $taskClass => $taskName)
        {
            if ($task && (strtolower($task) != strtolower($taskName))) {
                continue;
            }

            $taskInstance = $this->createTaskInstance($taskClass, $this, $taskName);

            $syntax = $this->_scriptName . ' ' . $taskInstance->getTaskName();
            
            echo $this->_formatter->format($syntax, 'INFO');

            if ($full)
            {
                $args = '';
                $args = null;
                
                $requiredArguments = $taskInstance->getRequiredArgumentsDescriptions();
                
                if ( ! empty($requiredArguments)) {
                    foreach ($requiredArguments as $name => $description) {
                        $args .= $this->_formatter->format($name, "ERROR");
                        
                        if (isset($this->_config[$name])) {
                            $args .= " - " . $this->_formatter->format($this->_config[$name], 'COMMENT');
                        } else {
                            $args .= " - " . $description;
                        }
                        
                        $args .= "\n";
                    }
                }
            
                $optionalArguments = $taskInstance->getOptionalArgumentsDescriptions();
                
                if ( ! empty($optionalArguments)) {
                    foreach ($optionalArguments as $name => $description) {
                        $args .= $name . ' - ' . $description."\n";
                    }
                }
                echo " - " . $taskInstance->getDescription() . "\n";
                if ($args) {
                    echo "\n" . $this->_formatter->format('Arguments:', 'HEADER') . "\n" . $args;
                }
            }
            
            echo "\n";
        }
    }

    /**
     * Returns TRUE if the specified class is a Task, or FALSE otherwise
     * 
     * @param string $className
     * @return bool
     */
    public function classIsTask($className)
    {
        $reflectionClass = new ReflectionClass($className);
        return (bool) $reflectionClass->isSubClassOf(self::TASK_BASE_CLASS);
    }

    /**
     * Registers the specified _loaded_ task-class
     * 
     * @param string $className
     * @param string $taskName
     * @throws InvalidArgumentException If the class does not exist or the task-name is blank
     * @throws DomainException If the class is not a Doctrine Task
     */
    public function registerTask($className, $taskName)
    {
        if ( ! class_exists($className, false))
        {
            throw new InvalidArgumentException("The task class \"{$className}\" does not exist");
        }

        if ( ! $this->classIsTask($className))
        {
            throw new DomainException("The class \"{$className}\" is not a Doctrine Task");
        }

        if ( ! (is_string($taskName) && strlen($taskName)))
        {
            throw new InvalidArgumentException("The task-name is blank");
        }

        $this->_registeredTask[$className] = $taskName;
    }

    /**
     * Loads and registers the task-class in the specified file
     * 
     * @param string $fileName
     * @param string $className
     * @param string $taskName
     * @throws InvalidArgumentException If the file or class do not exist, or if the task-name is blank
     * @throws DomainException If the class is not a Doctrine Task
     */
    public function loadAndRegisterTask($fileName, $className, $taskName)
    {
        if ( ! is_file($fileName))
        {
            throw new InvalidArgumentException("The task file \"{$fileName}\" does not exist");
        }

        require_once($fileName);

        $this->registerTask($className, $taskName);
    }

    /**
     * Returns the name of a Task from its class name, or FALSE if the class-name does not follow the Doctrine Task
     * naming convention
     * 
     * @param string $className
     * @return string|bool
     */
    public function deriveDoctrineTaskName($className)
    {
        $prefix = self::TASK_BASE_CLASS . '_';

        if (strpos($className, $prefix) === 0)
        {
            $baseName = substr($className, strlen($prefix));
            return str_replace('_', '-', Doctrine_Inflector::tableize($baseName));
        }

        return false;
    }

    /**
     * Registers loaded task classes that look like built-in Tasks
     */
    protected function registerLoadedDoctrineTasks()
    {
        foreach ($this->getLoadedDoctrineTasks() as $className => $taskName) {
            $this->registerTask($className, $taskName);
        }
    }

    /**
     * Get the name of the task class based on the first argument
     * which is always the task name. Do some inflection to determine the class name
     *
     * @param  array $args       Array of arguments from the cli
     * @return string $taskClass Task class name
     * @todo This method is no longer used internally.  For this reason, it is suggested that it be deprecated.
     */
    protected function _getTaskClassFromArgs(array $args)
    {
        return self::TASK_BASE_CLASS . '_' . Doctrine_Inflector::classify(str_replace('-', '_', $args[1]));
    }

    /**
     * @param array $_registeredTask
     * @return array
     * @todo Used by Doctrine_Cli::loadTasks() and Doctrine_Cli::getLoadedTasks() to re-create their pre-refactoring
     * behaviour 
     */
    private function createOldStyleTaskList(array $_registeredTask)
    {
        $tasks = array();

        foreach ($_registeredTask as $className => $taskName) {
            $tasks[$taskName] = $taskName;
        }

        return $tasks;
    }

    /**
     * Get array of all the Doctrine_Task child classes that are loaded
     * 
     * @return array $tasks
     * @todo This so-called getter has side-effects so it is no longer used internally.  For this reason, it is
     * suggested that it be deprecated.
     */
    public function getLoadedTasks()
    {
        $this->registerLoadedDoctrineTasks();
        return $this->createOldStyleTaskList($this->getRegisteredTasks());
    }


    /**
     * Load tasks from the passed directory. If no directory is given it looks in the default
     * Doctrine/Task folder for the core tasks.
     *
     * @param  mixed $directory   Can be a string path or array of paths
     * @return array $loadedTasks Array of tasks loaded
     */
    public function loadTasks($directory = null)
    {
        if ($directory === null) {
            $directory = Doctrine_Core::getPath() . DIRECTORY_SEPARATOR . 'Doctrine' . DIRECTORY_SEPARATOR . 'Task';
        }
        
        if (is_dir($directory))
        {
            foreach ((array) $directory as $dir) {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($it as $file) {
                    $baseName = $file->getFileName();

                    /*
                     * Class-files must start with an uppercase letter.  This additional check will help prevent us
                     * accidentally running 'executable' scripts that may be mixed in with the class files.
                     */
                    $matched = (bool) preg_match('/^([A-Z].*?)\.php$/', $baseName, $match);

                    if ( ! ($matched && (strpos($baseName, '.inc') === false))) {
                        continue;
                    }

                    $className = self::TASK_BASE_CLASS . '_' . $match[1];

                    /*
                     * If the class doesn't exist, attempt to load it.  (We're assuming here that this is a class file
                     * named according to Doctrine's conventions.)
                     */
                    if ( ! class_exists($className)) {
                        require_once($file->getPathName());
                    }

                    /*
                     * So was the class loaded successfully?  (Is this a class file after all?)  If it was, and it's a
                     * task class, register the task.
                     */
                    if (class_exists($className, false) && $this->classIsTask($className)) {
                        $this->registerTask($className, $this->deriveDoctrineTaskName($className));
                    }
                }
            }
        }

        $this->registerLoadedDoctrineTasks();

        return $this->createOldStyleTaskList($this->getRegisteredTasks());
    }

    /**
     * Returns an array containing the names of loaded (but not necessarily _registered_) Task classes (i.e. all classes
     * extending Doctrine_Task)
     *
     * @return array
     */
    public function getLoadedTaskClasses()
    {
        $classNames = array();
        
        foreach (get_declared_classes() as $className) {
            if ($this->classIsTask($className)) {
                $classNames[] = $className;
            }
        }

        return $classNames;
    }

    /**
     * Get array of all the Doctrine_Task child classes that are loaded
     *
     * @return array $tasks
     */
    public function getLoadedDoctrineTasks()
    {
        $parent = new ReflectionClass('Doctrine_Task');
        $tasks = array();
        foreach ($this->getLoadedTaskClasses() as $className) {
            if (($taskName = $this->deriveDoctrineTaskName($className)) !== false) {
                $tasks[$className] = $taskName;
            }
        }
        
        return $tasks;
    }
}