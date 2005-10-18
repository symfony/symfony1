<?xml version='1.0' encoding="utf-8"?>
<xsl:stylesheet 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  version="1.0"
>

<xsl:import href="/usr/share/sgml/docbook/xsl-stylesheets-1.65.1-2/fo/docbook.xsl" />
<xsl:include href="http://docbook.sourceforge.net/release/xsl/current/fo/autoidx-ng.xsl" />

<xsl:param name="paper.type" select="'A4'" />
<xsl:param name="use.extensions">0</xsl:param>
<xsl:param name="fop.extensions">0</xsl:param>
<xsl:param name="double.sided">0</xsl:param>
<xsl:param name="header.rule">0</xsl:param>
<xsl:param name="draft.mode">0</xsl:param>
<xsl:param name="header.column.widths" select="'1 1 1'"></xsl:param>
<xsl:param name="footer.column.widths" select="'1 1 1'"></xsl:param>
<xsl:param name="admon.graphics.path">/usr/share/sgml/docbook/xsl-stylesheets-1.65.1-2/images/</xsl:param>
<xsl:param name="admon.graphics.extension" select="'.png'"></xsl:param>

<xsl:attribute-set name="admonition.title.properties">
  <xsl:attribute name="font-size">11pt</xsl:attribute>
  <xsl:attribute name="font-weight">bold</xsl:attribute>
  <xsl:attribute name="font-family">
    <xsl:value-of select="$title.fontset"></xsl:value-of>
  </xsl:attribute>
  <xsl:attribute name="hyphenate">false</xsl:attribute>
  <xsl:attribute name="keep-with-next.within-column">always</xsl:attribute>
  <xsl:attribute name="border">1pt solid #d7d7d7</xsl:attribute>
  <xsl:attribute name="background-color">#f7f7f7</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="admonition.properties">
  <xsl:attribute name="font-size">11pt</xsl:attribute>
  <xsl:attribute name="font-family">
    <xsl:value-of select="$title.fontset"></xsl:value-of>
  </xsl:attribute>
  <xsl:attribute name="border">1pt solid #d7d7d7</xsl:attribute>
  <xsl:attribute name="background-color">#f7f7f7</xsl:attribute>
</xsl:attribute-set>

<xsl:param name="section.autolabel" select="1"></xsl:param>
<xsl:param name="section.label.includes.component.label" select="0"></xsl:param>

<xsl:template name="head.sep.rule">
  <xsl:if test="$header.rule != 0">
    <xsl:attribute name="border-bottom-width">0.5pt</xsl:attribute>
    <xsl:attribute name="border-bottom-style">solid</xsl:attribute>
    <xsl:attribute name="border-bottom-color">black</xsl:attribute>
  </xsl:if>
</xsl:template>

<xsl:template name="foot.sep.rule">
  <xsl:if test="$footer.rule != 0">
    <xsl:attribute name="border-top-width">0.5pt</xsl:attribute>
    <xsl:attribute name="border-top-style">solid</xsl:attribute>
    <xsl:attribute name="border-top-color">black</xsl:attribute>
  </xsl:if>
</xsl:template>

<xsl:template name="header.content">
  <xsl:param name="pageclass" select="''"/>
  <xsl:param name="sequence" select="''"/>
  <xsl:param name="position" select="''"/>
  <xsl:param name="gentext-key" select="''"/>

  <fo:block>
    <xsl:choose>
      <xsl:when test="$position='left'"><xsl:apply-templates select="." mode="title.markup"/></xsl:when>
      <xsl:when test="$position='center'"></xsl:when>
      <xsl:when test="$position='right'"><xsl:call-template name="draft.text"/></xsl:when>
    </xsl:choose>
  </fo:block>
</xsl:template>

<xsl:template name="footer.content">
  <xsl:param name="pageclass" select="''"/>
  <xsl:param name="sequence" select="''"/>
  <xsl:param name="position" select="''"/>
  <xsl:param name="gentext-key" select="''"/>

  <fo:block>
    <xsl:choose>
      <xsl:when test="$position='left'"></xsl:when>
      <xsl:when test="$position='center'"><fo:page-number/></xsl:when>
      <xsl:when test="$position='right'"><xsl:call-template name="draft.text"/></xsl:when>
    </xsl:choose>
  </fo:block>
</xsl:template>

<xsl:attribute-set name="header.content.properties">
  <xsl:attribute name="background-color">#eeeeee</xsl:attribute>
  <xsl:attribute name="color">#333333</xsl:attribute>
  <xsl:attribute name="font-size">8pt</xsl:attribute>
  <xsl:attribute name="font-family">
    <xsl:value-of select="$title.fontset"></xsl:value-of>
  </xsl:attribute>
  <xsl:attribute name="padding">5pt</xsl:attribute>
</xsl:attribute-set>

<xsl:template match="sect1info/abstract" mode="sect1.titlepage.recto.auto.mode">  
  <xsl:attribute name="border">1pt solid #d7d7d7</xsl:attribute>
  <xsl:attribute name="background-color">#f7f7f7</xsl:attribute>
</xsl:template>

<xsl:template match="title" mode="chapter.titlepage.recto.auto.mode">  
  <fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format" 
            xsl:use-attribute-sets="chapter.titlepage.recto.style" 
            margin-left="{$title.margin.left}" 
            font-size="16pt"
            font-weight="bold" 
            font-family="{$title.font.family}">
    <xsl:call-template name="component.title">
      <xsl:with-param name="node" select="ancestor-or-self::chapter[1]"/>
    </xsl:call-template>
  </fo:block>
</xsl:template>

<xsl:attribute-set name="verbatim.properties">
  <xsl:attribute name="font-size">8pt</xsl:attribute>
  <xsl:attribute name="border">1pt solid #d7d7d7</xsl:attribute>
  <xsl:attribute name="background-color">#f7f7f7</xsl:attribute>
  <xsl:attribute name="margin-left">5pt</xsl:attribute>
  <xsl:attribute name="margin-top">5pt</xsl:attribute>
  <xsl:attribute name="margin-bottom">5pt</xsl:attribute>
  <xsl:attribute name="padding">5pt</xsl:attribute>
  <xsl:attribute name="wrap-option">wrap</xsl:attribute>
  <xsl:attribute name="hyphenation-character">\</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="section.title.level1.properties">
  <xsl:attribute name="font-size">13pt</xsl:attribute>
  <xsl:attribute name="border-bottom-style">solid</xsl:attribute>
  <xsl:attribute name="padding-bottom">5pt</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="section.title.level2.properties">
  <xsl:attribute name="font-size">12pt</xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="footer.content.properties">
  <xsl:attribute name="font-size">8pt</xsl:attribute>
  <xsl:attribute name="font-family">
    <xsl:value-of select="$title.fontset"></xsl:value-of>
  </xsl:attribute>
  <xsl:attribute name="margin-left">0pt</xsl:attribute>
</xsl:attribute-set>

</xsl:stylesheet>
