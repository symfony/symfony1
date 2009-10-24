<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE xsl:stylesheet [<!ENTITY nbsp "&#160;">]>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="##PROPEL_XSL##"/>

	<xsl:template match='/database'>
		<database>
      <!-- todo: can we override the propel xsl without having to copy this template? -->
			<xsl:if test='not(boolean(@defaultIdMethod))'>
				<xsl:attribute name='defaultIdMethod'>native</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@defaultPhpNamingMethod))'>
				<xsl:attribute name='defaultPhpNamingMethod'>underscore</xsl:attribute>
			</xsl:if>
			<xsl:if test='not(boolean(@heavyIndexing))'>
				<xsl:attribute name='heavyIndexing'>false</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select='@*'/>
			<xsl:apply-templates select='external-schema'/>
			<xsl:apply-templates select='table'/>
			<xsl:apply-templates select='behavior'/>

      <!-- add symfony global behaviors -->
      <behavior name="symfony"/>
      <behavior name="symfony_i18n"/>
		</database>
	</xsl:template>
</xsl:stylesheet>
