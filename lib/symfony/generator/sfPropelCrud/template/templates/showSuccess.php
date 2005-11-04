<table>
<tbody>
<?php foreach ($this->tableMap->getColumns() as $name => $column): ?>
<tr>
<th><b><?php echo $column->getPhpName() ?>: </b></th>
<td>[?= $object->get<?php echo $column->getPhpName() ?>() ?]</td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<hr />
[?php echo link_to('edit', '<?php echo $moduleName ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
&nbsp;[?php echo link_to('list', '<?php echo $moduleName ?>/list') ?]
