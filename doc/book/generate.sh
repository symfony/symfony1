#!/bin/sh

TYPES='htmlhelp xhtml'
for type in $TYPES
do
  echo "Generating " $type
  rm -rf $type
  xmlto --extensions -m stylesheets/book1.xsl -o $type $type book.xml
  cp stylesheets/book.css $type/
  mkdir $type/images
  cp -a /usr/share/sgml/docbook/docbook-xsl-1.68.1/images/* $type/images/.
  tar zcpf $type.tgz $type
done

echo "Generating XHTML (no chunks)"
xmlto --extensions -m stylesheets/book1.xsl xhtml-nochunks book.xml

echo "Generating PDF"
xmlto --extensions -x stylesheets/book.xsl pdf book.xml
