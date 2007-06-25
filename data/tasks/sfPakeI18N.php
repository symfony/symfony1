<?php

/*
 * Current known limitations:
 *   - Can only works with the default "messages" catalogue
 *   - For file backends (XLIFF and gettext), it only saves/deletes strings in the "most global" file
 */

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pake_desc('extract i18n strings from php files');
pake_task('i18n-extract');

function run_i18n_extract($task, $args, $options)
{
  if (!count($args))
  {
    throw new Exception('You must provide the application.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception(sprintf('The app "%s" does not exist.', $app));
  }

  if (!isset($args[1]))
  {
    throw new Exception('You must provide a culture.');
  }

  $culture = $args[1];

  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', 'dev');
  define('SF_DEBUG',       true);

  require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

  sfContext::getInstance();

  pake_echo_action('i18n', sprintf('extracting i18n strings for the "%s" application', $app));

  $extract = new sfI18nApplicationExtract();
  $extract->initialize($culture);
  $extract->extract();

  pake_echo_action('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages())));
  pake_echo_action('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages())));

  if (isset($options['auto-save']))
  {
    pake_echo_action('i18n', 'saving new i18n strings');

    $extract->saveNewMessages();
  }

  if (isset($options['auto-delete']))
  {
    pake_echo_action('i18n', 'deleting old i18n strings');

    $extract->deleteOldMessages();
  }
}
