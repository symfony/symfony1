<table>
<tbody>
<?php foreach ($this->tableMap->getColumns() as $name => $column): ?>
<tr>
<th><b><?= $column->getPhpName() ?>: </b></th>
<td>[?= $object->get<?php echo $column->getPhpName() ?>() ?]</td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<hr />
[?php echo link_to ('edit', '<?php echo $moduleName ?>/edit?id='.$object-><?php echo $this->primaryKeyMethod ?>()) ?]
&nbsp;[?php echo link_to ('list', '<?php echo $moduleName ?>/list') ?]
