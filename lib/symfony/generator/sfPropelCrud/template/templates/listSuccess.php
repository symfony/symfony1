<h1><?php echo $moduleName ?></h1>

<table>
<thead>
<tr>
<?php foreach ($this->tableMap->getColumns() as $column): ?>
  <th><b><?php echo $column->getPhpName() ?></b></th>
<?php endforeach ?>
</tr>
</thead>
<tbody>
[?php foreach ($objects as $object): ?]
<tr>
<?php foreach ($this->tableMap->getColumns() as $column): ?>
  <?php if ($column->isPrimaryKey()): ?>
  <td>[?php echo link_to($object->get<?php echo $column->getPhpName() ?>(), '<?php echo $moduleName ?>/show?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo $object->get<?php echo $column->getPhpName() ?>() ?]</td>
  <?php endif ?>
<?php endforeach ?>
</tr>
[?php endforeach ?]
</tbody>
</table>

[?php echo link_to ('create', '<?php echo $moduleName ?>/edit') ?]
