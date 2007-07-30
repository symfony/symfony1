<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Finds non "i18n ready" strings in an application.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18nFindTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'find';
    $this->briefDescription = 'Finds non "i18n ready" strings in an application';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->log($this->formatSection('i18n', sprintf('find non "i18n ready" strings in the "%s" application', $arguments['application'])));

    sfCore::initDirectoryLayout(sfConfig::get('sf_root_dir'), $arguments['application'], $options['env']);

    // Look in templates
    $moduleNames = sfFinder::type('dir')->maxdepth(0)->ignore_version_control()->relative()->in(sfConfig::get('sf_app_dir').'/modules');
    $strings = array();
    foreach ($moduleNames as $moduleName)
    {
      $dir = sfConfig::get('sf_app_dir').'/modules/'.$moduleName.'/templates';
      $templates = sfFinder::type('file')->name('*.php')->relative()->in($dir);
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
        $this->log($this->formatSection('i18n', sprintf('strings in "%s:%s"', $moduleName, $template)));
        foreach ($messages as $message)
        {
          $this->log("  $message\n");
        }
      }
    }
  }
}
