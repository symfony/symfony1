<table>
  <tbody>
<?php foreach ($this->getTableMap()->getColumns() as $column): ?>
    <tr>
      <th><?php echo sfInflector::humanize(sfInflector::underscore($column->getPhpName())) ?>:</th>
      <td>[?= $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

<hr />

<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]">Edit</a>
&nbsp;
<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/index') ?]">List</a>
