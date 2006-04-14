<ul class="sf_admin_actions">
<?php $listActions = $this->getParameterValue('list.actions') ?>
<?php if ($listActions): ?>
  <?php if (is_array($listActions)): ?>
    <?php foreach ($listActions as $actionName => $params): ?>
      <?php echo $this->addCredentialCondition($this->getButtonToAction($actionName, $params, false), $params) ?>
    <?php endforeach; ?>
  <?php endif; ?>
<?php else: ?>
  <?php echo $this->getButtonToAction('_create', array(), false) ?>
<?php endif; ?>
</ul>
