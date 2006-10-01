<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Text'));

$t = new lime_test(33, new lime_output_color());

// text_truncate()
$t->diag('text_truncate()');
$t->is(truncate_text('Test'), 'Test', 'text_truncate() truncates to 30 characters by default');

$text = str_repeat('A', 35);
$truncated = str_repeat('A', 27).'...';
$t->is(truncate_text($text), $truncated, 'text_truncate() adds ... to truncated text');

$text = str_repeat('A', 35);
$truncated = str_repeat('A', 22).'...';
$t->is(truncate_text($text, 25), $truncated, 'text_truncate() takes the max length as its second argument');

$text = str_repeat('A', 35);
$truncated = str_repeat('A', 21).'BBBB';
$t->is($truncated, truncate_text($text, 25, 'BBBB'), 'text_truncate() takes the ... text as its third argument');

// text_highlighter()
$t->diag('text_highlighter()');
$t->is(highlight_text("This is a beautiful morning", "beautiful"),
  "This is a <strong class=\"highlight\">beautiful</strong> morning",
  'text_highlighter() highlights a word given as its second argument'
);

$t->is(highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful"),
  "This is a <strong class=\"highlight\">beautiful</strong> morning, but also a <strong class=\"highlight\">beautiful</strong> day",
  'text_highlighter() highlights all occurrences of a word given as its second argument'
);

$t->is(highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful", '<b>\\1</b>'),
  "This is a <b>beautiful</b> morning, but also a <b>beautiful</b> day",
  'text_highlighter() takes a pattern as its third argument'
);

$t->is(highlight_text('', 'beautiful'), '', 'text_highlighter() returns an empty string if input is empty');
$t->is(highlight_text('', ''), '', 'text_highlighter() returns an empty string if input is empty');
$t->is(highlight_text('foobar', 'beautiful'), 'foobar', 'text_highlighter() does nothing is string to highlight is not present');
$t->is(highlight_text('foobar', ''), 'foobar', 'text_highlighter() returns input if string to highlight is not present');

$t->is(highlight_text("This is a beautiful! morning", "beautiful!"), "This is a <strong class=\"highlight\">beautiful!</strong> morning", 'text_highlighter() escapes search string to be safe in a regex');
$t->is(highlight_text("This is a beautiful! morning", "beautiful! morning"), "This is a <strong class=\"highlight\">beautiful! morning</strong>", 'text_highlighter() escapes search string to be safe in a regex');
$t->is(highlight_text("This is a beautiful? morning", "beautiful? morning"), "This is a <strong class=\"highlight\">beautiful? morning</strong>", 'text_highlighter() escapes search string to be safe in a regex');

// text_excerpt()
$t->diag('text_excerpt()');
$t->is(excerpt_text("This is a beautiful morning", "beautiful", 5), "...is a beautiful morn...", 'text_excerpt() creates an excerpt of a text');
$t->is(excerpt_text("This is a beautiful morning", "this", 5), "This is a...", 'text_excerpt() creates an excerpt of a text');
$t->is(excerpt_text("This is a beautiful morning", "morning", 5), "...iful morning", 'text_excerpt() creates an excerpt of a text');
$t->is(excerpt_text("This is a beautiful morning", "morning", 5), "...iful morning", 'text_excerpt() creates an excerpt of a text');
$t->is(excerpt_text("This is a beautiful morning", "day"), '', 'text_excerpt() does nothing if the search string is not in input');

// text_simple_format()
$t->diag('text_simple_format()');
$t->is(simple_format_text("crazy\r\n cross\r platform linebreaks"), "<p>crazy\n<br /> cross\n<br /> platform linebreaks</p>", 'text_simple_format() replaces \n by <br />');
$t->is(simple_format_text("A paragraph\n\nand another one!"), "<p>A paragraph</p>\n\n<p>and another one!</p>", 'text_simple_format() replaces \n\n by <p>');
$t->is(simple_format_text("A paragraph\n With a newline"), "<p>A paragraph\n<br /> With a newline</p>", 'text_simple_format() wrap all string with <p>');

// text_strip_links()
$t->diag('text_strip_links()');
$t->is(strip_links_text("<a href='almost'>on my mind</a>"), "on my mind", 'text_strip_links() strips all links in input');

// auto_linking()
$t->diag('auto_linking()');
$email_raw = 'fabien.potencier@symfony-project.com.com';
$email_result = '<a href="mailto:'.$email_raw.'">'.$email_raw.'</a>';
$link_raw = 'http://www.google.com';
$link_result = '<a href="'.$link_raw.'">'.$link_raw.'</a>';
$link2_raw = 'www.google.com';
$link2_result = '<a href="http://'.$link2_raw.'">'.$link2_raw.'</a>';

$t->is(auto_link_text('hello '.$email_raw, 'email_addresses'), 'hello '.$email_result, 'auto_linking() converts emails to links');
$t->is(auto_link_text('Go to '.$link_raw, 'urls'), 'Go to '.$link_result, 'auto_linking() converts absolute URLs to links');
$t->is(auto_link_text('Go to '.$link_raw, 'email_addresses'), 'Go to '.$link_raw, 'auto_linking() takes a second parameter');
$t->is(auto_link_text('Go to '.$link_raw.' and say hello to '.$email_raw), 'Go to '.$link_result.' and say hello to '.$email_result, 'auto_linking() converts emails and URLs if no second argument is given');
$t->is(auto_link_text('<p>Link '.$link_raw.'</p>'), '<p>Link '.$link_result.'</p>', 'auto_linking() converts URLs to links');
$t->is(auto_link_text('<p>'.$link_raw.' Link</p>'), '<p>'.$link_result.' Link</p>', 'auto_linking() converts URLs to links');
$t->is(auto_link_text('Go to '.$link2_raw, 'urls'), 'Go to '.$link2_result, 'auto_linking() converts URLs to links even if link does not start with http://');
$t->is(auto_link_text('Go to '.$link2_raw, 'email_addresses'), 'Go to '.$link2_raw, 'auto_linking() converts URLs to links');
$t->is(auto_link_text('<p>Link '.$link2_raw.'</p>'), '<p>Link '.$link2_result.'</p>', 'auto_linking() converts URLs to links');
$t->is(auto_link_text('<p>'.$link2_raw.' Link</p>'), '<p>'.$link2_result.' Link</p>', 'auto_linking() converts URLs to links');
