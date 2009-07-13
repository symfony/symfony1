<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Tries to fix the removal of the common filter.
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
        // remove the common filter from all filters.yml configuration file
        $dir = sfConfig::get('sf_lib_dir');

        if (file_exists($file = $dir.'/form/BaseForm.class.php'))
        {
            return;
        }
        $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);

        $tokens = array(
            'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
            'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here'
        );

        $content = file_get_contents($this->createConfiguration(null, null)->getSymfonyLibDir().'/task/generator/skeleton/project/lib/form/BaseForm.class.php');
        $this->logSection('form', sprintf('Creating %s', $file));
        file_put_contents($file, $content);

        $this->getFilesystem()->replaceTokens(array($file), '##', '##', $tokens);

    }
}