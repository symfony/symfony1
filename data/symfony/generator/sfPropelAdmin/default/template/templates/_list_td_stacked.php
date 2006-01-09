<td colspan="<?php echo count($this->getColumns('list.display.fields'))  ?>">
<?php if ($this->getParameterValue('list.display.params')): ?>
<?php echo $this->getI18NString('list.display.params') ?>
<?php else: ?>
<?php foreach ($this->getColumns('list.display.fields') as $column): ?>
  <?php if ($column->isLink()): ?>
  [?php echo link_to($<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>(), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
  <?php else: ?>
  [?php echo <?php echo $this->getColumnListTag($column) ?> ?]
  <?php endif ?>
   - 
<?php endforeach ?>
<?php endif ?>
</td>