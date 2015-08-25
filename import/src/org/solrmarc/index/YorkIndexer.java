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
import java.util.ArrayList;
import java.util.HashSet;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.TreeMap;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.marc4j.marc.ControlField;
import org.marc4j.marc.DataField;
import org.marc4j.marc.Record;
import org.marc4j.marc.Subfield;
import org.marc4j.marc.VariableField;

import ca.yorku.library.vufind.Utils;

public class YorkIndexer extends VuFindIndexer {
    public static final String AVAILABLE = "Available";
    public static final String CHECKEDOUT = "Checked out";
    
    // make these properties public so bsh code can access them
    public static Connection vufindDatabase = null;
    public static Set<String> sfxISSNs = null;
    public static Set<String> mulerISSNs = null;
    public static Set<String> sirsiISSNs = null;
    public static Set<String> resolverIDsInSirsi = null;
    public static Map<String, String> shelvingKeysMap = null;
    public static Map<String, Set<String>> itemsLocationsMap = null;

    // Initialize logging category
    static Logger logger = Logger.getLogger(YorkIndexer.class.getName());

    // Initialize the class
    static {
        logger.debug("Start of YorkIndexer static initialization block.");
        try {
            vufindDatabase = Utils.connectToDatabase();
            loadISSNs();
            loadResolverIDs();
            loadShelvingKeys();
            loadItemsLocations();
        } catch (Exception e) {
           throw new RuntimeException(e);
        }
        logger.debug("End of YorkIndexer static initialization block.");
    }
    
    public YorkIndexer(final String propertiesMapFile,
            final String[] propertyDirs) throws FileNotFoundException,
            IOException, ParseException {
        super(propertiesMapFile, propertyDirs);
    }
    
    public String getRecordId(Record record, String source) {
        return Utils.getRecordId(record, source);
    }

    @Override
    public Set<String> getFullTextUrls(Record record) {
        Set<String> urls = new TreeSet<String>();
        Set<String> possUrls = super.getFullTextUrls(record);
        for (String url : possUrls) {
            if (!url.toLowerCase().contains("loc.gov")
                    && !url.toLowerCase().contains(
                            "http://www.library.yorku.ca/images/erc/")) {
                urls.add(url);
            }
        }
        return urls;
    }

    public String getFullTitle(Record record) {
        String title = "";
        DataField field = (DataField) record.getVariableField("245");
        if (field != null) {
            List<Subfield> subfields = field.getSubfields();
            for (Subfield subfield : subfields) {
                char code = subfield.getCode();
                String data = subfield.getData();
                if (code != 'c') {
                    title += data + " ";
                }
            }
        }
        return title;
    }

    public Set<String> getISSNs(Record record) {
        return getFieldList(record, "022a:022y:440x:490x:730x:776x:780x:785x");
    }

    public Set<String> getCleanedISSNs(Record record) {
        Set<String> result = new TreeSet<String>();
        Set<String> issns = getISSNs(record);
        for (String issn : issns) {
            issn = issn.replaceAll("[^0-9Xx]", "").toUpperCase();
            if (issn.length() >= 8) {
                result.add(issn.substring(0, 8));
            }
        }
        return result;
    }

    public String getSuppressed(Record record, String source) {
        if ("muler".equals(source)) {
            Set<String> issns = getCleanedISSNs(record);
            for (String issn : issns) {
                if (sfxISSNs.contains(issn)) {
                    // suppress MULER records that exist in SFX
                    return "issn_exists_in_sfx";
                }
                if (sirsiISSNs.contains(issn)) {
                    return "issn_exists_in_sirsi_but_not_in_sfx";
                }
            }

            Set<String> urls = getFullTextUrls(record);
            for (String url : urls) {
                String id = null;
                if (url.indexOf("http://www.library.yorku.ca/eresolver/?id=") != -1) {
                    id = url.substring("http://www.library.yorku.ca/eresolver/?id="
                            .length());
                } else if (url
                        .indexOf("http://www.library.yorku.ca/e/resolver/id/") != -1) {
                    id = url.substring("http://www.library.yorku.ca/e/resolver/id/"
                            .length());
                }
                if (resolverIDsInSirsi.contains(id)) {
                    return "resolver_id_exists_in_sirsi";
                }
            }
        }
        if ("sfx".equals(source)) {
            Set<String> issns = getCleanedISSNs(record);
            if (issns.isEmpty()) {
                // suppress SFX records with no ISSN
                return "no_issn";
            }
            for (String issn : issns) {
                if (sirsiISSNs.contains(issn)) {
                    // suppress SFX records that exist in SIRSI
                    return "issn_exists_in_sirsi";
                }
            }
        }
        return "no";
    }

