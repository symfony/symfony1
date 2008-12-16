<?php

function is_cli()
{
  return !isset($_SERVER['HTTP_HOST']);
}

/**
 * Checks a configuration.
 */
function check($boolean, $message, $help = '', $fatal = false)
{
  echo $boolean ? "  OK        " : sprintf("[[%s]] ", $fatal ? ' ERROR ' : 'WARNING');
  echo "$message\n";

  if (!$boolean)
  {
    echo "            *** $help ***\n";
    if ($fatal)
    {
      die("You must fix this problem before resuming the check.\n");
    }
  }
}

/**
 * Gets the php.ini path used by the current PHP interpretor.
 *
 * @return string the php.ini path
 */
function get_ini_path()
{
  if ($path = get_cfg_var('cfg_file_path'))
  {
    return $path;
  }

  return 'WARNING: not using a php.ini file';
}

if (!is_cli())
{
  echo '<html><body><pre>';
}

echo "********************************\n";
echo "*                              *\n";
echo "*  symfony requirements check  *\n";
echo "*                              *\n";
echo "********************************\n\n";

echo sprintf("php.ini used by PHP: %s\n\n", get_ini_path());

if (is_cli())
{
  echo "** WARNING **\n";
  echo "*  The PHP CLI can use a different php.ini file\n";
  echo "*  than the one used with your web server.\n";
  if ('\\' == DIRECTORY_SEPARATOR)
  {
    echo "*  (especially on the Windows platform)\n";
  }
  echo "*  If this is the case, please launch this\n";
  echo "*  utility from your web server.\n";
  echo "** WARNING **\n";
}

// mandatory
echo "\n** Mandatory requirements **\n\n";
check(version_compare(phpversion(), '5.2.4', '>='), 'requires PHP >= 5.2.4', 'Current version is '.phpversion(), true);
check(!ini_get('zend.ze1_compatibility_mode'), 'php.ini: requires zend.ze1_compatibility_mode set to off', sprintf('Set it to off in php.ini (%s)', get_ini_path()), true);

// warnings
echo "\n** Optional checks **\n\n";
check(class_exists('PDO'), 'PDO is installed', 'Install PDO (mandatory for Propel and Doctrine)', false);
if (class_exists('PDO'))
{
  $drivers = PDO::getAvailableDrivers();
  check(count($drivers), 'PDO has some drivers installed: '.implode(', ', $drivers), 'Install PDO drivers (mandatory for Propel and Doctrine)', false);
}
check(class_exists('DomDocument'), 'PHP-XML module installed', 'Install the php-xml module (required by Propel)', false);
check(class_exists('XSLTProcessor'), 'XSL module installed', 'Install the XSL module (required by Propel)', false);
check(function_exists('token_get_all'), 'can use token_get_all()', 'Install token_get_all() function (highly recommended)', false);
check(function_exists('mb_strlen'), 'can use mb_strlen()', 'Install mb_strlen() function', false);
check(function_exists('iconv'), 'can use iconv()', 'Install iconv() function', false);
check(function_exists('utf8_decode'), 'can use utf8_decode()', 'Install utf8_decode() function', false);

$accelerator = 
  (function_exists('apc_store') && ini_get('apc.enabled'))
  ||
  function_exists('eaccelerator_put') && ini_get('eaccelerator.enable')
  ||
  function_exists('xcache_set')
;
check($accelerator, 'has a PHP accelerator', 'Install a PHP accelerator like APC (highly recommended)', false);

check(!ini_get('magic_quotes_gpc'), 'php.ini: magic_quotes_gpc set to off', 'Set it to off in php.ini');
check(!ini_get('register_globals'), 'php.ini: register_globals set to off', 'Set it to off in php.ini');
check(!ini_get('session.auto_start'), 'php.ini: session.auto_start set to off', 'Set it to off in php.ini');

if (!is_cli())
{
  echo '</pre></body></html>';
}
