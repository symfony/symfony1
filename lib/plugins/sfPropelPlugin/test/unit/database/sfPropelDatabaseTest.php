<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
set_include_path(sfConfig::get('sf_symfony_lib_dir').'/plugins/sfPropelPlugin/lib/vendor'.PATH_SEPARATOR.get_include_path());

$t = new lime_test(4, new lime_output_color());

$p = new sfPropelDatabase();

$configuration = array(
  'propel' => array(
    'datasources' => array(
      'propel' => array(
        'adapter' => 'mysql',
        'connection' => array(
          'phptype'             => 'mysql',
          'hostspec'            => 'localhost',
          'database'            => 'testdb',
          'username'            => 'foo',
          'password'            => 'bar',
          'port'                => null,
          'encoding'            => 'utf8',
          'persistent'          => '1',
          'protocol'            => null,
          'socket'              => null,
          'compat_assoc_lower'  => null,
          'compat_rtrim_string' => null,
        ),
      ),
      'default' => 'propel',
    ),
  ),
);

$parametersTests = array(
  array(
    'dsn'        => 'mysql://foo:bar@localhost/testdb?encoding=utf8&persistent=1',
  ),
  array(
    'dsn'        => 'mysql://foo:bar@localhost/testdb',
    'encoding'   => 'utf8',
    'persistent' => 1,
  ),
  array(
    'phptype'    => 'mysql',
    'database'   => 'testdb',
    'encoding'   => 'utf8',
    'host'       => 'localhost',
    'username'   => 'foo',
    'password'   => 'bar',
    'persistent' => 1,
  ),
  array(
    'phptype'    => 'mysql',
    'database'   => 'testdb',
    'encoding'   => 'utf8',
    'hostspec'   => 'localhost',
    'username'   => 'foo',
    'password'   => 'bar',
    'persistent' => 1,
  ),
);

foreach ($parametersTests as $parameters)
{
  $p->initialize($parameters);
  $t->is($p->getConfiguration(), $configuration);
}
