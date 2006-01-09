<ul class="sf_admin_actions">
<?php if ($this->getParameterValue('edit.actions')): ?>
<?php foreach ($this->getParameterValue('edit.actions') as $actionName => $params): ?>
  <?php echo $this->getButtonToAction($actionName, $params, true) ?>
<?php endforeach ?>
<?php else: ?>
  <?php echo $this->getButtonToAction('_delete', array(), true) ?>
  <?php echo $this->getButtonToAction('_list', array(), true) ?>
  <?php echo $this->getButtonToAction('_save', array(), true) ?>
<?php endif ?>
</ul>
