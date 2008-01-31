<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfPropelBaseTask.class.php');

/**
 * Create form classes for the current model.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildFormsTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'propel';
    $this->name = 'build-forms';
    $this->briefDescription = 'Creates form classes for the current model';

    $this->detailedDescription = <<<EOF
The [propel:build-forms|INFO] task creates form classes from the schema:

  [./symfony propel:build-forms|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

The task use the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [--connection|COMMENT] option:

  [./symfony propel:build-forms --connection="name"|INFO]

The model form classes files are created in [lib/form|COMMENT].

This task never overrides custom classes in [lib/form|COMMENT].
It only replaces base classes generated in [lib/form/base|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('propel', 'generating form classes'))));

    $generatorManager = new sfGeneratorManager();

    $generatorManager->generate('sfPropelFormGenerator', array('connection' => $options['connection']));
  }
}
