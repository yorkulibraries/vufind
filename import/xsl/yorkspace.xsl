<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:php="http://php.net/xsl"
    xmlns:xlink="http://www.w3.org/2001/XMLSchema-instance">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">York University Libraries</xsl:param>
    <xsl:param name="collection">YorkSpace</xsl:param>
    <xsl:template match="oai_dc:dc">
        <add>
            <doc>
                <!-- ID -->
                <!-- Important: This relies on an <identifier> tag being injected by the OAI-PMH harvester. -->
                <field name="id">
                    <xsl:value-of select="//identifier"/>
                </field>

                <!-- CHANGE TRACKING DATES -->
                <xsl:if test="$track_changes != 0">
                    <field name="first_indexed">
                        <xsl:value-of select="php:function('YorkVuFind::getFirstIndexed', $solr_core, string(//identifier))" />
                    </field>
                    <field name="last_indexed">
                        <xsl:value-of select="php:function('YorkVuFind::getLastIndexed', $solr_core, string(//identifier))" />
                    </field>
                </xsl:if>
                
                <field name="source_str">YorkSpace</field>
                
                <!-- RECORDTYPE -->
                <field name="recordtype">dspace</field>

                <!-- FULLRECORD -->
                <field name="fullrecord">
                    <xsl:copy-of select="php:function('VuFind::xmlAsText', //oai_dc:dc)"/>
                </field>

                <!-- ALLFIELDS -->
                <field name="allfields">
                    <xsl:value-of select="normalize-space(string(//oai_dc:dc))"/>
                </field>

                <!-- INSTITUTION -->
                <field name="institution">
                    <xsl:value-of select="$institution" />
                </field>

                <!-- COLLECTION -->
                <field name="collection">
                    <xsl:value-of select="$collection" />
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
                <xsl:if test="//dc:language">
                    <xsl:for-each select="//dc:language">
                        <xsl:if test="string-length() > 0">
                            <field name="language">
                                <xsl:value-of select="php:function('YorkVuFind::getYorkSpaceLanguage', normalize-space(string(.)), 'language_map_iso639-1.properties')"/>
                            </field>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>

                <!-- FORMAT -->
                <!-- populating the format field with dc.type instead, see TYPE below.
                     if you like, you can uncomment this to add a hard-coded format
                     in addition to the dynamic ones extracted from the record.
                <field name="format">Online</field>
                -->

                <!-- SUBJECT -->
                <!--
                <xsl:if test="//dc:subject">
                    <xsl:for-each select="//dc:subject">
                        <xsl:if test="string-length() > 0">
                            <field name="topic">
                                <xsl:value-of select="normalize-space()"/>
                            </field>
                            <field name="topic_facet">
                                <xsl:value-of select="normalize-space()"/>
                            </field>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>
                -->

                <!-- DESCRIPTION -->
                <xsl:if test="//dc:description">
                    <field name="description">
                        <xsl:value-of select="//dc:description" />
                    </field>
                </xsl:if>

                <!-- ADVISOR / CONTRIBUTOR -->
                <xsl:if test="//dc:contributor[normalize-space()]">
                    <field name="author_additional">
                        <xsl:value-of select="//dc:contributor[normalize-space()]" />
                    </field>
                    <field name="author_facet_txtF_mv">
                        <xsl:value-of select="//dc:contributor[normalize-space()]" />
                    </field>
                </xsl:if>
                
                <!-- FORMAT -->
                <xsl:if test="//dc:type">
                    <field name="format">
                        <xsl:value-of select="php:function('YorkVuFind::getYorkSpaceFormat', string(//dc:type))" />
                    </field>
                </xsl:if>
                
                <!-- BROAD FORMAT -->
                <xsl:if test="php:function('YorkVuFind::mapString', php:function('YorkVuFind::getYorkSpaceFormat', string(//dc:type)), 'broad_format_map.properties')">
                    <field name="broad_format_str_mv">
                        <xsl:value-of select="php:function('YorkVuFind::mapString', php:function('YorkVuFind::getYorkSpaceFormat', string(//dc:type)), 'broad_format_map.properties')" />
                    </field>
                </xsl:if>

                <!-- AUTHOR -->
                <xsl:if test="//dc:creator">
                    <xsl:for-each select="//dc:creator">
                        <xsl:if test="normalize-space()">
                            <!-- author is not a multi-valued field, so we'll put
                                 first value there and subsequent values in author2.
                             -->
                            <xsl:if test="position()=1">
                                <field name="author">
                                    <xsl:value-of select="normalize-space()"/>
                                </field>
                                <field name="author-letter">
                                    <xsl:value-of select="normalize-space()"/>
                                </field>
                            </xsl:if>
                            <xsl:if test="position()>1">
                                <field name="author2">
                                    <xsl:value-of select="normalize-space()"/>
                                </field>
                            </xsl:if>
                            <field name="author_facet_txtF_mv">
                                <xsl:value-of select="normalize-space()"/>
                            </field>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>

                <!-- TITLE -->
                <xsl:if test="//dc:title[normalize-space()]">
                    <field name="title">
                        <xsl:value-of select="//dc:title[normalize-space()]"/>
                    </field>
                    <field name="title_txtP">
                        <xsl:value-of select="//dc:title[normalize-space()]"/>
                    </field>
                    <field name="title_short">
                        <xsl:value-of select="//dc:title[normalize-space()]"/>
                    </field>
                    <field name="title_short_txtP">
                        <xsl:value-of select="//dc:title[normalize-space()]"/>
                    </field>
                    <field name="title_full">
                        <xsl:value-of select="//dc:title[normalize-space()]"/>
                    </field>
                    <field name="title_sort">
                        <xsl:value-of select="php:function('VuFind::stripArticles', string(//dc:title[normalize-space()]))"/>
                    </field>
                </xsl:if>

                <!-- PUBLISHER -->
                <xsl:if test="//dc:publisher[normalize-space()]">
                    <field name="publisher">
                        <xsl:value-of select="//dc:publisher[normalize-space()]"/>
                    </field>
                </xsl:if>

                <!-- PUBLISHDATE -->
                <xsl:if test="//dc:date">
                    <field name="publishDate">
                        <xsl:value-of select="php:function('YorkVuFind::getYorkSpacePublishDate', string(//dc:date))"/>
                    </field>
                </xsl:if>

                <!-- URL -->
               <xsl:for-each select="//dc:identifier">
                   <xsl:if test="substring(., 1, 21) = &quot;http://hdl.handle.net&quot;">
                       <field name="url">
                           <xsl:value-of select="." />
                       </field>
                   </xsl:if>
                   <xsl:if test="contains(., '/dspace/handle/123456789/')">
                       <field name="url">
                           <xsl:value-of select="'http://hdl.handle.net/10315/'"/><xsl:value-of select="substring-after(., '/dspace/handle/123456789/')" />
                       </field>
                   </xsl:if>
               </xsl:for-each>
               
               <!-- FULLTEXT -->
               <field name="fulltext">
                   <xsl:value-of select="php:function('YorkVuFind::getYorkSpaceFullText', string(//identifier))"/>
               </field>
            </doc>
        </add>
    </xsl:template>
</xsl:stylesheet>
