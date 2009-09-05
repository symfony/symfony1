<?php

$app = 'frontend';
include dirname(__FILE__).'/../../bootstrap/functional.php';

$t = new lime_test(6);

$t->diag('->getQuery()');

$filter = new ArticleFormFilter();
$filter->bind(array());
$t->isa_ok($filter->getQuery(), 'Doctrine_Query', '->getQuery() returns a Doctrine_Query object');

$query = Doctrine_Query::create()->select('title, body');

$filter = new ArticleFormFilter(array(), array('query' => $query));
$filter->bind(array());
$t->is_deeply($filter->getQuery()->getDqlPart('select'), array('title, body'), '->getQuery() uses the query option');
$t->ok($filter->getQuery() !== $query, '->getQuery() clones the query option');

$t->diag('->setTableMethod()');

$filter = new ArticleFormFilter();
$filter->setTableMethod('getNewQuery');
$filter->bind(array());
$t->is_deeply($filter->getQuery()->getDqlPart('select'), array('title, body'), '->setTableMethod() specifies a method that can return a new query');

$filter = new ArticleFormFilter();
$filter->setTableMethod('filterSuppliedQuery');
$filter->bind(array());
$t->is_deeply($filter->getQuery()->getDqlPart('select'), array('title, body'), '->setTableMethod() specifies a method that can modify the supplied query');

$filter = new ArticleFormFilter();
$filter->setTableMethod('filterSuppliedQueryAndReturn');
$filter->bind(array());
$t->is_deeply($filter->getQuery()->getDqlPart('select'), array('title, body'), '->setTableMethod() specifies a method that can modify and return the supplied query');
