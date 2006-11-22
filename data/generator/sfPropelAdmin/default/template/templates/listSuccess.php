[?php use_helper('I18N', 'Date') ?]

<div id="sf_admin_container">

<h1><?php echo $this->getI18NString('list.title', $this->getModuleName().' list') ?></h1>

<div id="sf_admin_header">
[?php include_partial('<?php echo $this->getModuleName() ?>/list_header', array('pager' => $pager)) ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/list_messages', array('pager' => $pager)) ?]
</div>

<div id="sf_admin_bar">
<?php if ($this->getParameterValue('list.filters')): ?>
[?php include_partial('filters', array('filters' => $filters)) ?]
<?php endif; ?>
</div>

<div id="sf_admin_content">
[?php if(!$pager->getNbResults()): ?]
[?php echo __('no result') ?]
[?php else: ?]
[?php include_partial('<?php echo $this->getModuleName() ?>/list', array('pager' => $pager)) ?]
[?php endif; ?]
[?php include_partial('list_actions') ?]
</div>

<div id="sf_admin_footer">
[?php include_partial('<?php echo $this->getModuleName() ?>/list_footer', array('pager' => $pager)) ?]
</div>

</div>
