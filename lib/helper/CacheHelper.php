<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
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
 * @version    SVN: $Id: CacheHelper.php 5085 2007-09-14 20:09:53Z fabien $
 */

/* Usage

<?php if (!cache('name')): ?>

... HTML ...

  <?php cache_save() ?>
<?php endif; ?>

*/
function cache($name, $lifeTime = 86400)
{
  $context = sfContext::getInstance();

  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }

  $request = $context->getRequest();
  $cache   = $context->getViewCacheManager();

  if (!is_null($request->getAttribute('started', null, 'symfony/action/sfAction/cache')))
  {
    throw new sfCacheException('Cache already started');
  }

  $data = $cache->start($name, $lifeTime);

  if ($data === null)
  {
    $request->setAttribute('started', 1, 'symfony/action/sfAction/cache');
    $request->setAttribute('current_name', $name, 'symfony/action/sfAction/cache');

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
  $context = sfContext::getInstance();

  if (!sfConfig::get('sf_cache'))
  {
    return null;
  }

  $request = $context->getRequest();

  if (is_null($request->getAttribute('started', null, 'symfony/action/sfAction/cache')))
  {
    throw new sfCacheException('Cache not started');
  }

  $name = $request->getAttribute('current_name', '', 'symfony/action/sfAction/cache');

  $data = $context->getViewCacheManager()->stop($name);

  $request->setAttribute('started', null, 'symfony/action/sfAction/cache');
  $request->setAttribute('current_name', null, 'symfony/action/sfAction/cache');

  echo $data;
}
