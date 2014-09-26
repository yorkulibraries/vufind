package ca.yorku.library.vufind;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import org.apache.log4j.Logger;
import org.apache.solr.client.solrj.SolrServer;
import org.apache.solr.client.solrj.SolrServerException;
import org.apache.solr.client.solrj.impl.HttpSolrServer;
import org.apache.solr.common.SolrInputDocument;

public class IndexStatuses  {
    // Initialize logging category
    static Logger logger = Logger.getLogger(IndexStatuses.class.getName());
    
    
    public static void main(String[] args) throws SolrServerException, IOException {
    	String solrUrl = System.getProperty("solr_url", "http://localhost:8080/solr");
    	SolrServer solr = new HttpSolrServer(solrUrl);
    	SolrInputDocument doc = new SolrInputDocument();
    	doc.addField("id", "2676643");
    	Map<String, String> partialUpdate = new HashMap<String, String>();
    	partialUpdate.put("set", "AVAILABLE");
    	doc.addField("status_str_mv", partialUpdate);
    	solr.add(doc);
    	solr.commit();
    }
}
