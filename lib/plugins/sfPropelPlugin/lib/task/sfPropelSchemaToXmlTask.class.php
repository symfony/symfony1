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
 * Creates schema.xml from schema.yml.
 *
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelSchemaToXmlTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('propel-convert-yml-schema');
    $this->namespace = 'propel';
    $this->name = 'schema-to-xml';
    $this->briefDescription = 'Creates schema.xml from schema.yml';

    $this->detailedDescription = <<<EOF
The [propel:schema-to-xml|INFO] task converts XML schemas to YML:

  [./symfony propel:convert-to-xml|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->schemaToXML(self::CHECK_SCHEMA);
  }
}
