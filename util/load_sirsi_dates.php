<?php
/**
 * Command-line tool to load all SIRSI's "date catalogued" into change_tracker table.
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
$core = (isset($argv[1])) ? $argv[1] : 'biblio';

ConnectionManager::connectToDatabase();
while($line = fgets(STDIN)) {
    list($id, $date) = explode('|', $line);
    $id = '(Sirsi) a' . trim($id);
    $date = trim($date);
    if (strlen($date) != 8) {
        die('Expecting date to be 8-digit YYYYMMDD string, but got: ' . $date);
    }
    $sql = "insert into change_tracker (id, first_indexed, last_indexed, core) values ('{$id}', '{$date}', '{$date}', '{$core}') on duplicate key update id=id";
    $result = mysql_query($sql);
    if (!$result) {
        die(mysql_error() . "\n" . $sql);
    }
}
?>
