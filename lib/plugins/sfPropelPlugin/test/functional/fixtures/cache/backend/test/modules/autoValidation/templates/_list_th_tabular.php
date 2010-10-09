  <th id="sf_admin_list_th_id">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'id'): ?>
      <?php echo link_to(__('Id'), 'validation/list?sort=id&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Id'), 'validation/list?sort=id&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_title">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'title'): ?>
      <?php echo link_to(__('Title'), 'validation/list?sort=title&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Title'), 'validation/list?sort=title&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_body">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'body'): ?>
      <?php echo link_to(__('Body'), 'validation/list?sort=body&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Body'), 'validation/list?sort=body&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_online">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'online'): ?>
      <?php echo link_to(__('Online'), 'validation/list?sort=online&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Online'), 'validation/list?sort=online&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_excerpt">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'excerpt'): ?>
      <?php echo link_to(__('Excerpt'), 'validation/list?sort=excerpt&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Excerpt'), 'validation/list?sort=excerpt&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_category_id">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'category_id'): ?>
      <?php echo link_to(__('Category'), 'validation/list?sort=category_id&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Category'), 'validation/list?sort=category_id&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_created_at">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'created_at'): ?>
      <?php echo link_to(__('Created at'), 'validation/list?sort=created_at&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Created at'), 'validation/list?sort=created_at&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_end_date">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'end_date'): ?>
      <?php echo link_to(__('End date'), 'validation/list?sort=end_date&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('End date'), 'validation/list?sort=end_date&type=asc') ?>
      <?php endif; ?>
          </th>
  <th id="sf_admin_list_th_book_id">
          <?php if ($sf_user->getAttribute('sort', null, 'sf_admin/article/sort') == 'book_id'): ?>
      <?php echo link_to(__('Book'), 'validation/list?sort=book_id&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort') == 'asc' ? 'desc' : 'asc')) ?>
      (<?php echo __($sf_user->getAttribute('type', 'asc', 'sf_admin/article/sort')) ?>)
      <?php else: ?>
      <?php echo link_to(__('Book'), 'validation/list?sort=book_id&type=asc') ?>
      <?php endif; ?>
          </th>
