    <td><?php echo link_to($article->getId() ? $article->getId() : __('-'), 'validation/edit?id='.$article->getId()) ?></td>
    <td><?php echo $article->getTitle() ?></td>
      <td><?php echo $article->getBody() ?></td>
      <td><?php echo $article->getOnline() ? image_tag(sfConfig::get('sf_admin_web_dir').'/images/tick.png') : '&nbsp;' ?></td>
      <td><?php echo $article->getExcerpt() ?></td>
      <td><?php echo $article->getCategoryId() ?></td>
      <td><?php echo ($article->getCreatedAt() !== null && $article->getCreatedAt() !== '') ? format_date($article->getCreatedAt(), "f") : '' ?></td>
      <td><?php echo ($article->getEndDate() !== null && $article->getEndDate() !== '') ? format_date($article->getEndDate(), "f") : '' ?></td>
      <td><?php echo $article->getBookId() ?></td>
  