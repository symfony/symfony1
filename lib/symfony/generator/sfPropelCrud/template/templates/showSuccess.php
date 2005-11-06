<table>
<tbody>
<?php foreach ($this->getTableMap()->getColumns() as $name => $column): ?>
<tr>
<th><b><?php echo $column->getPhpName() ?>: </b></th>
<td>[?= $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</td>
</tr>
<?php endforeach ?>
</tbody>
</table>
<hr />
[?php echo link_to('edit', '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
&nbsp;[?php echo link_to('list', '<?php echo $this->getModuleName() ?>/list') ?]
