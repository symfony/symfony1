<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Adds BaseForm and upgrades form classes to extend it.
 *
 * @package    symfony
 * @subpackage task
 * @author     Pascal Borreli <pborreli@sqli.com>
 * @version    SVN: $Id: sfFormSymfonyUpgrade.class.php 12542 2008-11-01 15:38:31Z Pascal $
 */
class sfFormSymfonyUpgrade extends sfUpgrade
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

    // change all forms that extend sfForm to extend BaseForm
    $finder = sfFinder::type('file')->name('*.php');
    foreach ($finder->in($this->getProjectLibDirectories('/form')) as $file)
    {
      $contents = file_get_contents($file);
      if (preg_match_all('/\bextends\b\s+\bsfForm\b/i', $contents, $matches))
      {
        foreach ($matches[0] as $match)
        {
          $contents = str_replace($match, str_ireplace('sfForm', 'BaseForm', $match), $contents);
        }

        $this->logSection('form', 'Migrating '.$file);
        file_put_contents($file, $contents);
      }
    }
  }
}
