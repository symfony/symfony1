<?php

/**
 * Article form.
 *
 * @package    form
 * @subpackage article
 * @version    SVN: $Id$
 */
class ArticleForm extends BaseArticleForm
{
  public function configure()
  {
    if ($category = $this->getObject()->getCategory())
    {
      $this->embedForm('category', new CategoryForm($this->getObject()->getCategory()));
    }
  }
}
