<?php if ($actions = $this->configuration->getValue('list.actions')): ?>
<?php foreach ($actions as $name => $params): ?>
<?php if ('_new' == $name): ?>
<?php echo $this->addCredentialCondition('[?php echo $helper->linkToNew('.$this->asPhp($params).') ?]', $params) ?>
<?php else: ?>
  <li class="sf_admin_action_<?php echo $params['class_suffix'] ?>">
    <?php echo $this->addCredentialCondition($this->getLinkToAction($name, $params, false), $params) ?>
  </li>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
