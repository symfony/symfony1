[?php use_helpers('Object', 'Validation', 'ObjectAdmin') ?]

<h1>Edit <?php echo $this->getModuleName() ?></h1>

[?php if ($request->hasErrors()): ?]
<div class="form-errors">
<h2>There are some errors that prevent the form to validate:</h2>
<ul>
[?php foreach ($request->getErrorNames() as $name): ?]
  <li>[?php echo $request->getError($name) ?]</li>
[?php endforeach ?]
</ul>
</div>
[?php endif ?]

[?php echo form_tag('<?php echo $this->getModuleName() ?>/edit') ?]

<?php foreach ($this->getPrimaryKey() as $pk): ?>
[?php echo object_input_hidden_tag($<?php echo $this->getSingularName() ?>, 'get<?php echo $pk->getPhpName() ?>') ?]
<?php endforeach ?>

<?php foreach ($this->getColumnCategories('edit_fields') as $category): ?>
<?php
  if ($category[0] == '-')
  {
    $category_name = substr($category, 1);
    $collapse = true;
  }
  else
  {
    $category_name = $category;
    $collapse = false;
  }
?>
<fieldset class="module<?php if ($collapse): ?> collapse<?php endif ?>">
<?php if ($category != 'NONE'): ?><h2><?php echo $category_name ?></h2>

<?php endif ?>
<?php foreach ($this->getColumns('edit_fields', $category) as $name => $column): ?>
<?php if ($column->isPrimaryKey()) continue ?>
<div class="form-row">
  <label <?php if ($column->isNotNull()): ?>class="required" <?php endif ?>for="<?php echo $column->getName() ?>"><?php echo $this->getFieldName($column->getName()) ?>:</label>
  <div[?php if ($request->hasError('<?php echo $column->getName() ?>')): ?] class="form-error"[?php endif ?]>
  [?php if ($request->hasError('<?php echo $column->getName() ?>')): ?]<div class="form-error-msg">&darr;&nbsp;[?php echo $request->getError('<?php echo $column->getName() ?>') ?]&nbsp;&darr;</div>[?php endif ?]

  [?php echo <?php echo $this->getColumnEditTag($column) ?> ?]
  </div>
</div>

<?php endforeach ?>
</fieldset>
<?php endforeach ?>

<div class="submit-row">
[?php if (<?php echo $this->getPrimaryKeyIsSet() ?>): ?]
  <p class="float-left">[?php echo link_to('delete', '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, 'post=true&confirm=Are you sure?') ?]</p>
[?php endif ?]
  &nbsp;[?php echo link_to('cancel', '<?php echo $this->getModuleName() ?>/list') ?]
  [?php echo submit_tag('save', 'class=default') ?]
</div>

</form>
