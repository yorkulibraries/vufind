package org.solrmarc.index;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.marc4j.marc.Record;
import org.solrmarc.tools.SolrMarcIndexerException;

import ca.yorku.library.vufind.Utils;

public class YorkIndexer extends VuFindIndexer {
	// make these properties public so bsh code can access them
	public Connection vufindDatabase = null;
	public Set<String> sfxISSNs = null;
	public Set<String> mulerISSNs = null;
	public Set<String> sirsiISSNs = null;
	public Set<String> resolverIDsInSirsi = null;
	public Map<String, String> shelvingKeysMap = null;

	// Initialize logging category
	static Logger logger = Logger.getLogger(YorkIndexer.class.getName());

	public YorkIndexer(final String propertiesMapFile,
			final String[] propertyDirs) throws FileNotFoundException,
			IOException, ParseException {
		super(propertiesMapFile, propertyDirs);
		logger.debug("Constructor: YorkIndexer");

		String dsn = Utils.getConfigSetting("config.ini", "Database",
				"database");
		try {
			vufindDatabase = Utils.connectToDatabase(dsn);
		} catch (Exception e) {
			throw new SolrMarcIndexerException(SolrMarcIndexerException.EXIT,
					e.getMessage());
		}

		try {
			loadISSNs();
			loadResolverIDs();
			loadShelvingKeys();
		} catch (Exception e) {
			throw new SolrMarcIndexerException(SolrMarcIndexerException.EXIT,
					e.getMessage());
		}
	}

	public String getRecordId(Record record, String source) {
		return Utils.getRecordId(record, source);
	}
	
	private void loadISSNs() throws SQLException {
		logger.info("Loading ISSNs from db...");

		// Statement to get ISSNs in DB
		PreparedStatement stmt = vufindDatabase
				.prepareStatement("select distinct number from issns where source=?");

		// load muler ISSNs
		mulerISSNs = new TreeSet<String>();
		stmt.setString(1, "muler");
		ResultSet rs = stmt.executeQuery();
		while (rs.next()) {
			mulerISSNs.add(rs.getString(1));
		}
		rs.close();
		logger.info("Loaded " + mulerISSNs.size() + " ISSNs from MULER");

		// load sfx ISSNs
		sfxISSNs = new TreeSet<String>();
		stmt.setString(1, "sfx");
		rs = stmt.executeQuery();
		while (rs.next()) {
			sfxISSNs.add(rs.getString(1));
		}
		rs.close();
		logger.info("Loaded " + sfxISSNs.size() + " ISSNs from SFX");

		// load sirsi ISSNs
		sirsiISSNs = new TreeSet<String>();
		stmt.setString(1, "sirsi");
		rs = stmt.executeQuery();
		while (rs.next()) {
			sirsiISSNs.add(rs.getString(1));
		}
		rs.close();
		logger.info("Loaded " + sirsiISSNs.size() + " ISSNs from SIRSI");

		stmt.close();
	}

	private void loadResolverIDs() throws SQLException {
		logger.info("Loading Resolver IDs from db...");

		// Statement to get IDs in DB
		PreparedStatement stmt = vufindDatabase
				.prepareStatement("select distinct number from resolver_ids where source=?");

		// load
		resolverIDsInSirsi = new TreeSet<String>();
		stmt.setString(1, "sirsi");
		ResultSet rs = stmt.executeQuery();
		while (rs.next()) {
			resolverIDsInSirsi.add(rs.getString(1));
		}
		rs.close();
		logger.info("Loaded " + resolverIDsInSirsi.size()
				+ " Resolver IDs from SIRSI");

		stmt.close();
	}

	private void loadShelvingKeys() {
		shelvingKeysMap = new HashMap<String, String>();
		File tmpFile = new File("/tmp/callnums.txt");
		try {
			if (tmpFile.exists() && tmpFile.length() > 0) {
				BufferedReader reader = new BufferedReader(new FileReader(
						tmpFile));
				String line;
				while ((line = reader.readLine()) != null) {
					String[] parts = line.split("\\|");
					if (parts.length > 0) {
						String key = parts[0].replaceAll("[^a-zA-Z0-9. ]", "")
								.toUpperCase().trim();
						String value = key;
						if (parts.length > 1) {
							value = parts[1].trim();
						}
						shelvingKeysMap.put(key, value);
					}
				}
				reader.close();
			}
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
		logger.info("Loaded " + shelvingKeysMap.size() + " shelving keys from "
				+ tmpFile.getAbsolutePath());
	}
}
