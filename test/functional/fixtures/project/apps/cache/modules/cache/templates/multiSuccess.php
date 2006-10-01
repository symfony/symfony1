<?php include_partial('cache/partial') ?>

<?php include_partial('cache/cacheablePartial') ?>

<?php include_partial('cache/cacheablePartial', array('foo' => 'bar')) ?>

<?php include_component('cache', 'component') ?>

<?php include_component('cache', 'cacheableComponent') ?>

<?php include_component('cache', 'cacheableComponent', array('foo' => 'bar')) ?>
