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

pake_desc('find non "i18n ready" strings in an application');
pake_task('i18n-find');

function run_i18n_find($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the application.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_apps_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception(sprintf('The app "%s" does not exist.', $app));
  }

  pake_echo_action('i18n', sprintf('find non "i18n ready" strings in the "%s" application', $app));

  sfConfig::set('sf_app', $app);
  sfConfig::set('sf_environment', 'dev');
  sfCore::initDirectoryLayout(sfConfig::get('sf_root_dir'), $app, 'dev')

  // Look in templates
  $moduleNames = sfFinder::type('dir')->maxdepth(0)->ignore_version_control()->relative()->in(sfConfig::get('sf_app_dir').'/modules');
  $strings = array();
  foreach ($moduleNames as $moduleName)
  {
    $dir = sfConfig::get('sf_app_dir').'/modules/'.$moduleName.'/templates';
    $templates = pakeFinder::type('file')->name('*.php')->relative()->in($dir);
    foreach ($templates as $template)
    {
      $dom = new DomDocument('1.0', sfConfig::get('sf_charset', 'UTF-8'));
      @$dom->loadXML('<doc>'.file_get_contents($dir.'/'.$template).'</doc>');

      $nodes = array($dom);
      while ($nodes)
      {
        $node = array_shift($nodes);

        if (XML_TEXT_NODE === $node->nodeType)
        {
          if (!$node->isWhitespaceInElementContent())
          {
            if (!isset($strings[$moduleName][$template]))
            {
              if (!isset($strings[$moduleName]))
              {
                $strings[$moduleName] = array();
              }

              $strings[$moduleName][$template] = array();
            }

            $strings[$moduleName][$template][] = $node->nodeValue;
          }
        }
        else if ($node->childNodes)
        {
          for ($i = 0, $max = $node->childNodes->length; $i < $max; $i++)
          {
            $nodes[] = $node->childNodes->item($i);
          }
        }
      }
    }
  }

  foreach ($strings as $moduleName => $templateStrings)
  {
    foreach ($templateStrings as $template => $messages)
    {
      pake_echo_action('i18n', sprintf('strings in "%s:%s"', $moduleName, $template));
      foreach ($messages as $message)
      {
        echo "  $message\n";
      }
    }
  }
}

function run_i18n_extract($task, $args, $options)
{
  if (!count($args))
  {
    throw new Exception('You must provide the application.');
  }

  $app = $args[0];

  if (!is_dir(sfConfig::get('sf_apps_dir').DIRECTORY_SEPARATOR.$app))
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

  if (isset($options['display-new']))
  {
    pake_echo_action('i18n', sprintf('display new i18n strings', count($extract->getOldMessages())));
    foreach ($extract->getNewMessages() as $message)
    {
      echo '               '.$message."\n";
    }
  }

  if (isset($options['auto-save']))
  {
    pake_echo_action('i18n', 'saving new i18n strings');

    $extract->saveNewMessages();
  }

  if (isset($options['display-old']))
  {
    pake_echo_action('i18n', sprintf('display old i18n strings', count($extract->getOldMessages())));
    foreach ($extract->getOldMessages() as $message)
    {
      echo '               '.$message."\n";
    }
  }

  if (isset($options['auto-delete']))
  {
    pake_echo_action('i18n', 'deleting old i18n strings');

    $extract->deleteOldMessages();
  }
}
