<?php echo form_tag('validation/save', array(
  'id'        => 'sf_admin_edit_form',
  'name'      => 'sf_admin_edit_form',
  'multipart' => true,
)) ?>

<?php echo object_input_hidden_tag($article, 'getId') ?>

<fieldset id="sf_fieldset_none" class="">

<div class="form-row">
  <?php echo label_for('article[title]', __($labels['article{title}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('article{title}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{title}')): ?>
    <?php echo form_error('article{title}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($article, 'getTitle', array (
  'size' => 80,
  'control_name' => 'article[title]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[body]', __($labels['article{body}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{body}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{body}')): ?>
    <?php echo form_error('article{body}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_textarea_tag($article, 'getBody', array (
  'size' => '30x3',
  'control_name' => 'article[body]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[online]', __($labels['article{online}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{online}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{online}')): ?>
    <?php echo form_error('article{online}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_checkbox_tag($article, 'getOnline', array (
  'control_name' => 'article[online]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[excerpt]', __($labels['article{excerpt}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{excerpt}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{excerpt}')): ?>
    <?php echo form_error('article{excerpt}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_tag($article, 'getExcerpt', array (
  'size' => 20,
  'control_name' => 'article[excerpt]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[category_id]', __($labels['article{category_id}']), 'class="required" ') ?>
  <div class="content<?php if ($sf_request->hasError('article{category_id}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{category_id}')): ?>
    <?php echo form_error('article{category_id}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_select_tag($article, 'getCategoryId', array (
  'related_class' => 'Category',
  'control_name' => 'article[category_id]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[created_at]', __($labels['article{created_at}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{created_at}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{created_at}')): ?>
    <?php echo form_error('article{created_at}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_date_tag($article, 'getCreatedAt', array (
  'rich' => true,
  'withtime' => true,
  'calendar_button_img' => '/sf/sf_admin/images/date.png',
  'control_name' => 'article[created_at]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[end_date]', __($labels['article{end_date}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{end_date}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{end_date}')): ?>
    <?php echo form_error('article{end_date}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_input_date_tag($article, 'getEndDate', array (
  'rich' => true,
  'withtime' => true,
  'calendar_button_img' => '/sf/sf_admin/images/date.png',
  'control_name' => 'article[end_date]',
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

<div class="form-row">
  <?php echo label_for('article[book_id]', __($labels['article{book_id}']), '') ?>
  <div class="content<?php if ($sf_request->hasError('article{book_id}')): ?> form-error<?php endif; ?>">
  <?php if ($sf_request->hasError('article{book_id}')): ?>
    <?php echo form_error('article{book_id}', array('class' => 'form-error-msg')) ?>
  <?php endif; ?>

  <?php $value = object_select_tag($article, 'getBookId', array (
  'related_class' => 'Book',
  'control_name' => 'article[book_id]',
  'include_blank' => true,
)); echo $value ? $value : '&nbsp;' ?>
    </div>
</div>

</fieldset>

<?php include_partial('edit_actions', array('article' => $article)) ?>

</form>

<ul class="sf_admin_actions">
      <li class="float-left"><?php if ($article->getId()): ?>
<?php echo button_to(__('delete'), 'validation/delete?id='.$article->getId(), array (
  'post' => true,
  'confirm' => __('Are you sure?'),
  'class' => 'sf_admin_action_delete',
)) ?><?php endif; ?>
</li>
  </ul>
