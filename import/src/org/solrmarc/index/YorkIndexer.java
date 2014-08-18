package org.solrmarc.index;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.text.ParseException;

public class YorkIndexer extends VuFindIndexer {

	public YorkIndexer(final String propertiesMapFile,
			final String[] propertyDirs) throws FileNotFoundException,
			IOException, ParseException {
		super(propertiesMapFile, propertyDirs);
		logger.debug("Constructor: YorkIndexer");
	}
	
	public static void main(String[] args) {
	}

}
