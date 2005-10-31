<?php

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: CacheHelper.php 375 2005-08-25 10:46:12Z fabien $
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