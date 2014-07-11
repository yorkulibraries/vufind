<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">York University Libraries</xsl:param>
    <xsl:param name="collection">Electronic Resources</xsl:param>
    <xsl:template match="records">
	<add>
		<xsl:apply-templates select="record" />
	</add>
    </xsl:template>
    <xsl:template match="record">
	<doc>
		<!-- ID -->
		<field name="id">
			<xsl:value-of select="'muler'" /><xsl:value-of select="id" />
		</field>

	     <!-- CHANGE TRACKING DATES -->
	    <xsl:if test="$track_changes != 0">
			<field name="first_indexed">
				<xsl:value-of select="php:function('YorkVuFind::getFirstIndexed', $solr_core, concat('muler',string(id)))" />
			</field>
			<field name="last_indexed">
				<xsl:value-of select="php:function('YorkVuFind::getLastIndexed', $solr_core, concat('muler',string(id)))" />
			</field>
	    </xsl:if>
	    
	    <field name="source_str">Catalogue</field>

		<!-- RECORDTYPE -->
		<field name="recordtype">muler</field>

		<!-- FULLRECORD -->
		<field name="fullrecord">
			<xsl:copy-of select="php:function('VuFind::xmlAsText', .)" />
		</field>

		<!-- ALLFIELDS -->
		<field name="allfields">
			<xsl:value-of select="php:function('YorkVuFind::getMulerSearchableFields', .)" />
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

		<!-- DESCRIPTION -->
		<xsl:if test="description">
			<field name="description">
				<xsl:value-of select="description" />
			</field>
		</xsl:if>

		<!-- FORMAT -->
		<field name="format">
			<xsl:value-of select="php:function('VuFind::mapString', string(type), 'muler_format_map.properties')" />
		</field>
		
		<!-- BROAD FORMAT -->
		<xsl:if test="php:function('YorkVuFind::mapString', string(type), 'broad_format_map.properties')">
        <field name="broad_format_str_mv">
            <xsl:value-of select="php:function('YorkVuFind::mapString', string(type), 'broad_format_map.properties')" />
        </field>
        </xsl:if>
                
		<!-- TITLE -->
		<field name="title">
			<xsl:value-of select="title[normalize-space()]" />
		</field>
		<field name="title_txtP">
            <xsl:value-of select="title[normalize-space()]" />
        </field>
		<field name="title_short">
            <xsl:value-of select="title[normalize-space()]" />
        </field>
        <field name="title_short_txtP">
            <xsl:value-of select="title[normalize-space()]" />
        </field>
        <field name="title_full">
            <xsl:value-of select="title[normalize-space()]" />
        </field>
	    <field name="title_sort">
		    <xsl:value-of select="php:function('VuFind::stripArticles', string(title[normalize-space()]))" />
	    </field>
	    
	    <!-- OTHER TITLES -->
	    <xsl:for-each select="other_titles">
	    <field name="title_alt">
            <xsl:value-of select="normalize-space()"/>
        </field>
        </xsl:for-each>
        
        <!-- SUBJECTS  -->
        <xsl:for-each select="subject_records/subject_record">
            <field name="topic">
                <xsl:value-of select="name[normalize-space()]"/>
            </field>
            <field name="topic_facet">
                <xsl:value-of select="name[normalize-space()]"/>
            </field>
        </xsl:for-each>
		
		<!-- URL -->
        <xsl:for-each select="url_records/url_record">
            <field name="url">
                <xsl:value-of select="'http://www.library.yorku.ca/e/resolver/id/'"/><xsl:value-of select="id" />
            </field>
        </xsl:for-each>
        
		<!-- ISSN -->
		<xsl:if test="issn[normalize-space()]">
        <field name="issn">
            <xsl:value-of select="issn[normalize-space()]" />
        </field>
        </xsl:if>

		<!-- PUBLISHER -->
		<xsl:if test="publisher[normalize-space()]">
			<field name="publisher">
				<xsl:value-of select="publisher[normalize-space()]" />
			</field>
		</xsl:if>
	</doc>
    </xsl:template>
</xsl:stylesheet>
