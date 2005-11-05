<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * CacheHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/* Usage

<?php if (!cache()): ?>

... HTML ...

  <?php cache_save() ?>
<?php endif ?>

*/
function cache($lifeTime = SF_DEFAULT_CACHE_LIFETIME, $uri = sfViewCacheManager::CURRENT_URI)
{
  if (!SF_CACHE)
    return null;

  $context = sfContext::getInstance();
  $request = $context->getRequest();
  $cache   = $context->getViewCacheManager();

  // get the current action instance
  $actionInstance = $context->getController()->getActionStack()->getLastEntry()->getActionInstance();

  // get the current action information
  $moduleName = $context->getModuleName();
  $actionName = $context->getActionName();

  if ($request->getAttribute('cache_started') !== null)
    throw new sfCacheException('Cache already started');

  $data = $cache->start($moduleName, $actionName, $lifeTime, $uri);

  if ($data === null)
  {
    $request->setAttribute('started', 1, 'symfony/action/sfAction/cache');

    return 0;
  }
  else
  {
    echo $data;

    return 1;
  }
}

function cache_save()
{
  if (!SF_CACHE)
    return null;

  $context = sfContext::getInstance();
  $request = $context->getRequest();

  if ($request->getAttribute('started', null, 'symfony/action/sfAction/cache') === null)
    throw new sfCacheException('Cache not started');

  // get the current action instance
  $actionInstance = $context->getController()->getActionStack()->getLastEntry()->getActionInstance();

  // get the current action information
  $moduleName = $context->getModuleName();
  $actionName = $context->getActionName();

  $data = $context->getViewCacheManager()->stop($moduleName, $actionName);

  $request->setAttribute('started', null, 'symfony/action/sfAction/cache');

  echo $data;
}

?>