<?php $form = $this->getFormObject() ?>
<h1><?php echo sfInflector::humanize($this->getModuleName()) ?> List</h1>

<table>
  <thead>
    <tr>
<?php foreach ($this->getTableMap()->getColumns() as $column): ?>
      <th><?php echo sfInflector::humanize(sfInflector::underscore($column->getPhpName())) ?></th>
<?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    [?php foreach ($<?php echo $this->getSingularName() ?>List as $<?php echo $this->getSingularName() ?>): ?]
    <tr>
<?php foreach ($this->getTableMap()->getColumns() as $column): ?>
<?php if ($column->isPrimaryKey()): ?>
      <td><a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]">[?php echo $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</a></td>
<?php else: ?>
      <td>[?php echo $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</td>
<?php endif; ?>
<?php endforeach; ?>
    </tr>
    [?php endforeach; ?]
  </tbody>
</table>

<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/<?php echo isset($this->params['non_atomic_actions']) && $this->params['non_atomic_actions'] ? 'edit' : 'create' ?>') ?]">Create</a>
