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
 * Creates a schema.xml from an existing database.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildSchemaTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_NONE, 'Creates an XML schema instead of a YML one'),
    ));

    $this->aliases = array('propel-build-schema');
    $this->namespace = 'propel';
    $this->name = 'build-schema';
    $this->briefDescription = 'Creates a schema.xml from an existing database';

    $this->detailedDescription = <<<EOF
The [propel:build-schema|INFO] task introspects a database to create a schema:

  [./symfony propel:build-schema|INFO]

By default, the task creates a YML file, but you can also create a XML file:

  [./symfony --xml propel:build-schema|INFO]

The XML format contains more information than the YML one.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callPhing('creole', self::DO_NOT_CHECK_SCHEMA);

    // Fix database name
    if (file_exists(sfConfig::get('sf_config_dir').'/schema.xml'))
    {
      $schema = file_get_contents(sfConfig::get('sf_config_dir').'/schema.xml');
      $schema = preg_replace('/<database\s+name="[^"]+"/s', '<database name="propel"', $schema);
      file_put_contents(sfConfig::get('sf_config_dir').'/schema.xml', $schema);
    }

    if (!isset($options['xml']))
    {
      $this->schemaToYML(self::DO_NOT_CHECK_SCHEMA, '');
      $this->cleanup();
    }
  }
}
