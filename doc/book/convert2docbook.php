<?php

// Convert a markdown set of files to a docbook document

require_once(dirname(__FILE__).'/markdown.php');
require_once(dirname(__FILE__).'/../../lib/spyc/spyc.php');

$spyc = new Spyc();
$config = $spyc->load(dirname(__FILE__).'/book.yml');

$product_name    = 'symfony';
$product_version = '1.0RC1';

# $bom = chr(hex('EF')).chr(hex('BB')).chr(hex('BF'));
$book = <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE book PUBLIC "-//OASIS//DTD DocBook XML V4.3//EN" "http://www.oasis-open.org/docbook/xml/4.3/docbookx.dtd"
[
  <!ENTITY product "<productname>$product_name</productname>">
  <!ENTITY version "<version>$product_version</version>">
  <!ENTITY % xinclude PUBLIC "-//NONAME//ELEMENTS DocBook XInclude extension//EN" "xinclude.mod">
  %xinclude;
]>

<book id="symfony" lang="en">
 
<bookinfo>
<title>&product;</title>
<subtitle>Guide de référence</subtitle>
<authorgroup>
  <author>
    <firstname>Fabien</firstname>
    <surname>POTENCIER</surname>
    <affiliation>
      <orgname>SENSIO</orgname>
      <address>
        <email>fabien.potencier@sensio.com</email>
      </address>
    </affiliation>
  </author>
  <author>
    <firstname>François</firstname>
    <surname>ZANINOTTO</surname>
    <affiliation>
      <orgname>SENSIO</orgname>
      <address>
        <email>francois.zaninotto@sensio.com</email>
      </address>
    </affiliation>
  </author>
</authorgroup>
<copyright>
  <year>2005</year>
  <holder role="mailto:fabien.potencier@symfony-project">Fabien POTENCIER</holder>
</copyright>
<pubdate role="rcs">\$Date\$</pubdate>
<edition>Beta</edition>
<releaseinfo>\$Id\$</releaseinfo>
<abstract>
  <para>
  </para>

  <para>
  <note><para>Note</para></note>
  <warning><para>Attention</para></warning>
  <important><para>Important à retenir</para></important>
  <tip><para>Truc et astuce</para></tip>
  <caution><para>Faites attention</para></caution>
  </para>
</abstract>
</bookinfo>
EOF;

// clean xml directory
$xml_dir = dirname(__FILE__).'/xml_content';
if (is_dir($xml_dir))
{
  $fp = opendir($xml_dir);

  while (($file = readdir($fp)) !== false)
  {
    // delete the file
    if (strpos($file, '.xml'))
    {
      unlink($xml_dir.'/'.$file);
    }
  }

  // close file pointer
  fclose($fp);
  rmdir($xml_dir);
}

mkdir($xml_dir);

foreach ($config['parts'] as $part => $chapters)
{
  echo "Generating '$part' part\n";

  $book .= "<part><title>$part</title>\n";
  foreach ($chapters as $chapter)
  {
    echo "   Generating '$chapter' chapter\n";
    $chapter_path = dirname(__FILE__).'/xml_content/'.$chapter.'.xml';
    $chapter_filename = dirname(__FILE__).'/content/'.$chapter.'.txt';
    if (is_readable($chapter_filename))
    {
      $content = markdown(file_get_contents($chapter_filename));

      $content = preg_replace('#<h1>(.+?)</h1>#si', '<title>$1</title>', $content);
      $content = preg_replace('#<p>(.+?)</p>#si', '<para>$1</para>', $content);
      $content = preg_replace('#<h2>Overview</h2>(.+?)\s*((?=<h2>)|$)#si', '<abstract>$1</abstract>', $content);
      $content = preg_replace('#<h3>(.+?)</h3>(.+?)\s*((?=<h3>)|(?=<h2>)|$)#si', '<sect2><title>$1</title>$2</sect2>', $content);
      $content = preg_replace('#<h2>(.+?)</h2>(.+?)\s*((?=<h2>)|$)#si', '<sect1><title>$1</title>$2</sect1>', $content);
      $content = preg_replace('#<pre(\s.*?)?>(.+?)\s*</pre>#si', '<programlisting>$2</programlisting>', $content);
      $content = preg_replace('#<ol(\s.*?)?>(.+?)</ol>#si', '<orderedlist numeration="arabic">$2</orderedlist>', $content);
      $content = preg_replace('#<ul(\s.*?)?>(.+?)</ul>#si', '<itemizedlist>$2</itemizedlist>', $content);
      $content = preg_replace('#<li(\s.*?)?>(.+?)</li>#si', '<listitem>$2</listitem>', $content);
      $content = preg_replace('#&lt;IMPORTANT&gt;(.+?)&lt;/IMPORTANT&gt;#si', '<important><para>$1</para></important>', $content);
      $content = preg_replace('#&lt;TIP&gt;(.+?)&lt;/TIP&gt;#si', '<tip><para>$1</para></tip>', $content);
      $content = preg_replace('#&lt;WARNING&gt;(.+?)&lt;/WARNING&gt;#si', '<warning><para>$1</para></warning>', $content);
      $content = preg_replace('#&lt;NOTE&gt;(.+?)&lt;/NOTE&gt;#si', '<note><para>$1</para></note>', $content);
      $content = preg_replace('#&lt;CAUTION&gt;(.+?)&lt;/CAUTION&gt;#si', '<caution><para>$1</para></caution>', $content);
      $content = preg_replace('#<strong(\s.*?)?>(.+?)\s*</strong>#si', '<function>$2</function>', $content);
      $content = preg_replace('#<i(\s.*?)?>(.+?)\s*</i>#si', '<parameter>$2</parameter>', $content);
      $content = preg_replace('#</?(a|span|em)(\s.*?)?>#si', '', $content);
      $content = preg_replace('#symfony#si', '&product;', $content);

      $content = preg_replace('#<abstract>\s*</abstract>#si', '<abstract><para></para></abstract>', $content);

      $content = preg_replace('#<listitem>(?!<para>)(.+?)</listitem>#si', '<listitem><para>$2</para></listitem>', $content);

      // delete br
      $content = preg_replace('#<br\s*/?>#si', '', $content);

      // delete para in para
      $content = preg_replace('#<para><para>([^(</?para>)]*)</para></para>#si', '<para>$1</para>', $content);

      // on supprime tout ce que se trouve avant le titre principal
      $content = preg_replace('#^(.*?)<title>#si', '<title>', $content);

      $content = <<<EOF
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE chapter PUBLIC "-//OASIS//DTD DocBook XML V4.3//EN" "http://www.oasis-open.org/docbook/xml/4.3/docbookx.dtd"
[
  <!ENTITY product "<productname>$product_name</productname>">
  <!ENTITY version "<version>$product_version</version>">
]>
<chapter id="chapter_$chapter">
$content
</chapter>
EOF;

      file_put_contents($chapter_path, $content);
    }

    $book .= <<<EOF
<xi:include href="$chapter_path" xmlns:xi="http://www.w3.org/2001/XInclude">
  <xi:fallback>
  </xi:fallback>
</xi:include>
EOF;
  }

  $book .= '</part>';
}

$book .= '
</book>
';

file_put_contents(dirname(__FILE__).'/book.xml', $book);

?>