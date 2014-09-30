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
	public static final String UNAVAILABLE = "Unvailable";
	public static final String LOST = "Lost";
	public static final String STATUS_FIELD = "status_str";

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

		String unavailableFile = System.getProperty("unavailable_file",
				"/tmp/unavailable.txt");
		logger.info("Loading unavailable records from " + unavailableFile);
		Set<String> unavailable = loadSet(unavailableFile);
		logger.info("Loadded " + unavailable.size() + " records");

		String previouslyUnavailableFile = System.getProperty(
				"previously_unavailable_file",
				"/tmp/previously_unavailable.txt");
		logger.info("Loading previously unavailable records from "
				+ previouslyUnavailableFile);
		Set<String> previouslyUnavailable = loadSet(previouslyUnavailableFile);
		logger.info("Loadded " + previouslyUnavailable.size() + " records");

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

		// process previously unavailable or lost records
		Set<String> previouslyUnavailableOrLost = new HashSet<String>(previouslyUnavailable);
		previouslyUnavailableOrLost.addAll(lost);
		for (String id : previouslyUnavailableOrLost) {
			if (available.contains(id)) {
				SolrInputDocument doc = new SolrInputDocument();
				doc.addField("id", id);
				Map<String, String> partialUpdate = new HashMap<String, String>();
				partialUpdate.put("set", AVAILABLE);
				doc.addField(STATUS_FIELD, partialUpdate);
				add(doc);
			}
		}

		// process unavailable records (but not lost)
		Set<String> unavailableButNotLost = new HashSet<String>(unavailable);
		unavailableButNotLost.removeAll(lost);
		for (String id : unavailableButNotLost) {
			SolrInputDocument doc = new SolrInputDocument();
			doc.addField("id", id);
			Map<String, String> partialUpdate = new HashMap<String, String>();
			partialUpdate.put("set", UNAVAILABLE);
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
