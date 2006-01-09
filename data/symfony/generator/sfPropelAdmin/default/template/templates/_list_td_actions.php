<?php if ($this->getParameterValue('list.display.actions')): ?>
<td>
<ul class="sf_admin_td_actions">
<?php foreach ($this->getParameterValue('list.display.actions') as $actionName => $params): ?>
  <?php echo $this->getLinkToAction($actionName, $params, true) ?>
<?php endforeach ?>
</ul>
</td>
<?php endif ?>
