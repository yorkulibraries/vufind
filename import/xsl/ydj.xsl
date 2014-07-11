<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:nlm="http://dtd.nlm.nih.gov/publishing/2.3"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:mml="http://www.w3.org/1998/Math/MathML"
    >
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">York University Libraries</xsl:param>
    <xsl:param name="collection">York Digital Journals</xsl:param>
    <xsl:template match="nlm:article">
        <add>
            <doc>
                <!-- ID -->
                <!-- Important: This relies on an <identifier> tag being injected by the OAI-PMH harvester. -->
                <field name="id">
                    <xsl:value-of select="nlm:identifier"/>
                </field>

		        <!-- CHANGE TRACKING DATES -->
		        <xsl:if test="$track_changes != 0">
		            <field name="first_indexed">
		                <xsl:value-of select="php:function('YorkVuFind::getFirstIndexed', $solr_core, string(nlm:identifier))" />
		            </field>
		            <field name="last_indexed">
		                <xsl:value-of select="php:function('YorkVuFind::getLastIndexed', $solr_core, string(nlm:identifier))" />
		            </field>
		        </xsl:if>
        
                <!-- RECORDTYPE -->
                <field name="recordtype">NLMOJS</field>
                
                <field name="source_str">York Digital Journals</field>
                
                <!-- ALLFIELDS -->
                <field name="allfields">
                    <xsl:value-of select="normalize-space(string(.))"/>
                </field>

                <!-- INSTITUTION -->
                <field name="institution">
                    <xsl:value-of select="$institution" />
                </field>

                <!-- COLLECTION -->
                <field name="collection">
                    <xsl:value-of select="//nlm:journal-title" />
                </field>
                
                <!-- LOCATION -->
                <field name="location_str_mv">
                    <xsl:value-of select="php:function('VuFind::mapString', 'INTERNET', 'location_code_location_map.properties')" />
                </field>
                
                <!-- BUILDING -->
                <field name="building">
                    <xsl:value-of select="php:function('VuFind::mapString', 'INTERNET', 'location_code_building_map.properties')" />
                </field>

                <!-- LANGUAGE -->
                <field name="language">
                    <xsl:value-of select="php:function('VuFind::mapString', php:function('strtolower', string(@xml:lang)), 'language_map_iso639-1.properties')" />
                </field>

                <!-- FORMAT -->
                <field name="format">Article</field>
                                
                <!-- ISSN -->
                <xsl:for-each select="//nlm:issn">
                    <field name="issn">
                        <xsl:value-of select="normalize-space()"/>
                    </field>
                </xsl:for-each>

                <!-- SUBJECT -->
                <!--
                <xsl:for-each select="//nlm:subject">
                    <field name="topic">
                        <xsl:value-of select="normalize-space()"/>
                    </field>
                    <field name="topic_facet">
                        <xsl:value-of select="normalize-space()"/>
                    </field>
                </xsl:for-each>
                -->

                <!-- DESCRIPTION -->
                <xsl:if test="//nlm:abstract/nlm:p">
                    <field name="description">
                        <xsl:value-of select="//nlm:abstract/nlm:p" />
                    </field>
                </xsl:if>

                <!-- ADVISOR / CONTRIBUTOR -->
                <xsl:for-each select="//nlm:contrib[@contrib-type='editor']/nlm:name">
                    <xsl:if test="nlm:surname[normalize-space()] != '' and nlm:surname != 'Administrator'">
                    <field name="author_additional">
                        <xsl:value-of select="nlm:surname[normalize-space()]" />, <xsl:value-of select="nlm:given-names[normalize-space()]" />
                    </field>
                    </xsl:if>
                </xsl:for-each>

                <!-- AUTHOR -->
                <xsl:for-each select="//nlm:contrib[@contrib-type='author']/nlm:name">
                        <xsl:if test="nlm:surname[normalize-space()] != '' and nlm:surname != 'Administrator'">
                            <!-- author is not a multi-valued field, so we'll put
                                 first value there and subsequent values in author2.
                             -->
                            <xsl:if test="position()=1">
                                <field name="author">
                                    <xsl:value-of select="nlm:surname[normalize-space()]" />, <xsl:value-of select="nlm:given-names[normalize-space()]" />
                                </field>
                                <field name="author-letter">
                                    <xsl:value-of select="nlm:surname[normalize-space()]" />, <xsl:value-of select="nlm:given-names[normalize-space()]" />
                                </field>
                            </xsl:if>
                            <xsl:if test="position()>1">
                                <field name="author2">
                                    <xsl:value-of select="nlm:surname[normalize-space()]" />, <xsl:value-of select="nlm:given-names[normalize-space()]" />
                                </field>
                            </xsl:if>
                            <field name="author_facet_txtF_mv">
                                <xsl:value-of select="nlm:surname[normalize-space()]" />, <xsl:value-of select="nlm:given-names[normalize-space()]" />
                            </field>
                        </xsl:if>
                </xsl:for-each>

                <!-- TITLE -->
                <field name="title">
                    <xsl:value-of select="//nlm:article-title[normalize-space()]"/>
                </field>
                <field name="title_txtP">
                    <xsl:value-of select="//nlm:article-title[normalize-space()]"/>
                </field>
                <field name="title_short">
                    <xsl:value-of select="//nlm:article-title[normalize-space()]"/>
                </field>
                <field name="title_short_txtP">
                    <xsl:value-of select="//nlm:article-title[normalize-space()]"/>
                </field>
                <field name="title_full">
                    <xsl:value-of select="//nlm:article-title[normalize-space()]"/>
                </field>
                <field name="title_sort">
                    <xsl:value-of select="php:function('VuFind::stripArticles', string(//nlm:article-title[normalize-space()]))"/>
                </field>
                <field name="title_alt">
                    <xsl:value-of select="//nlm:trans-title[normalize-space()]"/>
                </field>

                <!-- PUBLISHER -->
                <xsl:if test="//nlm:publisher-name">
                    <field name="publisher">
                        <xsl:value-of select="//nlm:publisher-name[normalize-space()]"/>
                    </field>
                </xsl:if>

                <!-- PUBLISHDATE -->
                <xsl:if test="//nlm:pub-date/nlm:year">
                    <field name="publishDate">
                        <xsl:value-of select="//nlm:pub-date/nlm:year"/>
                    </field>
                </xsl:if>

                <!-- URL -->
                <xsl:for-each select="//nlm:self-uri">
                   <field name="url">
                       <xsl:value-of select="@xlink:href" />
                   </field>
                </xsl:for-each>

                <!-- FULL TEXT -->
                <xsl:choose>
                    <xsl:when test="nlm:body/nlm:p">
                       <field name="fulltext">
                           <xsl:value-of select="nlm:body/nlm:p" />
                       </field>
                    </xsl:when>
                    <xsl:otherwise>
                        <!-- we don't need to harvest OJS with Aperture since the fulltext is already part of the NLM metadata
                        <xsl:for-each select="//nlm:self-uri[@content-type=&quot;application/pdf&quot;]">
                            <field name="fulltext">
                                <xsl:value-of select="php:function('VuFind::harvestWithAperture', string(./@xlink:href))"/>
                            </field>
                        </xsl:for-each>
                        -->
                        <field name="fulltext">FULLTEXTNOTAVAILABLE</field>
                    </xsl:otherwise>
                </xsl:choose>

                <!-- FULLRECORD (exclude body tag because fulltext is huge) -->
                <field name="fullrecord">
                    <xsl:copy-of select="php:function('VuFind::removeTagAndReturnXMLasText', ., 'body')"/>
                </field>
            </doc>
        </add>
    </xsl:template>
</xsl:stylesheet>
