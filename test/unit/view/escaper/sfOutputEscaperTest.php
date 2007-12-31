<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../../../lib/vendor/lime/lime.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaper.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperGetterDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperArrayDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperObjectDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperIteratorDecorator.class.php');

require_once(dirname(__FILE__).'/../../../../lib/plugins/sfCompat10Plugin/lib/helper/EscapingHelper.php');
require_once(dirname(__FILE__).'/../../../../lib/config/sfConfig.class.php');

sfConfig::set('sf_charset', 'UTF-8');

$t = new lime_test(10, new lime_output_color());

// ::escape()
$t->diag('::escape()');
$t->is(sfOutputEscaper::escape('esc_entities', null), null, '::escape() returns null if the value to escape is null');
$t->is(sfOutputEscaper::escape('esc_entities', false), false, '::escape() returns false if the value to escape is false');
$t->is(sfOutputEscaper::escape('esc_entities', true), true, '::escape() returns true if the value to escape is true');

$t->is(sfOutputEscaper::escape('esc_raw', '<strong>escaped!</strong>'), '<strong>escaped!</strong>', '::escape() takes an escaping strategy function name as its first argument');

$t->is(sfOutputEscaper::escape('esc_entities', '<strong>escaped!</strong>'), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() returns an escaped string if the value to escape is a string');
$t->is(sfOutputEscaper::escape('esc_entities', '<strong>échappé</strong>'), '&lt;strong&gt;&eacute;chapp&eacute;&lt;/strong&gt;', '::escape() returns an escaped string if the value to escape is a string');

$t->isa_ok(sfOutputEscaper::escape('esc_entities', array(1, 2)), 'sfOutputEscaperArrayDecorator', '::escape() returns a sfOutputEscaperArrayDecorator object if the value to escape is an array');

$t->isa_ok(sfOutputEscaper::escape('esc_entities', new stdClass()), 'sfOutputEscaperObjectDecorator', '::escape() returns a sfOutputEscaperObjectDecorator object if the value to escape is an object');

class OutputEscaperTestClass
{
  public function getTitle()
  {
    return '<strong>escaped!</strong>';
  }
}

$object = new OutputEscaperTestClass();
$escaped_object = sfOutputEscaper::escape('esc_entities', $object);
$t->is(sfOutputEscaper::escape('esc_entities', $escaped_object)->getTitle(), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() does not double escape an object');

$t->isa_ok(sfOutputEscaper::escape('esc_entities', new DirectoryIterator('.')), 'sfOutputEscaperIteratorDecorator', '::escape() returns a sfOutputEscaperIteratorDecorator object if the value to escape is an object that implements the ArrayAccess interface');
