package ca.yorku.library.vufind;

import java.io.BufferedReader;
import java.io.FileReader;
import java.sql.Connection;
import java.sql.PreparedStatement;

import org.apache.log4j.Logger;

public class BuildCallNumberBrowseIndex {
	// Initialize logging category
	static Logger logger = Logger.getLogger(BuildCallNumberBrowseIndex.class
			.getName());
	static String callnumBrowse = "/tmp/callnum_browse.txt";

	public static void main(String[] args) throws Exception {
		Connection db = Utils.connectToDatabase();
		String tableName = "callnumber_browse_index";
		logger.info("truncating table " + tableName);
		PreparedStatement truncateStmt = db.prepareStatement("TRUNCATE TABLE "
				+ tableName);
		truncateStmt.execute();
		truncateStmt.close();

		logger.info("Loading data from file " + callnumBrowse + " to table "
				+ tableName);
		String insertSql = "INSERT INTO callnumber_browse_index "
				+ "(shelving_key, callnum, record_id) VALUES (?, ?, ?)";
		PreparedStatement insertStmt = db.prepareStatement(insertSql);
		BufferedReader reader = new BufferedReader(
				new FileReader(callnumBrowse));
		for (String line; (line = reader.readLine()) != null;) {
			String[] parts = line.split("\\|");
			insertStmt.setString(1, parts[0].trim());
			insertStmt.setString(2, parts[1].trim());
			insertStmt.setString(3, parts[2].trim());
			insertStmt.execute();
		}
		reader.close();
		insertStmt.close();
	}

}
