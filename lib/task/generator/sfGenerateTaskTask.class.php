<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates a task skeleton
 *
 * @package    symfony
 * @subpackage task
 * @author     Francois Zaninotto <francois.zaninotto@symfony-project.com>
 */
class sfGenerateTaskTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('task_name', sfCommandArgument::REQUIRED, 'The task name (can contain namespace)'),
    ));

    $this->addOptions(array(
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_OPTIONAL, 'The directory to create the task in', 'lib/task'),
      new sfCommandOption('use_database', 'db', sfCommandOption::PARAMETER_OPTIONAL, 'Whether the task needs model initialization to access database', 'true'),
      new sfCommandOption('brief_description', 'bd', sfCommandOption::PARAMETER_OPTIONAL, 'A brief task description (appears in task list)', ''),
    ));

    $this->namespace = 'generate';
    $this->name = 'task';
    $this->briefDescription = 'Creates a skeleton class for a new task';

    $this->detailedDescription = <<<EOF
The [generate:task|INFO] creates a new Task class based on the name passed as argument:
  [./symfony generate:task namespace:name|INFO]

The `fooBarTask.class.php` skeleton task is created under the `lib/task/` directory. Note that the namespace is optional.
If you want to create the file in another directory (relative to the project root folder), pass it in the [dir|INFO] option:
  [./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task|INFO]

If the task doesn't need database access, you can remove the database initialization code with the [use_database|INFO] option:
  [./symfony generate:task namespace:name --use_database=false|INFO]

You can also specify a description:
  [./symfony generate:task namespace:name --briefDescription='Does interesting things' --detailedDescription='Usage tutorial'|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $taskName = $arguments['task_name'];
    $taskNameComponents = split(':', $taskName);
    $namespace = isset($taskNameComponents[1]) ? $taskNameComponents[0] : '';
    $name = isset($taskNameComponents[1]) ? $taskNameComponents[1] : $taskNameComponents[0];
    $taskClassName = ($namespace ? $namespace.ucfirst($name) : $name) . 'Task';
    $briefDescription = $options['brief_description'];
    $detailedDescription = <<<HED
The [$taskName|INFO] task does things.
Call it with:

  [php symfony $taskName|INFO]
HED;
    
    if($options['use_database'] != 'true')
    {
      $content = <<<HED
<?php

class $taskClassName extends sfPropelBaseTask
{
  protected function configure()
  {
    \$this->namespace        = '$namespace';
    \$this->name             = '$name';
    \$this->briefDescription = '$briefDescription';
    \$this->detailedDescription = <<<EOF
$detailedDescription
EOF;
    \$this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    // add other arguments here
    \$this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    \$this->addOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel');
    // add other options here
  }

  protected function execute(\$arguments = array(), \$options = array())
  {
    // Database initialization
    \$databaseManager = new sfDatabaseManager(\$this->configuration);
    \$connection = Propel::getConnection(\$options['connection'] ? \$options['connection'] : '');
    // add code here

  }
}
HED;
    }
    else
    {
      $content = <<<HED
<?php

class $taskClassName extends sfBaseTask
{
  protected function configure()
  {
    \$this->namespace        = '$namespace';
    \$this->name             = '$name';
    \$this->briefDescription = '$briefDescription';
    \$this->detailedDescription = <<<EOF
$detailedDescription
EOF;
    // add arguments here, like the following:
    //\$this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    // add options here, like the following:
    //\$this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
  }

  protected function execute(\$arguments = array(), \$options = array())
  {
    // add code here
  }
}
HED;
    }

    // check that the task directory exists and that the task file doesn't exist
    if (!is_readable(sfConfig::get('sf_root_dir').'/'.$options['dir']))
    {
      $this->getFilesystem()->mkdirs(str_replace('/', DIRECTORY_SEPARATOR, $options['dir']));
    }

    $taskFile = sfConfig::get('sf_root_dir').'/'.$options['dir'].'/'.$taskClassName.'.class.php';
    if (is_readable($taskFile))
    {
      throw new sfCommandException(sprintf('A "%s" task already exists in "%s".', $taskName, $taskFile));
    }

    $this->logSection('task', sprintf('Creating "%s" task file', $taskFile));
    file_put_contents($taskFile, $content);
  }
}
