<td colspan="9">
    <?php echo link_to($article->getId() ? $article->getId() : __('-'), 'validation/edit?id='.$article->getId()) ?>
     - 
    <?php echo $article->getTitle() ?>
     - 
    <?php echo $article->getBody() ?>
     - 
    <?php echo $article->getOnline() ? image_tag(sfConfig::get('sf_admin_web_dir').'/images/tick.png') : '&nbsp;' ?>
     - 
    <?php echo $article->getExcerpt() ?>
     - 
    <?php echo $article->getCategoryId() ?>
     - 
    <?php echo ($article->getCreatedAt() !== null && $article->getCreatedAt() !== '') ? format_date($article->getCreatedAt(), "f") : '' ?>
     - 
    <?php echo ($article->getEndDate() !== null && $article->getEndDate() !== '') ? format_date($article->getEndDate(), "f") : '' ?>
     - 
    <?php echo $article->getBookId() ?>
     - 
</td>