<?php
/**
 * Command-line tool to dump all resolver ID from MARC file.
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

require_once 'File/MARC.php';

$configArray = readConfig();

$records = new File_MARC('php://stdin');

$prefixes = array(
    'http://www.library.yorku.ca/eresolver/?id=',
    'http://www.library.yorku.ca/e/resolver/id/'
);


while ($record = $records->next()) {
    $id = $record->getField('035')->getSubfield('a')->getData();
    $fields = $record->getFields('856');
    foreach ($fields as $field) {
        $subfield = $field->getSubField('u');
        if ($subfield) {
            $url = $subfield->getData();
            foreach ($prefixes as $prefix) {
                if (stripos($url, $prefix) !== false) {
                    $rid = substr($url, strlen($prefix));
                    echo trim($id) . ',' . trim($rid) . "\n";
                }
            }
        }
    }
}


?>
