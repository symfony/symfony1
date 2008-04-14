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
 * @subpackage task
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
The [i18n:find|INFO] task finds non internationalized strings embedded in templates:

  [./symfony i18n:find frontend|INFO]

This task has a very limited understanding of templates and can only find simple strings like this one:

  <p>Non i18n text</p>

But it can't find strings embedded in PHP code like this one:

  <p><?php echo 'Test' ?></p>
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->logSection('i18n', sprintf('find non "i18n ready" strings in the "%s" application', $arguments['application']));

    // Look in templates
    $dirs = array();
    $moduleNames = sfFinder::type('dir')->maxdepth(0)->relative()->in(sfConfig::get('sf_app_module_dir'));
    foreach ($moduleNames as $moduleName)
    {
      $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';
    }
    $dirs[] = sfConfig::get('sf_app_dir').'/templates';

    $strings = array();
    foreach ($dirs as $dir)
    {
      $templates = sfFinder::type('file')->name('*.php')->in($dir);
      foreach ($templates as $template)
      {
        $dom = new DomDocument('1.0', sfConfig::get('sf_charset', 'UTF-8'));
        $content = file_get_contents($template);

        // remove doctype
        $content = preg_replace('/<!DOCTYPE.*?>/', '', $content);

        @$dom->loadXML('<doc>'.$content.'</doc>');

        $nodes = array($dom);
        while ($nodes)
        {
          $node = array_shift($nodes);

          if (XML_TEXT_NODE === $node->nodeType)
          {
            if (!$node->isWhitespaceInElementContent())
            {
              if (!isset($strings[$template]))
              {
                $strings[$template] = array();
              }

              $strings[$template][] = $node->nodeValue;
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

    foreach ($strings as $template => $messages)
    {
      $this->logSection('i18n', sprintf('strings in "%s"', str_replace(sfConfig::get('sf_root_dir'), '', $template)), 1000);
      foreach ($messages as $message)
      {
        $this->log("  $message\n");
      }
    }
  }
}
