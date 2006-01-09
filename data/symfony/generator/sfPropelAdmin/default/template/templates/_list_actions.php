<ul class="sf_admin_actions">
<?php if ($this->getParameterValue('list.actions')): ?>
<?php foreach ($this->getParameterValue('list.actions') as $actionName => $params): ?>
  <?php echo $this->getButtonToAction($actionName, $params, false) ?>
<?php endforeach ?>
<?php else: ?>
  <?php echo $this->getButtonToAction('_create', array(), false) ?>
<?php endif ?>
</ul>
