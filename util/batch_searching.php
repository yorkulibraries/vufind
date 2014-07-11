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
$field = $argv[2];
$skip = isset($argv[3]) ? $argv[3] : 0;
if (count($argv) < 3) {
    die('Usage: cat inputfile | php util/batch_searching.php query_template fields_to_return skip_number_of_lines');
}
$lineNumber = 1;
while($line = fgets(STDIN)) {
    if ($lineNumber++ <= $skip) {
        continue;
    }
    $line = trim($line);
    if (!empty($line)) {
        $query = buildQuery($template, $line);
        $result = $solr->search($query);
        $fields = str_getcsv($line);
        if ($result['response']['numFound'] == 1) {
            foreach ($result['response']['docs'] as $doc) {
                $value = is_array($doc[$field]) ? $doc[$field][0] : $doc[$field];
                $fields[] = $value;
                fputcsv(STDOUT, $fields);
            }
        } else if ($result['response']['numFound'] == 0) {
            fputcsv(STDERR, $fields);
        }
    }
}

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
