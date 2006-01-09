<?php foreach ($this->getColumns('list.display.fields') as $column): ?>
  <?php if ($column->isLink()): ?>
  <td>[?php echo link_to($<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>(), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo <?php echo $this->getColumnListTag($column) ?> ?]</td>
  <?php endif ?>
<?php endforeach ?>
