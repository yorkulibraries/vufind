<?php
/**
 * Command-line tool run SOLR queries in batch.
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

// Setup Solr Connection -- Allow core to be specified as first command line param.
$solr = ConnectionManager::connectToIndex();

$template = $argv[1];
if (count($argv) < 1) {
    die('Usage: cat inputfile | php util/check_isn.php query_template ');
}
$lineNumber = 1;
$count = 0;
$match = 0;
while($line = fgets(STDIN)) {
    $line = trim($line);
    if (!empty($line)) {
        $query = buildQuery($template, $line);
        $result = $solr->search($query);
        $count++;
        $fields = str_getcsv($line);
        if ($result['response']['numFound'] > 0) {
            foreach ($result['response']['docs'] as $doc) {
                $id = $doc['id'];
                echo "$query matches https://www.library.yorku.ca/find/Record/$id\n";
            }
            $match++;
        }
    }
}
echo "$count ISN searched, $match ISN found\n";

function buildQuery($template, $line) {
    $query = $template;
    $fields = str_getcsv($line);
    for ($i = 0; $i < count($fields); $i++) {
        $field = trim($fields[$i]);
        if (empty($field)) {
            $field = '_no_matching_value_';
        }
        $query = str_replace("{f$i}", $field, $query);
    }
    return $query;
}
?>
