[?php use_helper('Object') ?]

[?php echo form_tag(''.$last_module.'/update') ?]

<table>
<?php foreach ($this->tableMap->getColumns() as $name => $column): ?>
<?php if ($column->isPrimaryKey()): ?>
[?php echo object_input_hidden_tag($object, '<?php echo 'get'.$column->getPhpName() ?>'); ?]
<?php else: ?>
<tr>
<th><?php echo $column->getPhpName() ?><?php if ($column->isNotNull()): ?>*<?php endif ?>:</th>
<td>[?php echo <?php
  $type = $column->getCreoleType();
  if ($column->isForeignKey())
  {
    // load map for related table
    $relatedTable = $this->map->getDatabaseMap()->getTable($column->getRelatedTableName());
    echo "object_select_tag(\$object, 'get{$column->getPhpName()}', array('related_class' => '{$relatedTable->getPhpName()}'))";
  }
  else if ($type == CreoleTypes::DATE)
  {
    echo "object_input_date_tag(\$object, 'get{$column->getPhpName()}')";
  }
  else if ($type == CreoleTypes::BOOLEAN)
  {
    echo "object_checkbox_tag(\$object, 'get{$column->getPhpName()}')";
  }
  else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
  {
    echo "object_input_tag(\$object, 'get{$column->getPhpName()}')";
  }
  else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
  {
    echo "object_input_tag(\$object, 'get{$column->getPhpName()}', array('size' => 7))";
  }
  else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE)
  {
    echo "object_input_tag(\$object, 'get{$column->getPhpName()}', array('size' => 7))";
  }
  else if ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
  {
    echo "object_textarea_tag(\$object, 'get{$column->getPhpName()}')";
  }
  else
  {
    echo "object_input_tag(\$object, 'get{$column->getPhpName()}', array('disabled' => true))";
  }
  ?>
  ?]</td>
</tr>
<?php endif ?>
<?php endforeach ?>
</table>
<hr />
[?php echo submit_tag('save') ?]
[?php if ($object-><?php echo $this->primaryKeyMethod ?>()): ?]
  &nbsp;[?php echo link_to('cancel', '<?php echo $moduleName ?>/show?id='.$object-><?php echo $this->primaryKeyMethod ?>()) ?]
  &nbsp;[?php echo link_to('delete', '<?php echo $moduleName ?>/delete?id='.$object-><?php echo $this->primaryKeyMethod ?>()) ?]
[?php else: ?]
  &nbsp;[?php echo link_to('cancel', '<?php echo $moduleName ?>/list') ?]
[?php endif ?]
</form>
