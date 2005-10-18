<?xml version='1.0'?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">

  <xsl:param name="admon.graphics" select="'1'"/>
  <xsl:param name="admon.graphics.path">images/</xsl:param>
  <xsl:param name="admon.textlabel" select="0"></xsl:param>

  <xsl:param name="navig.graphics" select="1"></xsl:param>
  <xsl:param name="navig.graphics.path">images/</xsl:param>
  <xsl:param name="navig.showtitles" select="1"/>

  <xsl:param name="chapter.autolabel" select="1"></xsl:param>
  <xsl:param name="section.autolabel" select="1"></xsl:param>
  <xsl:param name="section.label.includes.component.label" select="1"></xsl:param>

  <xsl:param name="chunk.section.depth" select="0"></xsl:param>
  <xsl:param name="html.stylesheet" select="'book.css'"/>

  <xsl:template name="user.header.content">
  <div class="myheader">SymFony - Guide de référence</div>
  </xsl:template>

  <xsl:template name="user.footer.content">
  <div class="myfooter">(c) 2004-2005 Fabien POTENCIER</div>
  </xsl:template>
</xsl:stylesheet>
