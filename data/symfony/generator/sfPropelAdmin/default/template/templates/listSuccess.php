<h1>List <?php echo $this->getModuleName() ?></h1>

<div class="module">
<table width="100%">
<thead>
<tr>
<?php foreach ($this->getColumns('list_fields') as $column): ?>
  <th><?php echo $this->getFieldName($column->getName()) ?></th>
<?php endforeach ?>
</tr>
</thead>
<tbody>
[?php foreach ($<?php echo $this->getPluralName() ?> as $<?php echo $this->getSingularName() ?>): ?]
<tr>
<?php foreach ($this->getColumns('list_fields') as $column): ?>
  <?php if ($this->isLinkedColumn($column)): ?>
  <td>[?php echo link_to($<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>(), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</td>
  <?php endif ?>
<?php endforeach ?>
</tr>
[?php endforeach ?]
</tbody>
</table>
</div>

<ul>
  <li>[?php echo link_to ('create', '<?php echo $this->getModuleName() ?>/edit') ?]</li>
</ul>

