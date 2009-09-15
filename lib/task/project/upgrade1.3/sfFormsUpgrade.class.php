<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Migrates form classes.
 *
 * @package    symfony
 * @subpackage task
 * @author     Pascal Borreli <pborreli@sqli.com>
 * @version    SVN: $Id: sfFormSymfonyUpgrade.class.php 12542 2008-11-01 15:38:31Z Pascal $
 */
class sfFormsUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    if (!file_exists($file = sfConfig::get('sf_lib_dir').'/form/BaseForm.class.php'))
    {
      $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);
      $tokens = array(
        'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
        'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here'
      );

      $this->getFilesystem()->copy(sfConfig::get('sf_symfony_lib_dir').'/task/generator/skeleton/project/lib/form/BaseForm.class.php', $file);
      $this->getFilesystem()->replaceTokens(array($file), '##', '##', $tokens);
    }

    $finder = sfFinder::type('file')->name('*.php');
    foreach ($finder->in($this->getProjectLibDirectories('/form')) as $file)
    {
      $contents = file_get_contents($file);
      $changed = false;

      // change all forms that extend sfForm to extend BaseForm (one at a time)
      while (preg_match('/\bextends\b\s+\bsfForm\b/i', $contents, $match, PREG_OFFSET_CAPTURE))
      {
        list($search, $offset) = current($match);
        $replace = str_ireplace('sfForm', 'BaseForm', $search);

        $contents = substr($contents, 0, $offset).$replace.substr($contents, $offset + strlen($search));

        $changed = true;
      }

      // change sfWidgetFormInput to sfWidgetFormInputText (one at a time)
      while (preg_match('/\bnew\b\s+\bsfWidgetFormInput\b/i', $contents, $match, PREG_OFFSET_CAPTURE))
      {
        list($search, $offset) = current($match);
        $replace = $search.'Text';

        $contents = substr($contents, 0, $offset).$replace.substr($contents, $offset + strlen($search));

        $changed = true;
      }

      if ($changed)
      {
        $this->logSection('form', 'Migrating '.$file);
        file_put_contents($file, $contents);
      }
    }
  }
}
