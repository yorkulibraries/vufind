<?php
/**
 * Command-line tool to load all ISSNs/ISBNs into a database table.
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

$configArray = readConfig();

$table = (isset($argv[1])) ? $argv[1] : null;
$source = (isset($argv[2])) ? $argv[2] : null;
$length = (isset($argv[3])) ? $argv[3] : 8;

if ($table) {
    ConnectionManager::connectToDatabase();
    if ($source != 'sirsi') {
      $sql = "delete from {$table} where source='{$source}'";
      $result = mysql_query($sql);
      if (!$result) {
          die(mysql_error() . "\n" . $sql);
      }
    }
    while($line = fgets(STDIN)) {
        list($id, $isn) = explode(',', $line);
        $id = trim($id);
        $isn = preg_replace('/[^0-9X]/', '', $isn);
        $source = trim($source);
        if ($length && strlen($isn) >= $length) {
            $isn = substr($isn, 0, $length);
        }
        $sql = "insert into {$table} (record_id, number, source) values ('{$id}', '{$isn}', '{$source}') on duplicate key update id=id";
        $result = mysql_query($sql);
        if (!$result) {
            die(mysql_error() . "\n" . $sql);
        }
    }
}
?>
