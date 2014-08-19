package ca.yorku.library.vufind;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.List;
import java.util.Properties;
import java.util.Set;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.ini4j.Ini;
import org.ini4j.InvalidFileFormatException;
import org.marc4j.MarcStreamWriter;
import org.marc4j.MarcWriter;
import org.marc4j.marc.DataField;
import org.marc4j.marc.Record;
import org.marc4j.marc.Subfield;
import org.marc4j.marc.VariableField;

public class Utils {
	// Initialize logging category
	static Logger logger = Logger.getLogger(Utils.class.getName());

	static String sirsiCatkeyPrefix = "(Sirsi) a";
	
	/**
	 * Load an ini file.
	 * 
	 * @param filename
	 * @throws IOException
	 * @throws FileNotFoundException
	 * @throws InvalidFileFormatException
	 */
	public static Ini loadConfigFile(String filename)
			throws InvalidFileFormatException, FileNotFoundException,
			IOException {
		Ini ini = new Ini();
		ini.load(new FileReader(findConfigFile(filename)));
		return ini;
	}

	/**
	 * Connect to the VuFind database.
	 * 
	 * @throws ClassNotFoundException
	 * @throws IllegalAccessException
	 * @throws InstantiationException
	 * @throws SQLException
	 */
	public static Connection connectToDatabase(String dsn)
			throws InstantiationException, IllegalAccessException,
			ClassNotFoundException, SQLException {
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
		return DriverManager.getConnection("jdbc:" + dsn, username, password);
	}

	/**
	 * Given the base name of a configuration file, locate the full path.
	 * 
	 * @param filename
	 */
	public static File findConfigFile(String filename) {
		// Find VuFind's home directory in the environment; if it's not
		// available,
		// try using a relative path on the assumption that we are currently in
		// VuFind's import subdirectory:
		String vufindHome = System.getenv("VUFIND_HOME");
		if (vufindHome == null) {
			vufindHome = "..";
		}
		logger.debug("VUFIND_HOME=" + vufindHome);

		// Check for VuFind 2.0's local directory environment variable:
		String vufindLocal = System.getenv("VUFIND_LOCAL_DIR");
		logger.debug("VUFIND_LOCAL_DIR=" + vufindLocal);

		String[] propertyDirs = { vufindHome + "/import",
				vufindHome + "/import/translation_maps",
				vufindHome + "/import/index_scripts" };
		Properties vuFindConfigs = org.solrmarc.tools.Utils.loadProperties(
				propertyDirs, "vufind.properties");

		// Get the relative VuFind path from the properties file, defaulting to
		// the 2.0alpha-style application/configs if necessary.
		String relativeConfigPath = org.solrmarc.tools.Utils.getProperty(
				vuFindConfigs, "vufind.config.relative_path",
				"application/configs");
		logger.debug("vufind.config.relative_path=" + relativeConfigPath);

		// Try several different locations for the file -- VuFind 2 local dir,
		// VuFind 2 base dir, VuFind 1 base dir.
		File file;
		if (vufindLocal != null) {
			file = new File(vufindLocal + "/" + relativeConfigPath + "/"
					+ filename);
			if (file.exists()) {
				return file;
			}
		}
		file = new File(vufindHome + "/" + relativeConfigPath + "/" + filename);
		if (file.exists()) {
			return file;
		}
		file = new File(vufindHome + "/web/conf/" + filename);
		return file;
	}

	/**
	 * Sanitize a VuFind configuration setting.
	 * 
	 * @param str
	 */
	public static String sanitizeConfigSetting(String str) {
		// Drop comments if necessary:
		int pos = str.indexOf(';');
		if (pos >= 0) {
			str = str.substring(0, pos).trim();
		}

		// Strip wrapping quotes if necessary (the ini reader won't do this for
		// us):
		if (str.startsWith("\"")) {
			str = str.substring(1, str.length());
		}
		if (str.endsWith("\"")) {
			str = str.substring(0, str.length() - 1);
		}
		return str;
	}

	/**
	 * Get a setting from a VuFind configuration file.
	 * 
	 * @param filename
	 * @param section
	 * @param setting
	 * @throws IOException
	 * @throws FileNotFoundException
	 * @throws InvalidFileFormatException
	 */
	public static String getConfigSetting(String filename, String section,
			String setting) throws InvalidFileFormatException,
			FileNotFoundException, IOException {
		String retVal = null;

		// Grab the ini file.
		Ini ini = loadConfigFile(filename);

		// Check to see if we need to worry about an override file:
		String override = ini.get("Extra_Config", "local_overrides");
		if (override != null) {
			Ini overrideIni = loadConfigFile(override);
			retVal = overrideIni.get(section, setting);
			if (retVal != null) {
				return sanitizeConfigSetting(retVal);
			}
		}

		// Try to find the requested setting:
		retVal = ini.get(section, setting);

		// No setting? Check for a parent configuration:
		if (retVal == null) {
			String parent = ini.get("Parent_Config", "path");
			if (parent != null) {
				ini = loadConfigFile(parent);
				retVal = ini.get(section, setting);
			}
		}

		// Return the processed setting:
		return retVal == null ? null : sanitizeConfigSetting(retVal);
	}

	public static Set<String> getFieldValues(Record record, String fieldSpecs) {
		Set<String> results = new TreeSet<String>();
		String[] specs = fieldSpecs.split(":");
		for (String spec : specs) {
			if (spec.length() == 4) {
				String tag = spec.substring(0, 3);
				char code = spec.charAt(3);
				List<VariableField> fields = record.getVariableFields(tag);
				for (VariableField f : fields) {
					DataField field = (DataField) f;
					Subfield subfield = field.getSubfield(code);
					if (subfield != null) {
						results.add(subfield.getData().trim());
					}
				}
			}
		}
		return results;
	}
	
	public static String getFirstFieldValue(Record record, String fieldSpecs) {
		String value = null;
		String[] specs = fieldSpecs.split(":");
		for (String spec : specs) {
			if (spec.length() == 4) {
				String tag = spec.substring(0, 3);
				char code = spec.charAt(3);
				List<VariableField> fields = record.getVariableFields(tag);
				for (VariableField f : fields) {
					DataField field = (DataField) f;
					Subfield subfield = field.getSubfield(code);
					if (subfield != null) {
						return subfield.getData().trim();
					}
				}
			}
		}
		return value;
	}
	
	public static String getRecordId(Record record, String source) {
		String id = null;
	    if ("sirsi".equals(source)) {
	    	Set<String> vals = getFieldValues(record, "035a");
	    	for (String val : vals) {
	    		if (val.startsWith(sirsiCatkeyPrefix)) {
	    			id = val.substring(sirsiCatkeyPrefix.length());
	    			break;
	    		}
	    	}
	    } else if ("sfx".equals(source)) {
	        id = getFirstFieldValue(record, "090a");
	    } else if ("muler".equals(source)) {
	        id = getFirstFieldValue(record, "035a");
	    }
	    return id;
	}
	
	public static String writeRaw(Record record) {
	    ByteArrayOutputStream out = new ByteArrayOutputStream();
	    MarcWriter writer = new MarcStreamWriter(out, "UTF-8", true);
	    writer.write(record);
	    writer.close();

	    String result = null;
	    try {
	        result = out.toString("UTF-8");
	    }
	    catch (UnsupportedEncodingException e) {
	        logger.error(e.getMessage());
	    }
	    return result;
	}
}
