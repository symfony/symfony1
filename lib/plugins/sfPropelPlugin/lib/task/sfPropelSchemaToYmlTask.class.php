<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates schema.yml from schema.xml.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelSchemaToYmlTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('propel-convert-xml-schema');
    $this->namespace = 'propel';
    $this->name = 'schema-to-yml';
    $this->briefDescription = 'Creates schema.yml from schema.xml';

    $this->detailedDescription = <<<EOF
The [propel:schema-to-yml|INFO] task converts YML schemas to XML:

  [./symfony propel:convert-to-yml|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->schemaToYML(self::CHECK_SCHEMA);
  }
}
