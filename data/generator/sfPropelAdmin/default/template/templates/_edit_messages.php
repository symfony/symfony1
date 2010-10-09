[?php if ($sf_request->hasErrors()): ?]
<div class="form-errors">
<h2>[?php echo __('The form is not valid because it contains some errors.') ?]</h2>
<dl>
[?php foreach ($sf_request->getErrorNames() as $name): ?]
  <dt>[?php echo __($labels[$name]) ?]</dt>
  <dd>[?php echo $sf_request->getError($name) ?]</dd>
[?php endforeach; ?]
</dl>
</div>
[?php elseif ($sf_flash->has('notice')): ?]
<div class="save-ok">
<h2>[?php echo __($sf_flash->get('notice')) ?]</h2>
</div>
[?php endif; ?]
