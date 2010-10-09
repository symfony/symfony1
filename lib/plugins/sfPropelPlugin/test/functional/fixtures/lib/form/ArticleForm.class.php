<?php

/**
 * Article form.
 *
 * @package    form
 * @subpackage article
 * @version    SVN: $Id: ArticleForm.class.php 12637 2008-11-04 17:48:38Z fabien $
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
