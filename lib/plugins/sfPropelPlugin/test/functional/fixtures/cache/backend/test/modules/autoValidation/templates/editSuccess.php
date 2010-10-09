<?php use_helper('Object', 'Validation', 'ObjectAdmin', 'I18N', 'Date') ?>

<?php use_stylesheet('/sf/sf_admin/css/main') ?>

<div id="sf_admin_container">

<h1><?php echo __('edit validation', 
array()) ?></h1>

<div id="sf_admin_header">
<?php include_partial('validation/edit_header', array('article' => $article)) ?>
</div>

<div id="sf_admin_content">
<?php include_partial('validation/edit_messages', array('article' => $article, 'labels' => $labels)) ?>
<?php include_partial('validation/edit_form', array('article' => $article, 'labels' => $labels)) ?>
</div>

<div id="sf_admin_footer">
<?php include_partial('validation/edit_footer', array('article' => $article)) ?>
</div>

</div>
