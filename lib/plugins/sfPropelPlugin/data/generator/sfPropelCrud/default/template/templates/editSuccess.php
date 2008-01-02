<?php $form = $this->getFormObject() ?>
[?php $<?php echo $this->getSingularName() ?> = $form->getObject() ?]
<h1>[?php echo $<?php echo $this->getSingularName() ?>->isNew() ? 'New' : 'Edit' ?] <?php echo sfInflector::humanize($this->getModuleName()) ?></h1>

<form action="[?php echo url_for('<?php echo $this->getModuleName() ?>/<?php echo isset($this->params['non_atomic_actions']) && $this->params['non_atomic_actions'] ? 'edit' : 'update' ?>'.(!$<?php echo $this->getSingularName() ?>->isNew() ? '?<?php echo $this->getPrimaryKeyUrlParams() ?> : '')) ?]" method="post" [?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?]>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          &nbsp;<a href="[?php echo url_for('<?php echo $this->getModuleName() ?>/index') ?]">Cancel</a>
          [?php if (!$<?php echo $this->getSingularName() ?>->isNew()): ?]
            &nbsp;[?php echo link_to('Delete', '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, array('post' => true, 'confirm' => 'Are you sure?')) ?]
          [?php endif; ?]
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
<?php if (isset($this->params['non_verbose_templates']) && $this->params['non_verbose_templates']): ?>
      [?php echo $this->getAttributeHolder()->isEscaped() ? $form->render(ESC_RAW) : $form ?]
<?php else: ?>

<?php foreach ($form->getWidgetSchema()->getPositions() as $i => $name): ?>
<?php if ($form[$name]->isHidden()) continue ?>
      <tr>
        <th><?php echo $form[$name]->renderLabel() ?></th>
        <td>
          [?php echo $form['<?php echo $name ?>']->renderError() ?]
          [?php echo $form['<?php echo $name ?>'] ?]
<?php $i == $this->getLastNonHiddenField() and print $this->getHiddenFieldsAsString() ?>
        </td>
      </tr>
<?php endforeach; ?>
<?php endif; ?>
    </tbody>
  </table>
</form>