    public Set<String> getLocation(Record record) {
        String id = Utils.getRecordId(record, "catalog");
        Set<String> locations = itemsLocationsMap.get(id);
        if (locations == null) {
            locations = new HashSet<String>(); 
        }
        
        // check if item is available online, if so then add INTERNET as a location
        Set<String> issns = getISSNs(record);
        for (String issn : issns) {
        	issn = issn.toUpperCase().replaceAll("[^0-9X]", "");
            if (sfxISSNs.contains(issn) || mulerISSNs.contains(issn)) {
                locations.add("INTERNET");
                return locations;
            }
        }
        Set<String> urls = getFullTextUrls(record);
        if (!urls.isEmpty()) {
            locations.add("INTERNET");
            return locations;
        }
        return locations;
    }

    /**
     * Get the call number that is sortable.
     */
    public String getCallNumberSortable(Record record) {

        // get all LC call numbers and try to find first matching shelving key
        List<String> callnums = getLCCallNumbersFrom999(record);
        for (String callnum : callnums) {
            String key = shelvingKeysMap.get(callnum);
            if (key != null && !key.trim().equals("")) {
                return key;
            }
        }

        // no matching shelving key, return the first LC call number
        if (!callnums.isEmpty()) {
            return callnums.get(0);
        }

        // return the first call number that is not a generic string
        callnums = getCallNumbersFrom999(record);
        for (String callnum : callnums) {
            if ("ELECTRONIC".equals(callnum) || "DVD".equals(callnum)
                    || "VIDEO".equals(callnum)) {
                continue;
            }
            return callnum;
        }

        return null;
    }

    /**
     * Get the first letter of the first LC call number.
     */
    public String getCallNumberFirstLetter(Record record) {
        String callnum = getFirstLCCallNumberFrom999(record);
        return (callnum == null) ? null : callnum.substring(0, 1);
    }

    /**
     * Extract the subject component of the first LC call number
     * 
     * Can return null
     * 
     * @param record
     * @return subject portion
     */
    public String getCallNumberSubject(Record record) {
        String callnum = getFirstLCCallNumberFrom999(record);
        if (callnum != null) {
            String[] parts = callnum.split("\\s+");
            if (parts.length > 0) {
                return parts[0];
            }
        }
        return null;
    }

    public List<String> getCallNumberFacet(Record record) {
        List<String> results = new ArrayList<String>();
        List<String> callnums = getLCCallNumbersFrom999(record);
        for (String callnum : callnums) {
            String[] parts = callnum.split("\\s+");
            if (parts.length > 0) {
                String code = parts[0].trim().toUpperCase();
                if (code.length() >= 1 && code.length() <= 3) {
                    results.add(code);
                }
            }
        }
        return results;
    }

    public List<String> getCallNumberSearchable(Record record) {
        List<String> results = new ArrayList<String>();
        List<String> callnums = getCallNumbersFrom999(record);
        for (String callnum : callnums) {
            results.add(callnum.replace(" ", ""));
        }
        return results;
    }

