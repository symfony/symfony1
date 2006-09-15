<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/util/sfDomCssSelector.class.php');

$t = new lime_test(19, new lime_output_color());

$html = <<<EOF
<html>
  <head>
  </head>
  <body>
    <h1>Test page</h1>

    <h2>Title 1</h2>
    <p class="header">header</p>
    <p class="foo bar foobar">multi-classes</p>

    <ul id="list">
      <li>First</li>
      <li>Second with a <a href="http://www.google.com/" class="foo1 bar1 bar1-foo1 foobar1">link</a></li>
    </ul>

    <ul id="anotherlist">
      <li>First</li>
      <li>Third with a <a class="bar1-foo1">another link</a></li>
    </ul>

    <h2>Title 2</h2>
    <ul id="mylist">
      <li>element 1</li>
      <li>element 2</li>
      <ul>
        <li>element 3</li>
        <li>element 4</li>
      </ul>
    </ul>

    <div id="footer">footer</div>
  </body>
</html>
EOF;

$dom = new DomDocument('1.0', 'utf-8');
$dom->validateOnParse = true;
$dom->loadHTML($html);

// ->getTexts()
$t->diag('->getTexts()');

$c = new sfDomCssSelector($dom);
$t->is($c->getTexts('h1'), array('Test page'), '->getTexts() takes a CSS selector as its first argument');
$t->is($c->getTexts('h2'), array('Title 1', 'Title 2'), '->getTexts() returns an array of matching texts');
$t->is($c->getTexts('#footer'), array('footer'), '->getTexts() supports searching html elements by id');
$t->is($c->getTexts('div#footer'), array('footer'), '->getTexts() supports searching html elements by id for a tag name');

$t->is($c->getTexts('.header'), array('header'), '->getTexts() supports searching html elements by class name');
$t->is($c->getTexts('p.header'), array('header'), '->getTexts() supports searching html elements by class name for a tag name');
$t->is($c->getTexts('div.header'), array(), '->getTexts() supports searching html elements by class name for a tag name');

$t->is($c->getTexts('.foo'), array('multi-classes'), '->getTexts() supports searching html elements by class name for multi-class elements');
$t->is($c->getTexts('.bar'), array('multi-classes'), '->getTexts() supports searching html elements by class name for multi-class elements');
$t->is($c->getTexts('.foobar'), array('multi-classes'), '->getTexts() supports searching html elements by class name for multi-class elements');

$t->is($c->getTexts('ul#mylist ul li'), array('element 3', 'element 4'), '->getTexts() supports searching html elements by several selectors');

$t->is($c->getTexts('ul#list li a[href]'), array('link'), '->getTexts() supports checking attribute existence');
$t->is($c->getTexts('ul#list li a[class~="foo1"]'), array('link'), '->getTexts() supports checking attribute word matching');
$t->is($c->getTexts('ul#list li a[class~="bar1"]'), array('link'), '->getTexts() supports checking attribute word matching');
$t->is($c->getTexts('ul#list li a[class~="foobar1"]'), array('link'), '->getTexts() supports checking attribute word matching');
$t->is($c->getTexts('ul#list li a[class^="foo1"]'), array('link'), '->getTexts() supports checking attribute starting with');
$t->is($c->getTexts('ul#list li a[class$="foobar1"]'), array('link'), '->getTexts() supports checking attribute ending with');
$t->is($c->getTexts('ul#list li a[class*="oba"]'), array('link'), '->getTexts() supports checking attribute with *');
//$t->is($c->getTexts('ul#list li a[href="http://www.google.com/"]'), array('link'), '->getTexts() supports checking attribute word matching');
$t->is($c->getTexts('ul#anotherlist li a[class|="bar1"]'), array('another link'), '->getTexts() supports checking attribute starting with value followed by optional hyphen');
