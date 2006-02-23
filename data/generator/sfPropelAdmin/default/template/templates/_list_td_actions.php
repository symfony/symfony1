<?php if ($this->getParameterValue('list.display.actions')): ?>
<td>
<ul class="sf_admin_td_actions">
<?php foreach ($this->getParameterValue('list.display.actions') as $actionName => $params): ?>
  <?php echo $this->addCredentialCondition($this->getLinkToAction($actionName, $params, true), $params) ?>
<?php endforeach ?>
</ul>
</td>
<?php endif ?>
