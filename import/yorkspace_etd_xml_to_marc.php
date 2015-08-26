<?php
    require 'File/MARC.php';
    require 'File/MARCXML.php';

   $xmlDir = isset($argv[1]) ? $argv[1] : null;
   $stylesheet = isset($argv[2]) ? $argv[2] : null;
   
   if (empty($xmlDir) || empty($stylesheet)) {
       echo "Usage: php $argv[0] /path/to/input/xml/dir /path/to/xsl\n";
       exit;
   }
   
   $files = scandir($xmlDir);
   $xmlFiles = array();
   foreach ($files as $file) {
       $ext = pathinfo($file, PATHINFO_EXTENSION);
       if ($ext == 'xml') {
           $xmlFiles[] = $xmlDir . '/' . $file;
       }
   }
   
   // Load Stylesheet
   $style = new DOMDocument;
   $style->load($stylesheet) || die("Cannot load $stylesheet");

   // Setup XSLT
   $xsl = new XSLTProcessor();
   $xsl->importStyleSheet($style);
   
   foreach ($xmlFiles as $file) {
       $src = file_get_contents($file);
       $marcxml = null;
       
       // Transform MARCXML
       $doc = new DOMDocument;
       if ($doc->loadXML($src)) {
           $marcxml = $xsl->transformToXML($doc);
       }
       $records = new File_MARCXML($marcxml, File_MARC::SOURCE_STRING);
       while ($record = $records->next()) {
           $field = new File_MARC_Data_Field('035', array( new File_MARC_Subfield('a', getIDFromFileName($file))));
           $record->appendField($field);
           print $record->toRaw();
       }
   }
   
   function getIDFromFileName($file) {
       $pieces = explode('_', basename($file, '.xml'));
       $p1 = array_pop($pieces);
       $p2 = array_pop($pieces);
       return $p2 . '-' . $p1;
   }
?>
