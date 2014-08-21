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
import java.util.HashMap;
import java.util.HashSet;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.marc4j.marc.ControlField;
import org.marc4j.marc.DataField;
import org.marc4j.marc.Record;
import org.marc4j.marc.Subfield;
import org.marc4j.marc.VariableField;

import ca.yorku.library.vufind.Utils;

public class YorkIndexer extends VuFindIndexer {
    // make these properties public so bsh code can access them
    public static Connection vufindDatabase = null;
    public static Set<String> sfxISSNs = null;
    public static Set<String> mulerISSNs = null;
    public static Set<String> sirsiISSNs = null;
    public static Set<String> resolverIDsInSirsi = null;
    public static Map<String, String> shelvingKeysMap = null;

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
        Set<String> locations = new HashSet<String>();
        List<VariableField> fields = record.getVariableFields("999");
        for (VariableField f : fields) {
            DataField field = (DataField) f;
            String location = "";
            Subfield sfl = field.getSubfield('l');
            if (sfl != null) {
                location = sfl.getData().trim();
                locations.add(location);
            }
            Subfield sfa = field.getSubfield('a');
            if (sfa != null) {
                String callnum = sfa.getData().trim();
                if ("INTERNET".equals(location)
                        || "E-RESERVES".equals(location)
                        || "ELECTRONIC".equals(callnum)) {
                    locations.add("INTERNET");
                }
            }
        }
        if (!locations.contains("INTERNET")) {
            // check if issn in SFX or MULER
            Set<String> issns = getISSNs(record);
            for (String issn : issns) {
                if (sfxISSNs.contains(issn) || mulerISSNs.contains(issn)) {
                    logger.info(issn + " is in MULER or SFX");
                    locations.add("INTERNET");
                    break;
                }
            }

            Set<String> urls = getFullTextUrls(record);
            if (!urls.isEmpty()) {
                locations.add("INTERNET");
            }
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

    /**
     * Returns the formats of the resource as described by a marc bib record.
     * NOTE: Adapted from Stanford VuFind so it can be used in a beanshell
     * 
     * @param record
     * @return Set of strings containing format values for the resource
     */
    public Set<String> getStanfordFormats(Record record) {
        Set<String> resultSet = new HashSet<String>();

        // As of July 28, 2008, algorithms for formats are currently in email
        // message from Vitus Tang to Naomi Dushay, cc Phil Schreur, Margaret
        // Hughes, and Jennifer Vine dated July 23, 2008.

        // Note: MARC21 documentation refers to char numbers that are 0 based,
        // just like java string indexes, so char "06" is at index 6, and is
        // the seventh character of the field

        // assign formats based on leader chars 06, 07 and chars in 008
        String leaderStr = record.getLeader().toString();
        char leaderChar07 = leaderStr.charAt(7);
        VariableField f008 = record.getVariableField("008");
        char leaderChar06 = leaderStr.charAt(6);
        switch (leaderChar06) {
        case 'a':
            if (leaderChar07 == 'a' || leaderChar07 == 'm')
                resultSet.add("Book");
            break;
        case 'b':
        case 'p':
            resultSet.add("Manuscript/Archive");
            break;
        case 'c':
        case 'd':
            resultSet.add("Score");
            break;
        case 'e':
        case 'f':
            resultSet.add("Map");
            break;
        case 'g':
            // look for m or v in 008 field, char 33 (count starts at 0)
            if (f008 != null && f008.find("^.{33}[mv]"))
                resultSet.add("Video");
            break;
        case 'i':
            resultSet.add("Sound Recording");
            break;
        case 'j':
            resultSet.add("Music Recording");
            break;
        case 'k':
            // look for i, k, p, s or t in 008 field, char 33 (count starts at
            // 0)
            if (f008 != null && f008.find("^.{33}[ikpst]"))
                resultSet.add("Image");
            break;
        case 'm':
            // look for a in 008 field, char 26 (count starts at 0)
            if (f008 != null && f008.find("^.*{26}a"))
                resultSet.add("Computer File");
            break;
        case 'o': // instructional kit
            resultSet.add("Other");
            break;
        case 'r': // object
            resultSet.add("Other");
            break;
        case 't':
            if (leaderChar07 == 'a' || leaderChar07 == 'm')
                resultSet.add("Book");
            break;
        } // end switch

        if (resultSet.isEmpty() || resultSet.size() == 0) {
            // look for serial publications - leader/07 s
            if (leaderChar07 == 's') {
                if (f008 != null) {
                    char c21 = ((ControlField) f008).getData().charAt(21);
                    switch (c21) {
                    case 'd': // updating database (ignore)
                        break;
                    case 'l': // updating looseleaf (ignore)
                        break;
                    case 'm': // monographic series
                        resultSet.add("Book");
                        break;
                    case 'n':
                        resultSet.add("Newspaper");
                        break;
                    case 'p':
                        // b4 2008-12-02 was:
                        // resultSet.add(Format.JOURNAL.toString());
                        resultSet.add("Journal/Periodical");
                        break;
                    case 'w': // web site
                        resultSet.add("Website");
                        break;
                    }
                }
            }
        }

        // look for serial publications 006/00 s
        if (resultSet.isEmpty() || resultSet.size() == 0) {
            VariableField f006 = record.getVariableField("006");
            if (f006 != null && f006.find("^[s]")) {
                char c04 = ((ControlField) f006).getData().charAt(4);
                switch (c04) {
                case 'd': // updating database (ignore)
                    break;
                case 'l': // updating looseleaf (ignore)
                    break;
                case 'm': // monographic series
                    resultSet.add("Book");
                    break;
                case 'n':
                    resultSet.add("Newspaper");
                    break;
                case 'p':
                    resultSet.add("Journal/Periodical");
                    break;
                case 'w': // web site
                    resultSet.add("Website");
                    break;
                case ' ':
                    resultSet.add("Journal/Periodical");
                }
            }
            // if still nothing, see if 007/00s serial publication by default
            else if ((resultSet.isEmpty() || resultSet.size() == 0)
                    && leaderChar07 == 's') {
                if (f008 != null) {
                    char c21 = ((ControlField) f008).getData().charAt(21);
                    switch (c21) {
                    case 'd':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'p':
                    case 'w':
                        break;
                    case ' ':
                        // b4 2008-12-02 was:
                        // resultSet.add(Format.SERIAL_PUBLICATION.toString());
                        resultSet.add("Journal/Periodical");
                    }
                }
            }
        }

        // look for conference proceedings in 6xx
        List<DataField> dfList = record.getDataFields();
        for (DataField df : dfList) {
            if (df.getTag().startsWith("6")) {
                List<String> subList = org.solrmarc.tools.Utils
                        .getSubfieldStrings(df, 'x');
                subList.addAll(org.solrmarc.tools.Utils.getSubfieldStrings(df,
                        'v'));
                for (String s : subList) {
                    if (s.toLowerCase().contains("congresses")) {
                        resultSet.remove("Journal/Periodical");
                        resultSet.add("Conference Proceedings");
                    }
                }
            }
        }

        // thesis is determined by the presence of a 502 field.
        Set<String> dissNote = new LinkedHashSet<String>();
        dissNote.addAll(getSubfieldDataAsSet(record, "502", "a", null));
        if (!dissNote.isEmpty() || dissNote.size() != 0)
            resultSet.add("Thesis");

        // microfilm is determined by 245 subfield h containing "microform"
        Set<String> titleH = new LinkedHashSet<String>();
        titleH.addAll(getSubfieldDataAsSet(record, "245", "h", null));
        // check the h subfield of the 245 field
        if (org.solrmarc.tools.Utils.setItemContains(titleH, "microform"))
            resultSet.add("Microform");

        // if we still don't have a format, it's an "other"
        if (resultSet.isEmpty() || resultSet.size() == 0)
            resultSet.add("Other");

        return resultSet;
    }

    /**
     * Get a mix of item types and record formats.
     */
    public Set<String> getMixedFormats(Record record) {
        Set<String> formats = getStanfordFormats(record);

        List<VariableField> fields = record.getVariableFields("999");

        // map item types to formats
        for (VariableField f : fields) {
            DataField field = (DataField) f;
            String type = field.getSubfield('t').getData();
            switch (type) {
            case "ACCESSORY":
            case "SMIL-ACSRY":
                formats.add("Accessory");
                break;
            case "AUDCD-14D":
            case "AUDCD-CLOS":
            case "AUDIO-CD":
                formats.add("Audio Compact Disc");
                break;
            case "AUDIO-78":
                formats.add("Audio 78 RPM");
                break;
            case "AUDIO-CASS":
                formats.add("Audio Cassette");
                break;
            case "AUDIO-LP":
                formats.add("Audio LP");
                break;
            case "AUDIO-REEL":
                formats.add("Audio Reel");
                break;
            case "BOOK":
            case "BRONF-BOOK":
            case "E-ASIAN-BK":
            case "FROST-BOOK":
            case "LAW-BOOK":
            case "LAW-FICTN":
            case "NELLI-BOOK":
            case "SCOTT-BOOK":
            case "SMIL-BOOK":
            case "STEAC-BOOK":
                formats.add("Book");
                break;
            case "CD-ROM":
            case "CDROM-CLOS":
                formats.add("CD-ROM");
                break;
            case "DATAFILE":
                formats.add("Data File");
                break;
            case "DVD-3DAY":
            case "DVD-4HR":
            case "DVD-7DAY":
            case "DVD-CLOS":
            case "DVD-ROM":
            case "DVD":
                formats.add("DVD");
                break;
            case "E-AUDIO":
                formats.add("Streaming Audio");
                break;
            case "E-BOOK":
                formats.add("eBook");
                break;
            case "E-GOV-DOC":
            case "FR-GOV-DOC":
            case "GOV-DOC":
                formats.add("Government Document");
                break;
            case "E-INDEX":
            case "INDEX/DATABASE":
                formats.add("Index/Database");
                break;
            case "E-MAP":
                formats.add("eMap");
                break;
            case "E-SCORE":
                formats.add("Digitized Score");
                break;
            case "E-VIDEO":
                formats.add("Streaming Video");
                break;
            case "EJOURNAL":
                formats.add("eJournal");
                break;
            case "FILM":
                formats.add("Film");
                break;
            case "FONDS":
                formats.add("Archival Finding Aids");
                break;
            case "LAPTOP":
                formats.add("Laptop Computer");
                break;
            case "LASER-DISC":
                formats.add("Laserdisc");
                break;
            case "LAW-DIGEST":
                formats.add("Law Digest");
                break;
            case "LAW-REPORT":
                formats.add("Law Report");
                break;
            case "LAW-STATCI":
            case "LAW-STAT":
                formats.add("Law Statute");
                break;
            case "LAW-THESIS":
                formats.add("Thesis");
                break;
            case "MAP":
                formats.add("Map");
                break;
            case "MICROCARD":
            case "MICROFICHE":
            case "MICROFILM":
                formats.add("Microform");
                break;
            case "MODEL":
                formats.add("Model");
                break;
            case "MULTIMEDIA":
                formats.add("Multimedia");
                break;
            case "PAMPHLET":
                formats.add("Pamphlet");
                break;
            case "PERIODICAL":
            case "LAW-PER":
                formats.add("Journal/Periodical");
                break;
            case "SCORE":
                formats.add("Score");
                break;
            case "SLIDE":
                formats.add("Slide");
                break;
            case "VIDEO-3DAY":
            case "VIDEO-4HR":
            case "VIDEO-7DAY":
            case "VIDEO-CLOS":
            case "VIDEO":
                formats.add("Video");
                break;
            case "WEBSITE":
            case "E-JOURNAL COLLECTION":
            case "E-BOOK COLLECTION":
            case "E-NEWSPAPER COLLECTION":
                formats.clear();
                formats.add("Website");
                break;
            case "E-RESERVES":
                formats.add("eReserve");
                break;
            case "SPEC-COLL":
                break;
            case "ENCYCLOPEDIA/DICTIONARY":
                formats.clear();
                formats.add("Encyclopedia/Dictionary");
                break;
            }
        }

        // if there is more than one formats, then remove "Other"
        if (formats.size() > 1) {
            formats.remove("Other");
        }

        return formats;
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
        stmt.setString(1, "sirsi");
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

    private static void loadShelvingKeys() {
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
