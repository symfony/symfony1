<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class backendTestBrowser extends sfTestBrowser
{
  public function checkListCustomization($title, $listParams)
  {
    $this->test()->diag($title);

    $this->customizeGenerator(array('list' => $listParams));

    return $this->getAndCheck('article', 'list');
  }

  public function checkEditCustomization($title, $editParams)
  {
    $this->test()->diag($title);

    $this->customizeGenerator(array('edit' => $editParams));

    return $this->
      get('/article/edit/id/1')->
      isStatusCode(200)->
      isRequestParameter('module', 'article')->
      isRequestParameter('action', 'edit')
    ;
  }

  private function customizeGenerator($params)
  {
    $params['model_class'] = 'Article';
    $params['moduleName']  = 'article';
    sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
    $generatorManager = new sfGeneratorManager();
    $generatorManager->initialize();
    mkdir(sfConfig::get('sf_config_cache_dir'), 0777);
    file_put_contents(sfConfig::get('sf_config_cache_dir').'/modules_article_config_generator.yml.php', '<?php '.$generatorManager->generate('sfPropelAdminGenerator', $params));
  }
}
