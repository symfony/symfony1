<?php

require_once 'helper/TextHelper.php';

Mock::generate('sfContext');

class sfTextHelperTest extends UnitTestCase
{
  private $context;

  private static $TruncateTexts = array(
    'Test' => 'Test'
  );

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
  }

    public function test_text_truncate()
    {
    $this->assertEqual('Test', truncate_text('Test'));
    foreach (sfTextHelperTest::$TruncateTexts as $text => $truncated)
    {
      $this->assertEqual($truncated, truncate_text($text));
    }

    $text = str_repeat('A', 35);
    $truncated = str_repeat('A', 27).'...';
    $this->assertEqual($truncated, truncate_text($text));

    $text = str_repeat('A', 35);
    $truncated = str_repeat('A', 22).'...';
    $this->assertEqual($truncated, truncate_text($text, 25));

    $text = str_repeat('A', 35);
    $truncated = str_repeat('A', 21).'BBBB';
    $this->assertEqual($truncated, truncate_text($text, 25, 'BBBB'));
    }

  public function test_text_highlighter()
  {
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful</strong> morning",
      highlight_text("This is a beautiful morning", "beautiful"));

    $this->assertEqual("This is a <strong class=\"highlight\">beautiful</strong> morning, but also a <strong class=\"highlight\">beautiful</strong> day",
      highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful"));

    $this->assertEqual("This is a <b>beautiful</b> morning, but also a <b>beautiful</b> day",
      highlight_text("This is a beautiful morning, but also a beautiful day", "beautiful", '<b>\\1</b>'));

    $this->assertEqual('', highlight_text('', 'beautiful'));
    $this->assertEqual('', highlight_text('', ''));
    $this->assertEqual('foobar', highlight_text('foobar', 'beautiful'));
    $this->assertEqual('foobar', highlight_text('foobar', ''));
  }

  public function test_text_highlighter_with_regexp()
  {
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful!</strong> morning", highlight_text("This is a beautiful! morning", "beautiful!"));
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful! morning</strong>", highlight_text("This is a beautiful! morning", "beautiful! morning"));
    $this->assertEqual("This is a <strong class=\"highlight\">beautiful? morning</strong>", highlight_text("This is a beautiful? morning", "beautiful? morning"));
  }

  public function test_text_excerpt()
  {
    $this->assertEqual("...is a beautiful morn...", excerpt_text("This is a beautiful morning", "beautiful", 5));
    $this->assertEqual("This is a...", excerpt_text("This is a beautiful morning", "this", 5));
    $this->assertEqual("...iful morning", excerpt_text("This is a beautiful morning", "morning", 5));
    $this->assertEqual("...iful morning", excerpt_text("This is a beautiful morning", "morning", 5));
    $this->assertEqual('', excerpt_text("This is a beautiful morning", "day"));
  }

  public function test_text_simple_format()
  {
    $this->assertEqual("<p>crazy\n<br /> cross\n<br /> platform linebreaks</p>", simple_format_text("crazy\r\n cross\r platform linebreaks"));
    $this->assertEqual("<p>A paragraph</p>\n\n<p>and another one!</p>", simple_format_text("A paragraph\n\nand another one!"));
    $this->assertEqual("<p>A paragraph\n<br /> With a newline</p>", simple_format_text("A paragraph\n With a newline"));
  }

  public function test_text_strip_links()
  {
    $this->assertEqual("on my mind", strip_links_text("<a href='almost'>on my mind</a>"));
  }

  public function test_auto_linking()
  {
    $email_raw = 'fabien.potencier@symfony-project.com.com';
    $email_result = '<a href="mailto:'.$email_raw.'">'.$email_raw.'</a>';
    $link_raw = 'http://www.google.com';
    $link_result = '<a href="'.$link_raw.'">'.$link_raw.'</a>';
    $link2_raw = 'www.google.com';
    $link2_result = '<a href="http://'.$link2_raw.'">'.$link2_raw.'</a>';

    $this->assertEqual('hello '.$email_result, auto_link_text('hello '.$email_raw, 'email_addresses'));
    $this->assertEqual('Go to '.$link_result, auto_link_text('Go to '.$link_raw, 'urls'));
    $this->assertEqual('Go to '.$link_raw, auto_link_text('Go to '.$link_raw, 'email_addresses'));
    $this->assertEqual('Go to '.$link_result.' and say hello to '.$email_result, auto_link_text('Go to '.$link_raw.' and say hello to '.$email_raw));
    $this->assertEqual('<p>Link '.$link_result.'</p>', auto_link_text('<p>Link '.$link_raw.'</p>'));
    $this->assertEqual('<p>'.$link_result.' Link</p>', auto_link_text('<p>'.$link_raw.' Link</p>'));
    $this->assertEqual('Go to '.$link2_result, auto_link_text('Go to '.$link2_raw, 'urls'));
    $this->assertEqual('Go to '.$link2_raw, auto_link_text('Go to '.$link2_raw, 'email_addresses'));
    $this->assertEqual('<p>Link '.$link2_result.'</p>', auto_link_text('<p>Link '.$link2_raw.'</p>'));
    $this->assertEqual('<p>'.$link2_result.' Link</p>', auto_link_text('<p>'.$link2_raw.' Link</p>'));
  }
}
