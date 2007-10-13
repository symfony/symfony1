<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Create classes for the current model.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildModelTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('propel-build-model');
    $this->namespace = 'propel';
    $this->name = 'build-model';
    $this->briefDescription = 'Creates classes for the current model';

    $this->detailedDescription = <<<EOF
The [propel:build-model|INFO] task creates model classes from the schema:

  [./symfony propel:build-model|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

You mix and match YML and XML schema files. The task will convert
YML ones to XML before calling the Propel task.

The model classes files are created in [lib/model|COMMENT].

This task never overrides custom classes in [lib/model|COMMENT].
It only replaces files in [lib/model/om|COMMENT] and [lib/model/map|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->schemaToXML(self::DO_NOT_CHECK_SCHEMA, 'generated-');
    $this->copyXmlSchemaFromPlugins('generated-');
    $this->callPhing('om', self::CHECK_SCHEMA);
    $this->cleanup();

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('autoload', 'reloading autoloading'))));

    sfSimpleAutoload::getInstance()->reload();
  }
}
