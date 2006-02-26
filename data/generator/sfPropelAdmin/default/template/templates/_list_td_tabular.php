<?php foreach ($this->getColumns('list.display') as $column): ?>
  <?php if ($column->isLink()): ?>
  <td>[?php echo link_to(<?php echo $this->getColumnListTag($column) ?>, '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo <?php echo $this->getColumnListTag($column) ?> ?]</td>
  <?php endif ?>
<?php endforeach ?>
