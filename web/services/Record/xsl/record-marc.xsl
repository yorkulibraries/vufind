<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                              xmlns:marc="http://www.loc.gov/MARC21/slim"
  exclude-result-prefixes="marc">
  
  <xsl:output method="html" indent="yes"/>
	
  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>
	
  <xsl:template match="marc:record">
    <div class="table-responsive">
      <table border="0" class="table table-condensed table-hover table-bordered citation">
        <tr>
          <th>LEADER</th>
          <td colspan="3"><xsl:value-of select="//marc:leader"/></td>
        </tr>
		<xsl:apply-templates select="marc:datafield|marc:controlfield"/>
      </table>
      <br/>
    </div>
  </xsl:template>
	
  <xsl:template match="//marc:controlfield">
      <tr>
        <th>
          <xsl:value-of select="@tag"/>
        </th>
        <td colspan="3"><xsl:value-of select="."/></td>
      </tr>
  </xsl:template>
	
  <xsl:template match="//marc:datafield">
    <xsl:if test="not(@tag='999' or @tag='949')">
      <tr>
        <th>
          <xsl:value-of select="@tag"/>
        </th>
        <td><xsl:value-of select="@ind1"/></td>
        <td><xsl:value-of select="@ind2"/></td>
        <td>
          <xsl:apply-templates select="marc:subfield"/>
        </td>
      </tr>
    </xsl:if>
	</xsl:template>
	
	<xsl:template match="marc:subfield">
      <strong>|<xsl:value-of select="@code"/></strong>&#160;<xsl:value-of select="."/>&#160;
	</xsl:template>

</xsl:stylesheet>