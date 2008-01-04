<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrade web debug.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $filtersFinder = $this->getFinder('file')->name('filters.yml');
    foreach ($filtersFinder->in($this->getProjectConfigDirectories()) as $file)
    {
      $content = file_get_contents($file);
      $content = preg_replace("#web_debug\:\s+~\s*\n#s", '', $content, -1, $count);
      if ($count)
      {
        $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('web_debug', sprintf('Migrating %s', $file)))));
        file_put_contents($file, $content);
      }
    }
  }
}
