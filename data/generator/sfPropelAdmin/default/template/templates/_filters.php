[?php use_helper('Object') ?]

<?php if ($this->getParameterValue('list.filters')): ?>
<div class="sf_admin_filters">
[?php echo form_tag('<?php echo $this->getModuleName() ?>/list') ?]

  <fieldset>
    <h2>[?php echo __('filters') ?]</h2>
    <?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
    <div class="form-row">
    <label for="<?php echo $column->getName() ?>">[?php echo __('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>:') ?]<?php echo $this->getHelp($column, 'edit') ?></label>
    [?php echo <?php echo $this->getColumnFilterTag($column) ?> ?]
    </div>

    <?php endforeach ?>
  </fieldset>

  <ul class="sf_admin_actions">
    <li>[?php echo submit_tag(__('filter'), 'name=filter class=sf_admin_action_filter') ?]</li>
  </ul>

</form>
</div>
<?php endif ?>
