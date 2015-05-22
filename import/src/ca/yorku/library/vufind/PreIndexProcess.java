package ca.yorku.library.vufind;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import org.apache.log4j.Logger;
import org.marc4j.MarcReader;
import org.marc4j.MarcStreamReader;
import org.marc4j.MarcXmlReader;
import org.marc4j.marc.Record;

public class PreIndexProcess implements Runnable {
	private String recordId = null;
	private String source = null;
	private String file = null;
	private Connection db = null;
	private PreparedStatement saveResolverIdStmt = null;
	private PreparedStatement saveISSNStmt = null;
	private PreparedStatement deleteISSNStmt = null;

	// Initialize logging category
	static Logger logger = Logger.getLogger(PreIndexProcess.class.getName());

	static String[] resolverPrefixes = {
			"http://www.library.yorku.ca/eresolver/?id=",
			"http://www.library.yorku.ca/e/resolver/id/" };

	static String issnFieldSpecs = "022a:022y:440x:490x:730x:776x:780x:785x";

	static String insertResolverIdSql = "insert into resolver_ids (record_id, number, source) "
			+ "values (?, ?, ?) on duplicate key update id=id";

	static String insertISSNSql = "insert into issns (record_id, number, source) "
			+ "values (?, ?, ?) on duplicate key update id=id";

	static String deleteISSNSql = "DELETE FROM issns WHERE source=?";

	static String catalog = "/tmp/catalog.mrc";
	static String muler = "/tmp/muler.mrc";
	static String sfxJournals = "/tmp/sfx-journals.xml";

	static int threadCount = 2;

	public PreIndexProcess(String file, String source) throws SQLException {
		this.file = file;
		this.source = source;
		this.db = Utils.connectToDatabase();
		saveResolverIdStmt = db.prepareStatement(insertResolverIdSql);
		saveISSNStmt = db.prepareStatement(insertISSNSql);
		deleteISSNStmt = db.prepareStatement(deleteISSNSql);
	}

	public static void main(String[] args) {
		// make sure we got all the files before doing anything
		if (!(new File(catalog)).exists() || !(new File(sfxJournals)).exists()
				|| !(new File(muler)).exists()) {
			logger.error("Missing required MARC file(s). Abort!");
			System.exit(1);
		}

		// get thread count from system properties
		threadCount = Integer.valueOf(System.getProperty("thread_count", "2"));

		// split the catalog file
		logger.info("Splitting " + catalog + " into " + threadCount
				+ " pieces.");
		String[] catalogPieces = null;
		try {
			catalogPieces = Utils.splitMarcFile(catalog, threadCount);
		} catch (Exception e) {
			logger.error(e.getMessage());
			System.exit(1);
		}

		try {
			// run the pre-index process for each catalog piece in its own
			// thread
			List<Thread> threads = new ArrayList<Thread>();
			for (String catalogPiece : catalogPieces) {
				Thread t = new Thread(new PreIndexProcess(catalogPiece,
						"catalog"));
				t.start();
				threads.add(t);
			}

			// wait for all catalog threads to complete
			for (Thread t : threads) {
				t.join();
			}

			// run the pre-index process for the MULER piece
			Thread t = new Thread(new PreIndexProcess(muler, "muler"));
			t.start();

			// run the pre-index process for the SFX piece
			t = new Thread(new PreIndexProcess(sfxJournals, "sfx"));
			t.start();
		} catch (Exception e) {
			logger.error(e.getMessage());
			System.exit(1);
		}

	}

	@Override
	public void run() {
		logger.info("Processing source=" + source + ", file=" + file);
		long startTime = (new java.util.Date()).getTime();

		if (!source.equals("catalog")) {
			try {
				deleteISSNs(source);
			} catch (SQLException e) {
				logger.error(e.getMessage());
			}
		}

		int count = 0;
		try {
			InputStream in = new FileInputStream(file);
			MarcReader reader = (source.equals("sfx")) ? new MarcXmlReader(in)
					: new MarcStreamReader(in);
			while (reader.hasNext()) {
				count++;
				Record record = reader.next();
				process(record, count);
			}
			in.close();
		} catch (Exception e) {
			logger.error(e.getMessage());
		}

		long endTime = (new java.util.Date()).getTime();
		long duration = (endTime - startTime) / 1000;
		logger.info("Processed " + count + " records. source=" + source
				+ ", file=" + file + " in " + duration + " seconds");
	}

	private void process(Record record, int recnum) throws SQLException,
			IOException {
		recordId = Utils.getRecordId(record, source);
		if (source.equals("catalog")) {
			saveResolverIDs(record);
		}
		saveISSNs(record);
	}

	private void saveResolverIDs(Record record) throws SQLException {
		if (recordId != null) {
			List<String> urls = Utils.getFieldValues(record, "856u:856a");
			for (String url : urls) {
				for (String prefix : resolverPrefixes) {
					if (url.startsWith(prefix)) {
						String resolverId = url.substring(prefix.length());
						logger.debug("resolver ID number: " + resolverId
								+ " for record ID: " + recordId);
						if (resolverId.matches("[0-9]+")) {
							saveResolverId(recordId, resolverId);
						}
					}
				}
			}
		}
	}

	private void saveISSNs(Record record) throws SQLException {
		if (recordId != null) {
			List<String> issns = Utils.getFieldValues(record, issnFieldSpecs);
			for (String issn : issns) {
				issn = issn.toUpperCase().replaceAll("[^0-9X]", "");
				logger.debug("ISSN number: " + issn + " for record ID: "
						+ recordId);
				if (issn.matches("[0-9X]{8}")) {
					saveISSN(recordId, issn);
				}
			}
		}
	}

	private void saveResolverId(String recordId, String resolverId)
			throws SQLException {
		saveResolverIdStmt.setString(1, recordId);
		saveResolverIdStmt.setString(2, resolverId);
		saveResolverIdStmt.setString(3, source);
		saveResolverIdStmt.execute();
	}

	private void saveISSN(String recordId, String issn) throws SQLException {
		saveISSNStmt.setString(1, recordId);
		saveISSNStmt.setString(2, issn);
		saveISSNStmt.setString(3, source);
		saveISSNStmt.execute();
	}

	private void deleteISSNs(String source) throws SQLException {
		logger.info("Deleting ISSNs from " + source);
		deleteISSNStmt.setString(1, source);
		deleteISSNStmt.execute();
	}
}
