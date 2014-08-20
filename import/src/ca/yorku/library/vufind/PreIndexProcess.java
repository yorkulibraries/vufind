package ca.yorku.library.vufind;

import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.List;

import org.apache.log4j.Logger;
import org.marc4j.MarcReader;
import org.marc4j.MarcStreamReader;
import org.marc4j.MarcXmlReader;
import org.marc4j.marc.Record;
import org.solrmarc.tools.SolrMarcIndexerException;

public class PreIndexProcess implements Runnable {
    private String recordId = null;
    private String source = null;
    private String file = null;
    private Connection db = null;
    private PreparedStatement saveResolverIdStmt = null;
    private PreparedStatement saveISSNStmt = null;
    private PreparedStatement saveMARCStmt = null;

    // Initialize logging category
    static Logger logger = Logger.getLogger(PreIndexProcess.class.getName());

    static String catalog = "/tmp/catalog.mrc";
    static String muler = "/tmp/muler.mrc";
    static String sfxJournals = "/tmp/sfx-journals.xml";
    static String[] resolverPrefixes = {
            "http://www.library.yorku.ca/eresolver/?id=",
            "http://www.library.yorku.ca/e/resolver/id/" };
    static String issnFieldSpecs = "022a:022y:440x:490x:730x:776x:780x:785x";

    static String insertResolverIdSql = "insert into resolver_ids (record_id, number, source) "
            + "values (?, ?, ?) on duplicate key update id=id";

    static String insertISSNSql = "insert into issns (record_id, number, source) "
            + "values (?, ?, ?) on duplicate key update id=id";

    static String insertMARCSql = "insert into marc (record_id, marc) "
            + "values (?, ?) on duplicate key update id=id";

    public PreIndexProcess(String file, String source, Connection db)
            throws SQLException {
        this.file = file;
        this.source = source;
        this.db = db;

        this.saveResolverIdStmt = db.prepareStatement(insertResolverIdSql);
        this.saveISSNStmt = db.prepareStatement(insertISSNSql);
        this.saveMARCStmt = db.prepareStatement(insertMARCSql);
    }

    public static void main(String[] args) throws Exception {
        String dsn = Utils.getConfigSetting("config.ini", "Database",
                "database");
        Connection db = null;
        try {
            db = Utils.connectToDatabase(dsn);
        } catch (Exception e) {
            throw new SolrMarcIndexerException(SolrMarcIndexerException.EXIT,
                    e.getMessage());
        }

        Thread t = new Thread(new PreIndexProcess(catalog, "sirsi", db));
        t.start();

        Thread t2 = new Thread(new PreIndexProcess(muler, "muler", db));
        t2.start();

        Thread t3 = new Thread(new PreIndexProcess(sfxJournals, "sfx", db));
        t3.start();
    }

    private void process(Record record) throws SQLException {
        recordId = Utils.getRecordId(record, source);
        if (source.equals("sirsi")) {
            saveResolverIDs(record);
            saveMARC(record);
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

    private void saveMARC(Record record) {
        if (recordId != null) {
            try {
                String marc = Utils.writeRaw(record);
                saveMARCStmt.setString(1, recordId);
                saveMARCStmt.setString(2, marc);
                saveMARCStmt.execute();
            } catch (Exception e) {
                // this step is not very critical, logging the error is enough
                logger.error(e.getMessage());
            }
        }
    }

    @Override
    public void run() {
        logger.info("Processing source=" + source + ", file=" + file);
        int count = 0;
        try {
            File f = new File(file);
            if (f.exists()) {
                InputStream in = new FileInputStream(f);
                MarcReader reader = (source.equals("sfx")) ? new MarcXmlReader(
                        in) : new MarcStreamReader(in);
                while (reader.hasNext()) {
                    Record record = reader.next();
                    process(record);
                    count++;
                }
            }
        } catch (Exception e) {
            logger.error(e.getMessage());
        }
        logger.info("Processed " + count + " records. source=" + source
                + ", file=" + file);
    }
}
