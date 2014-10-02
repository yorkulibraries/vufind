package ca.yorku.library.vufind;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

import org.apache.log4j.Logger;
import org.apache.solr.client.solrj.SolrServer;
import org.apache.solr.client.solrj.SolrServerException;
import org.apache.solr.client.solrj.impl.HttpSolrServer;
import org.apache.solr.common.SolrInputDocument;

public class IndexStatuses {
	public static final String AVAILABLE = "Available";
	public static final String CHECKEDOUT = "Checked out";
	public static final String LOST = "Lost";
	public static final String NOT_SUPPRESSED = "no";
	public static final String STATUS_FIELD = "status_str";
	public static final String SUPPRESSED_FIELD = "suppressed_str";

	private static int batchSize = 10000;

	// Initialize logging category
	static Logger logger = Logger.getLogger(IndexStatuses.class.getName());

	private static Collection<SolrInputDocument> batch = new ArrayList<SolrInputDocument>();
	private static SolrServer solr = null;

	public static void main(String[] args) throws SolrServerException,
			IOException {
		batchSize = Integer.valueOf(System.getProperty("batch_size", "100000"));

		String solrUrl = System.getProperty("solr_url",
				"http://localhost:8080/solr/biblio");
		logger.info("Connecting to SOLR server at " + solrUrl);
		solr = new HttpSolrServer(solrUrl);

		String availableFile = System.getProperty("available_file",
				"/tmp/available.txt");
		logger.info("Loading available records from " + availableFile);
		Set<String> available = loadSet(availableFile);
		logger.info("Loadded " + available.size() + " records");

		String checkedoutFile = System.getProperty("checkedout_file",
				"/tmp/checkedout.txt");
		logger.info("Loading checkedout records from " + checkedoutFile);
		Set<String> checkedout = loadSet(checkedoutFile);
		logger.info("Loadded " + checkedout.size() + " records");

		String previouslyCheckedoutFile = System.getProperty(
				"previously_checkedout_file", "/tmp/previously_checkedout.txt");
		logger.info("Loading previously checkedout records from "
				+ previouslyCheckedoutFile);
		Set<String> previouslyCheckedout = loadSet(previouslyCheckedoutFile);
		logger.info("Loadded " + previouslyCheckedout.size() + " records");

		String lostFile = System.getProperty("lost_file", "/tmp/lost.txt");
		logger.info("Loading lost records from " + lostFile);
		Set<String> lost = loadSet(lostFile);
		logger.info("Loadded " + lost.size() + " records");

		String previouslyLostFile = System.getProperty("previously_lost_file",
				"/tmp/previously_lost.txt");
		logger.info("Loading previously lost records from "
				+ previouslyLostFile);
		Set<String> previouslyLost = loadSet(previouslyLostFile);
		logger.info("Loadded " + previouslyLost.size() + " records");

		// process previously lost records
		for (String id : previouslyLost) {
			if (available.contains(id)) {
				SolrInputDocument doc = new SolrInputDocument();
				doc.addField("id", id);
				Map<String, String> partialUpdate = new HashMap<String, String>();
				partialUpdate.put("set", AVAILABLE);
				doc.addField(STATUS_FIELD, partialUpdate);
				partialUpdate = new HashMap<String, String>();
				partialUpdate.put("set", NOT_SUPPRESSED);
				doc.addField(SUPPRESSED_FIELD, partialUpdate);
				add(doc);
			}
		}

		// process previously checkedout records
		for (String id : previouslyCheckedout) {
			if (available.contains(id)) {
				SolrInputDocument doc = new SolrInputDocument();
				doc.addField("id", id);
				Map<String, String> partialUpdate = new HashMap<String, String>();
				partialUpdate.put("set", AVAILABLE);
				doc.addField(STATUS_FIELD, partialUpdate);
				add(doc);
			}
		}

		// process checkedout records
		for (String id : checkedout) {
			SolrInputDocument doc = new SolrInputDocument();
			doc.addField("id", id);
			Map<String, String> partialUpdate = new HashMap<String, String>();
			partialUpdate.put("set", CHECKEDOUT);
			doc.addField(STATUS_FIELD, partialUpdate);
			add(doc);
		}

		// process lost records
		for (String id : lost) {
			SolrInputDocument doc = new SolrInputDocument();
			doc.addField("id", id);
			Map<String, String> partialUpdate = new HashMap<String, String>();
			partialUpdate.put("set", LOST);
			doc.addField(STATUS_FIELD, partialUpdate);
			partialUpdate = new HashMap<String, String>();
			partialUpdate.put("set", LOST);
			doc.addField(SUPPRESSED_FIELD, partialUpdate);
			add(doc);
		}

		if (batch.size() > 0) {
			logger.info("Indexing final batch of " + batch.size());
			solr.add(batch);
		}

		// commit
		solr.commit();
	}

	private static void add(SolrInputDocument doc) throws SolrServerException,
			IOException {
		batch.add(doc);
		if (batch.size() == batchSize) {
			logger.info("Indexing batch of " + batch.size());
			solr.add(batch);
			batch.clear();
		}
	}

	private static Set<String> loadSet(String filename) throws IOException {
		Set<String> set = new HashSet<String>();
		File tmpFile = new File(filename);
		BufferedReader reader = new BufferedReader(new FileReader(tmpFile));
		String line;
		while ((line = reader.readLine()) != null) {
			String[] parts = line.split("\\|");
			if (parts.length > 0) {
				set.add(parts[0].trim());
			}
		}
		reader.close();
		return set;
	}
}
