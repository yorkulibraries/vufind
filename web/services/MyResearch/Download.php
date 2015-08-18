<?php
require_once 'EZProxy.php';

class Download extends EZProxy
{
    public function __construct()
    {
        global $interface;

        parent::__construct();

        // Load Configuration for this Module
        $config = parse_ini_file('conf/download.ini', true);
        $this->config = array_merge($this->config, $config);
        
        $interface->setPageTitle('Download');
    }

    protected function connect($patron) 
    {
        global $interface;
        
        // get the short path of the file from the request parameter
        $shortPath = $_GET['filename'];
        
        // make sure the path is safe
        if (!$this->isSafePath($shortPath)) {
        	$interface->assign('message', 'The file you requested is not available');
        	$this->display();
        	return;
        }
        
        // get the full path for the file
        $fullPath = $this->config['download_directory'] . '/' . $shortPath;
        
        // make sure the file exists AND readable
        if (!(is_file($fullPath) && is_readable($fullPath))) {
        	$interface->assign('message', 'The file you requested is not available.');
        	$this->display();
        	return;
        }
        
        // send the file
        $this->streamFile($fullPath, isset($_GET['view']));
    }
    
    /**
     * Returns true if the given file path is considered "SAFE" for download.
     */
    protected function isSafePath($path)
    {
    	if (!$path) {
    		return false;
    	}
    
    	// not safe if contains ".." in the path
    	if (ereg('.*\.\..*', $path)) {
    		return false;
    	}
    
    	// not safe if starts with "/"
    	if (ereg('^/.*', $path)) {
    		return false;
    	}
    
    	// TODO: need better rules
    	return true;
    }
    
    /**
     * Returns the MIME type of the given file.
     */
    protected function getMIMEType($file) 
    {
    	$mimetype = $this->getMIMETypeFromExtension($file);
    	if (!$mimetype) {    
    	    $finfo = finfo_open(FILEINFO_MIME);
    		if ($finfo) {
    			$mimetype = finfo_file($finfo, $file);
    			finfo_close($finfo);
    		}
    	}
    	return ($mimetype) ? $mimetype : $this->config['MimeTypes']['default'];
    }
    
    protected function getMIMETypeFromExtension($file) 
    {
        // get file extension
    	$filename = basename($file);
    	$extension = strtolower(substr($filename, strrpos($filename, ".")));    
    	$mimetype = null;
    	if (isset($config['MimeTypes'][$extension])) {
    		$mimetype = $config['MimeTypes'][$extension];
    	}
    
    	return $mimetype;
    }

    /**
     * Streams the given file to the client.
     */
    protected function streamFile($path, $inline=false)
    {
        ob_clean();

    	$mimetype = $this->getMIMEType($path);
    
    	$filename = basename($path);
    	$disposition = ($inline) ? "inline" : "attachment";
    
    	if(strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
    		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    		header("Pragma: public");
    	}
    
    	header("Content-Type: " . $mimetype);
    	header("Content-Disposition: $disposition; filename=$filename");
    	header("Content-Length: " . filesize($path));
    
    	// stream the file
    	readfile($path);
    	
    	ob_end_flush();
    }
}
?>