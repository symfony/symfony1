[?php use_helpers('I18N', 'Date') ?]

<h1><?php echo $this->getParameterValue('list.title', $this->getModuleName().' list') ?></h1>

<div id="sf_admin_introduction">
[?php include_partial('<?php echo $this->getModuleName() ?>/list_introduction') ?]
</div>

<div id="sf_admin_bar">

<?php if ($this->getParameterValue('list.filters')): ?>
[?php echo include_partial('filters', array('filters' => $filters)) ?]
<?php endif ?>

</div>

<div id="sf_admin_content">

<table cellspacing="0" class="sf_admin_list">
<thead>
<tr>
[?php echo include_partial('list_th_<?php echo $this->getParameterValue('list.display.layout', 'tabular') ?>') ?]
<?php if ($this->getParameterValue('list.display.actions')): ?>
  <th>[?php echo __('Actions') ?]</th>
<?php endif ?>
</tr>
</thead>
<tbody>
[?php $i = 1; foreach ($pager->getResults() as $<?php echo $this->getSingularName() ?>): $odd = fmod(++$i, 2) ?]
<tr class="sf_admin_row_[?php echo $odd ?]">
[?php echo include_partial('list_td_<?php echo $this->getParameterValue('list.display.layout', 'tabular') ?>', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>)) ?]
[?php echo include_partial('list_td_actions', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>)) ?]
</tr>
[?php endforeach ?]
</tbody>
<tfoot>
<tr><th colspan="<?php echo $this->getParameterValue('list.display.actions') ? count($this->getColumns('list.display.fields')) + 1 : count($this->getColumns('list.display.fields')) ?>">
<div class="float-right">
[?php if ($pager->haveToPaginate()): ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/first.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page=1') ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/previous.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getPreviousPage()) ?]

  [?php foreach ($pager->getLinks() as $page): ?]
    [?php echo link_to_unless($page == $pager->getPage(), $page, '<?php echo $this->getModuleName() ?>/list?page='.$page) ?]
  [?php endforeach ?]

  [?php echo link_to(image_tag('/sf/images/sf_admin/next.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getNextPage()) ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/last.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getLastPage()) ?]
[?php endif ?]
</div>
[?php echo format_number_choice('[0] no result|[1] 1 result|(1,+Inf] %1% results', array('%1%' => $pager->getNbResults()), $pager->getNbResults()) ?]
</th></tr>
</tfoot>
</table>

[?php echo include_partial('list_actions') ?]

</div>
