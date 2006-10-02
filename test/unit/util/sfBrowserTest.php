<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(36, new lime_output_color());

// ->click()
$t->diag('->click()');
class myClickBrowser extends sfBrowser
{
  public function setHtml($html)
  {
    $this->dom = new DomDocument('1.0', 'UTF-8');
    $this->dom->validateOnParse = true;
    $this->dom->loadHTML($html);
  }

  public function call($uri, $method = 'get', $parameters = array(), $changeStack = true)
  {
    $this->fields = array();

    return array($method, $uri, $parameters);
  }
}

$html = <<<EOF
<html>
  <body>
    <a href="/mylink">test link</a>
    <form action="/myform" method="post">
      <input type="text" name="text_default_value" value="default" />
      <input type="text" name="text" value="" />
      <textarea name="textarea">content</textarea>
      <select name="select">
        <option value="first">first</option>
        <option value="selected" selected="selected">selected</option>
        <option value="last">last</option>
      </select>
      <select name="select_multiple" multiple="multiple">
        <option value="first">first</option>
        <option value="selected" selected="selected">selected</option>
        <option value="last" selected="selected">last</option>
      </select>
      <input name="article[title]" value="title"/>
      <select name="article[category]" multiple="multiple">
        <option value="1">1</option>
        <option value="2" selected="selected">2</option>
        <option value="3" selected="selected">3</option>
      </select>
      <input name="article[or][much][longer]" value="very long!"/>
      <input type="button" name="mybutton" value="mybuttonvalue" />
      <input type="submit" name="submit" value="submit" />
    </form>

    <form action="/myform1" method="get">
      <input type="text" name="text_default_value" value="default" />
      <input type="submit" name="submit" value="submit1" />
    </form>

    <form action="/myform2">
      <input type="text" name="text_default_value" value="default" />
      <input type="submit" name="submit" value="submit2" />
    </form>

    <form action="/myform3?key=value">
      <input type="text" name="text_default_value" value="default" />
      <input type="submit" name="submit" value="submit3" />
    </form>

    <form action="/myform4">
      <div><span>
        <input type="submit" name="submit" value="submit4" />
      </span></div>
    </form>
  </body>
</html>
EOF;

$b = new myClickBrowser();
$b->setHtml($html);
try
{
  $b->click('nonexistantname');
  $t->fail('->click() throws an error if the name does not exist');
}
catch (Exception $e)
{
  $t->pass('->click() throws an error if the name does not exist');
}

list($method, $uri, $parameters) = $b->click('test link');
$t->is($uri, '/mylink', '->click() clicks on links');

list($method, $uri, $parameters) = $b->click('submit');
$t->is($method, 'post', '->click() gets the form method');
$t->is($uri, '/myform', '->click() clicks on form submit buttons');
$t->is($parameters['text_default_value'], 'default', '->click() uses default form field values (input)');
$t->is($parameters['text'], '', '->click() uses default form field values (input)');
$t->is($parameters['textarea'], 'content', '->click() uses default form field values (textarea)');
$t->is($parameters['select'], 'selected', '->click() uses default form field values (select)');
$t->is($parameters['select_multiple'], array('selected', 'last'), '->click() uses default form field values (select - multiple)');
$t->is($parameters['article']['title'], 'title', '->click() recognizes array names');
$t->is($parameters['article']['category'], array('2', '3'), '->click() recognizes array names');
$t->is($parameters['article']['or']['much']['longer'], 'very long!', '->click() recognizes array names');
$t->is($parameters['submit'], 'submit', '->click() populates button clicked');
$t->ok(!isset($parameters['mybutton']), '->click() do not populate buttons not clicked');

list($method, $uri, $parameters) = $b->click('mybuttonvalue');
$t->is($uri, '/myform', '->click() clicks on form buttons');
$t->is($parameters['text_default_value'], 'default', '->click() uses default form field values');
$t->is($parameters['mybutton'], 'mybuttonvalue', '->click() populates button clicked');
$t->ok(!isset($parameters['submit']), '->click() do not populate buttons not clicked');

list($method, $uri, $parameters) = $b->click('submit1');
$t->is($uri, '/myform1?text_default_value=default&submit=submit1', '->click() clicks on form buttons');
$t->is($method, 'get', '->click() gets the form method');

list($method, $uri, $parameters) = $b->click('submit2');
$t->is($method, 'get', '->click() defaults to get method');

list($method, $uri, $parameters) = $b->click('submit3');
$t->is($uri, '/myform3?key=value&text_default_value=default&submit=submit3', '->click() concatenates fields values with existing action parameters');

list($method, $uri, $parameters) = $b->click('submit4');
$t->is($uri, '/myform4?submit=submit4', '->click() can click on submit button anywhere in a form');

list($method, $uri, $parameters) = $b->click('submit', array(
  'text_default_value' => 'myvalue',
  'text' => 'myothervalue',
  'textarea' => 'mycontent',
  'select' => 'last',
  'select_multiple' => array('first', 'selected', 'last'),
  'article' => array(
    'title' => 'mytitle',
    'category' => array(1, 2, 3),
    'or' => array('much' => array('longer' => 'long')),
  ),
));
$t->is($parameters['text_default_value'], 'myvalue', '->click() takes an array of parameters as its second argument');
$t->is($parameters['text'], 'myothervalue', '->click() can override input fields');
$t->is($parameters['textarea'], 'mycontent', '->click() can override textarea fields');
$t->is($parameters['select'], 'last', '->click() can override select fields');
$t->is($parameters['select_multiple'], array('first', 'selected', 'last'), '->click() can override select (multiple) fields');
$t->is($parameters['article']['title'], 'mytitle', '->click() can override array fields');
$t->is($parameters['article']['category'], array(1, 2, 3), '->click() can override array fields');
$t->is($parameters['article']['or']['much']['longer'], 'long', '->click() recognizes array names');

// ->setField()
$t->diag('->setField()');
list($method, $uri, $parameters) = $b->
  setField('text_default_value', 'myvalue')->
  setField('text', 'myothervalue')->
  setField('article[title]', 'mytitle')->
  click('submit')
;
$t->is($parameters['text_default_value'], 'myvalue', '->setField() overrides default form field values');
$t->is($parameters['text'], 'myothervalue', '->setField() overrides default form field values');
$t->is($parameters['article']['title'], 'mytitle', '->setField() overrides default form field values');

list($method, $uri, $parameters) = $b->
  setField('text_default_value', 'myvalue')->
  setField('text', 'myothervalue')->
  click('submit', array('text_default_value' => 'yourvalue', 'text' => 'yourothervalue'))
;
$t->is($parameters['text_default_value'], 'yourvalue', '->setField() is overriden by parameters from click call');
$t->is($parameters['text'], 'yourothervalue', '->setField() is overriden by parameters from click call');
