package ca.yorku.library.vufind;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.List;
import java.util.Set;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.marc4j.MarcReader;
import org.marc4j.MarcStreamReader;
import org.marc4j.MarcStreamWriter;
import org.marc4j.MarcWriter;
import org.marc4j.MarcXmlReader;
import org.marc4j.marc.Record;

public class PreIndexProcess implements Runnable {
    private String recordId = null;
    private String source = null;
    private String file = null;
    private Connection db = null;
    private PreparedStatement saveResolverIdStmt = null;
    private PreparedStatement saveISSNStmt = null;

    // Initialize logging category
    static Logger logger = Logger.getLogger(PreIndexProcess.class.getName());

    static String catalog = "/tmp/catalog.mrc";
    static String muler = "/tmp/muler.mrc";
    static String sfx = "/tmp/sfx.xml";
    
    static String[] resolverPrefixes = {
            "http://www.library.yorku.ca/eresolver/?id=",
            "http://www.library.yorku.ca/e/resolver/id/" };
    
    static String issnFieldSpecs = "022a:022y:440x:490x:730x:776x:780x:785x";

    static String insertResolverIdSql = "insert into resolver_ids (record_id, number, source) "
            + "values (?, ?, ?) on duplicate key update id=id";

    static String insertISSNSql = "insert into issns (record_id, number, source) "
            + "values (?, ?, ?) on duplicate key update id=id";
    
    static String databaseDSN = null;
    
    static int recordsPerFile = 0;
    
    public PreIndexProcess(String file, String source) {
        this.file = file;
        this.source = source;
        
        try {
            db = Utils.connectToDatabase(databaseDSN);
            saveResolverIdStmt = db.prepareStatement(insertResolverIdSql);
            saveISSNStmt = db.prepareStatement(insertISSNSql);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
    
    @Override
    public void run() {
        logger.info("Processing source=" + source + ", file=" + file);
        long startTime = (new java.util.Date()).getTime();
        
        int count = 0;
        try {
            InputStream in = new FileInputStream(file);
            MarcReader reader = (source.equals("sfx")) ? new MarcXmlReader(
                    in) : new MarcStreamReader(in);
            while (reader.hasNext()) {
                count++;
                Record record = reader.next();
                process(record, count);
            }
            in.close();
        } catch (Exception e) {
            logger.error(e.getMessage());
        }
        
        try {
            db.close();
        } catch (SQLException e) {
            logger.error(e.getMessage());
        }
        
        long endTime = (new java.util.Date()).getTime();
        long duration = (endTime - startTime) / 1000;
        logger.info("Processed " + count + " records. source=" + source
                + ", file=" + file + " in " + duration + " seconds");
    }

    public static void main(String[] args) throws Exception {
        // make sure we got all the files before doing anything
        if (!(new File(catalog)).exists() || !(new File(sfx)).exists() || !(new File(muler)).exists()) {
            logger.error("Missing required MARC file(s). Abort!");
            System.exit(1);
        }
            
        databaseDSN = Utils.getConfigSetting("config.ini", "Database", "database");
        recordsPerFile = Integer.valueOf(System.getProperty("records_per_file", "750000"));
        
        if (recordsPerFile > 0) {
            Set<String> files = splitMarcFile(catalog, "catalog"); 
            for (String file : files) {
                Thread t = new Thread(new PreIndexProcess(file, "catalog"));
                t.start();
            }
        }
        
        deleteISSNs("muler");
        deleteISSNs("sfx");

        Thread t2 = new Thread(new PreIndexProcess(muler, "muler"));
        t2.start();

        Thread t3 = new Thread(new PreIndexProcess(sfx, "sfx"));
        t3.start();
    }
    
    private static Set<String> splitMarcFile(String file, String source) {
        logger.info("About to split MARC file into files of " + recordsPerFile + " records each. source=" + source + ", file=" + file);
        long startTime = (new java.util.Date()).getTime();
        Set<String> files = new TreeSet<String>();
        int count = 0;
        try {
            File f = new File(file);
            if (f.exists()) {
                InputStream in = new FileInputStream(f);
                MarcReader reader = (source.equals("sfx")) ? new MarcXmlReader(
                        in) : new MarcStreamReader(in);
                while (reader.hasNext()) {
                    count++;
                    Record record = reader.next();
                    files.add(saveMARCToFile(record, count, source));
                }
                in.close();
            }
        } catch (Exception e) {
            logger.error(e.getMessage());
        }
        
        long endTime = (new java.util.Date()).getTime();
        long duration = (endTime - startTime) / 1000;
        logger.info("Finished splitting " + count + " records. source=" + source
                + ", file=" + file + " in " + duration + " seconds");
        return files;
    }
    
    private static String saveMARCToFile(Record record, int recnum, String source) throws IOException {
        // split original catalog.mrc into 4 files catalog0..3.mrc 
        // each of recordsPerFile records with the last file
        // (catalog3.mrc) having all remaining records
        int filenum = recnum / recordsPerFile;
        if (filenum > 3) {
            filenum = 3;
        }
        String extension = "sfx".equals(source) ? ".xml" : ".mrc";
        File file = new File("/tmp/" + source + filenum + extension);
        OutputStream out = new FileOutputStream(file, true);
        MarcWriter writer = new MarcStreamWriter(out);
        writer.write(record);
        writer.close();
        
        return file.getAbsolutePath();
    }
    
    private static void deleteISSNs(String source) {
        logger.info("Deleting ISSNs from " + source);
        try {
            Connection db = Utils.connectToDatabase(databaseDSN);
            PreparedStatement stmt = db.prepareStatement("DELETE FROM issns WHERE source=?");
            stmt.setString(1, "sfx");
            stmt.execute();
            stmt.close();
            db.close();
        } catch (Exception e) {
            logger.error(e.getMessage());
        }
    }

    private void process(Record record, int recnum) throws SQLException, IOException {
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
}
