[?php use_helper('Object') ?]

<?php if ($this->getParameterValue('list.filters')): ?>
<div class="sf_admin_filters">
[?php echo form_tag('<?php echo $this->getModuleName() ?>/list', array('method' => 'get')) ?]

  <fieldset>
    <h2>[?php echo __('filters') ?]</h2>
<?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
<?php $credentials = $this->getParameterValue('list.fields.'.$column->getName().'.credentials') ?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
    [?php if ($sf_user->hasCredential(<?php echo $credentials ?>)): ?]
<?php endif; ?>
    <div class="form-row">
    <label for="<?php echo $column->getName() ?>">[?php echo __('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>:') ?]<?php echo $this->getHelp($column, 'edit') ?></label>
    <div class="content">
    [?php echo <?php echo $this->getColumnFilterTag($column) ?> ?]
    </div>
    </div>
<?php if ($credentials): ?>
    [?php endif; ?]
<?php endif; ?>

    <?php endforeach; ?>
  </fieldset>

  <ul class="sf_admin_actions">
    <li>[?php echo button_to(__('reset'), '<?php echo $this->getModuleName() ?>/list?filter=filter', 'class=sf_admin_action_reset_filter') ?]</li>
    <li>[?php echo submit_tag(__('filter'), 'name=filter class=sf_admin_action_filter') ?]</li>
  </ul>

</form>
</div>
<?php endif; ?>
