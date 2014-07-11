<?php
/**
 * Command-line tool to extract fields in CVS input.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  Utilities
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/performance#index_optimization Wiki
 */
ini_set('memory_limit', '512M');

/**
 * Set up util environment
 */
require_once 'util.inc.php';
require_once 'sys/ConnectionManager.php';

// Read Config file
$configArray = readConfig();

// Setup Solr Connection 
$solr = ConnectionManager::connectToIndex();

$indices = explode(',', $argv[1]);
$skip = isset($argv[2]) ? $argv[2] : 0;
if (count($argv) < 2) {
    die('Usage: cat inputfile | php util/extract_cvs_fields.php comma_separated_field_indices skip_number_of_lines');
}
$lineNumber = 1;
while($line = fgets(STDIN)) {
    if ($lineNumber++ <= $skip) {
        continue;
    }
    
    $fields = str_getcsv($line);
    $results = array();
    foreach ($indices as $i) {
        $results[] = $fields[$i];
    }
    fputcsv(STDOUT, $results);
}
?>
