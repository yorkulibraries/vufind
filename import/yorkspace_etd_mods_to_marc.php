<?php
    require 'File/MARC.php';
    require 'File/MARCXML.php';

   $modsDir = isset($argv[1]) ? $argv[1] : null;
   
   if (empty($modsDir)) {
       echo "Usage: php mods2marcxml.php /path/to/input/mods/dir\n";
       exit;
   }
   
   $files = scandir($modsDir);
   $modsFiles = array();
   foreach ($files as $file) {
       $ext = pathinfo($file, PATHINFO_EXTENSION);
       if ($ext == 'xml') {
           $modsFiles[] = $modsDir . '/' . $file;
       }
   }
   
   // Load Stylesheet
   $style = new DOMDocument;
   $style->load('import/xsl/MODS2MARC21slim.xsl');

   // Setup XSLT
   $xsl = new XSLTProcessor();
   $xsl->importStyleSheet($style);
   
   foreach ($modsFiles as $file) {
       $mods = file_get_contents($file);
       $marcxml = null;
       
       // Transform MARCXML
       $doc = new DOMDocument;
       if ($doc->loadXML($mods)) {
           $marcxml = $xsl->transformToXML($doc);
       }
       
       $records = new File_MARCXML($marcxml, File_MARC::SOURCE_STRING, 'marc', true);
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