    /**
     * Returns all the local call numbers as found in the 999 tags
     * 
     * @param record
     * @return
     */
    public List<String> getCallNumbersFrom999(Record record) {
        List<String> callnums = new ArrayList<String>();

        // get all the 999 from the record
        List<VariableField> fields = record.getVariableFields("999");
        for (VariableField f : fields) {
            DataField field = (DataField) f;
            Subfield sf = field.getSubfield('a');
            if (sf != null) {
                String callnum = sf.getData();
                callnum = callnum.replaceAll("[^a-zA-Z0-9. ]", "").trim()
                        .toUpperCase();
                if (!callnum.equals("")) {
                    callnums.add(callnum);
                }
            }
        }

        return callnums;
    }

    public List<String> getLCCallNumbersFrom999(Record record) {
        List<String> callnums = new ArrayList<String>();
        List<VariableField> fields = record.getVariableFields("999");
        for (VariableField f : fields) {
            DataField field = (DataField) f;
            Subfield sf = field.getSubfield('a');
            if (sf != null) {
                String callnum = sf.getData();
                callnum = callnum.replaceAll("[^a-zA-Z0-9. ]", "").trim()
                        .toUpperCase();
                if ("ELECTRONIC".equals(callnum) || callnum.startsWith("VIDEO")
                        || callnum.startsWith("DVD")
                        || callnum.startsWith("XX")) {
                    continue;
                }
                String type = field.getSubfield('w').getData().trim();
                if ("LC".equals(type) || "LCPER".equals(type)) {
                    if (callnum.matches("^[A-NP-Z].+")) {
                        callnums.add(callnum);
                    }
                }
            }
        }
        return callnums;
    }

    public String getFirstLCCallNumberFrom999(Record record) {
        List<String> callnums = getLCCallNumbersFrom999(record);
        return callnums.isEmpty() ? null : callnums.get(0);
    }

    private static void loadISSNs() throws SQLException {
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
        stmt.setString(1, "catalog");
        rs = stmt.executeQuery();
        while (rs.next()) {
            sirsiISSNs.add(rs.getString(1));
        }
        rs.close();
        logger.info("Loaded " + sirsiISSNs.size() + " ISSNs from SIRSI");

        stmt.close();
    }

    private static void loadResolverIDs() throws SQLException {
        logger.info("Loading Resolver IDs from db...");

        // Statement to get IDs in DB
        PreparedStatement stmt = vufindDatabase
                .prepareStatement("select distinct number from resolver_ids where source=?");

        // load
        resolverIDsInSirsi = new TreeSet<String>();
        stmt.setString(1, "catalog");
        ResultSet rs = stmt.executeQuery();
        while (rs.next()) {
            resolverIDsInSirsi.add(rs.getString(1));
        }
        rs.close();
        logger.info("Loaded " + resolverIDsInSirsi.size()
                + " Resolver IDs from SIRSI");

        stmt.close();
    }

    private static void loadShelvingKeys() {
        shelvingKeysMap = new TreeMap<String, String>();
        File tmpFile = new File("/tmp/callnums.txt");
        logger.info("Loading shelving keys map from " + tmpFile.getAbsolutePath());
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
    
    private static void loadItemsLocations() {
        itemsLocationsMap = new TreeMap<String, Set<String>>();
        File tmpFile = new File("/tmp/items.txt");
        logger.info("Loading items locations map from " + tmpFile.getAbsolutePath());
        try {
            if (tmpFile.exists() && tmpFile.length() > 0) {
                BufferedReader reader = new BufferedReader(new FileReader(
                        tmpFile));
                String line;
                while ((line = reader.readLine()) != null) {
                    String[] parts = line.split("\\|");
                    if (parts.length > 1) {
                        String key = parts[0];
                        Set<String> value = itemsLocationsMap.get(key);
                        if (value == null) {
                            value = new TreeSet<String>();
                        }
                        value.add(parts[1].trim());
                        itemsLocationsMap.put(key, value);
                    }
                }
                reader.close();
            }
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
        logger.info("Loaded " + itemsLocationsMap.size() + " entries from "
                + tmpFile.getAbsolutePath());
    }
}
