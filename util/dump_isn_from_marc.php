<?php
/**
 * Command-line tool to dump all ISSNs/ISBNs from MARC file.
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
require_once 'util/util.inc.php';
require_once 'sys/ConnectionManager.php';

ini_set('memory_limit', '512M');

require_once 'File/MARCXML.php';
require_once 'File/MARC.php';

if (!isset($argv[1]) || !($argv[1] == 'marc' || $argv[1] == 'marcxml')) {
    die("First argument must be marc or marcxml\n");
}

$configArray = readConfig();

$records = ($argv[1] == 'marcxml') 
    ? new File_MARCXML('php://stdin') 
    : new File_MARC('php://stdin');
$idSpec = (isset($argv[2])) ? $argv[2] : null;
$saveMARC = (isset($argv[3])) ? fopen($argv[3], 'a') : false;
$specs = "022a:022y:440x:490x:730x:776x:780x:785x";
$tagSpecs = explode(':', $specs);

while ($record = $records->next()) {
    $id = getRecordId($record, $idSpec);
    
    foreach ($tagSpecs as $spec) {
        $isns = getISNs($record, $spec);
        foreach ($isns as $isn) {
            echo trim($id) . ',' . trim($isn) . "\n";
        }
    }
    if ($saveMARC) {
        fwrite($saveMARC, $record->toRaw());
    }
}

if ($saveMARC) {
    fclose($saveMARC);
}

function getRecordId($record, $spec) {
    $tag = substr($spec, 0, 3);
    $sfSpec = substr($spec, 3);
    if ($tag) {
        $field = $record->getField($tag);
        if ($field) {
            if ($sfSpec) {
                $sf = $field->getSubfield($sfSpec);
                return ($sf) ? (string) $sf->getData() : null;
            }
            return (string) $field->getData();
        }
    }
    return null;
}

function getISNs($record, $spec) {
    $results = array();
    $tag = substr($spec, 0, 3);
    $sfSpecs = substr($spec, 3);
    $fields = $record->getFields($tag);
    foreach ($fields as $field) {
        foreach (str_split($sfSpecs) as $sfCode) {
            $sf = $field->getSubfield($sfCode);
            if ($sf) {
                $isn = strtoupper($sf->getData());
                $isn = preg_replace('/[^0-9X]/', '', $isn);
                if (strlen($isn) == 8) {
                    $results[] = $isn;
                }
            }
        }
    }
    return $results;
}
?>
