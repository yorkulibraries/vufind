package org.solrmarc.index;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.text.ParseException;

import org.apache.log4j.Logger;
import org.solrmarc.tools.SolrMarcIndexerException;

public class YorkIndexer extends VuFindIndexer {
	// Initialize VuFind database connection (null until explicitly activated)
	public Connection vufindDatabase = null;

	// Initialize logging category
	static Logger logger = Logger.getLogger(YorkIndexer.class.getName());

	public YorkIndexer(final String propertiesMapFile,
			final String[] propertyDirs) throws FileNotFoundException,
			IOException, ParseException {
		super(propertiesMapFile, propertyDirs);
		logger.debug("Constructor: YorkIndexer");

		connectToDatabase();
	}

	/**
	 * Log an error message and throw a fatal exception.
	 * 
	 * @param msg
	 */
	private void dieWithError(String msg) {
		logger.error(msg);
		throw new SolrMarcIndexerException(SolrMarcIndexerException.EXIT, msg);
	}

	/**
	 * Connect to the VuFind database if we do not already have a connection.
	 */
	private void connectToDatabase() {
		// Already connected? Do nothing further!
		if (vufindDatabase != null) {
			return;
		}

		String dsn = getConfigSetting("config.ini", "Database", "database");

		try {
			// Parse key settings from the PHP-style DSN:
			String username = "";
			String password = "";
			String classname = "invalid";
			String prefix = "invalid";
			if (dsn.substring(0, 8).equals("mysql://")) {
				classname = "com.mysql.jdbc.Driver";
				prefix = "mysql";
			} else if (dsn.substring(0, 8).equals("pgsql://")) {
				classname = "org.postgresql.Driver";
				prefix = "postgresql";
			}

			Class.forName(classname).newInstance();
			String[] parts = dsn.split("://");
			if (parts.length > 1) {
				parts = parts[1].split("@");
				if (parts.length > 1) {
					dsn = prefix + "://" + parts[1];
					parts = parts[0].split(":");
					username = parts[0];
					if (parts.length > 1) {
						password = parts[1];
					}
				}
			}

			// Connect to the database:
			vufindDatabase = DriverManager.getConnection("jdbc:" + dsn,
					username, password);
		} catch (Throwable e) {
			dieWithError("Unable to connect to VuFind database");
		}

		Runtime.getRuntime().addShutdownHook(new VuFindShutdownThread(this));
	}

	public static void main(String[] args) {
	}

}
