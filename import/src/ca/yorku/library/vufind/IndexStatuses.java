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
	public static final String STATUS_FIELD = "status_str_mv";
	public static final int BATCH_SIZE = 10000;
	
	// Initialize logging category
	static Logger logger = Logger.getLogger(IndexStatuses.class.getName());

	public static void main(String[] args) throws SolrServerException,
			IOException {
		String solrUrl = System.getProperty("solr_url",
				"http://localhost:8080/solr/biblio");
		logger.info("Connecting to SOLR server at " + solrUrl);
		SolrServer solr = new HttpSolrServer(solrUrl);

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

		// set of docs to post to solr
		Map<String, SolrInputDocument> docs = new HashMap<String, SolrInputDocument>();
		
		// process previously unavailable records
		for (String id : previouslyUnavailable) {
			if (available.contains(id)) {
				SolrInputDocument doc = docs.get(id);
				if (doc == null) {
					doc = new SolrInputDocument();
					doc.addField("id", id);
					docs.put(id, doc);
				}
				Map<String, String> partialUpdate = new HashMap<String, String>();
				partialUpdate.put("set", AVAILABLE);
				doc.addField(STATUS_FIELD, partialUpdate);
			}
		}

		// process unavailable records
		for (String id : unavailable) {
			SolrInputDocument doc = docs.get(id);
			if (doc == null) {
				doc = new SolrInputDocument();
				doc.addField("id", id);
				docs.put(id, doc);
			}
			Map<String, String> partialUpdate = new HashMap<String, String>();
			partialUpdate.put("set", UNAVAILABLE);
			doc.addField(STATUS_FIELD, partialUpdate);
		}

		// send docs to solr
		Collection<SolrInputDocument> docSet = docs.values();
		Collection<SolrInputDocument> batch = new ArrayList<SolrInputDocument>();
		for (SolrInputDocument doc : docSet) {
			batch.add(doc);
			if (batch.size() == BATCH_SIZE) {
				logger.debug("Indexing batch of " + batch.size());
				solr.add(batch);
				batch = null;
				batch = new ArrayList<SolrInputDocument>();
			}
		}
		if (batch.size() > 0) {
			logger.debug("Indexing final batch of " + batch.size());
		}

		// commit
		solr.commit();
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
