<?php
/**
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
 */

require_once 'Unicorn.php';
require_once 'RecordDrivers/Factory.php';
require_once 'sys/Resolver/ResolverConnection.php';

class YorkUnicorn extends Unicorn
{
    public function getMyProfile($patron)
    {
        $profile = parent::getMyProfile($patron);
        $profile['pin'] = $patron['cat_password'];
        return $profile;
    }

    public function getStatus($id)
    {
        global $configArray, $logger, $memcache;
        
        $cacheKey = 'Driver::getStatus' . $id;
        if ($memcache) {
            $items = $memcache->get($cacheKey);
            if ($items !== false) {
                $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
                return $items;
            }
        }
        
        $items = parent::getStatus($id);
        
        // put SMIL items first
        $smil_items = array();
        $other_items = array();
        foreach ($items as $item) {
            if ($item['location'] == 'Sound and Moving Image Library') {
                $smil_items[] = $item;
            } else {
                $other_items[] = $item;
            }
        }
        $items = array_merge($smil_items, $other_items);
        
        // put LOST items LAST
        $lost_items = array();
        $other_items = array();
        foreach ($items as $item) {
            if ($item['location'] == 'Lost' || $item['location'] == 'Missing') {
                $lost_items[] = $item;
            } else {
                $other_items[] = $item;
            }
        }
        $items = array_merge($other_items, $lost_items);
        
        if ($memcache) {
            if ($memcache->set($cacheKey, $items, 0, $configArray['Caching']['memcache_ils_status_expiry']) !== false) {
                $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
            }
        }
        return $items;
    }

    public function getStatuses($idList)
    {
        global $configArray, $logger, $memcache;

        $cacheKey = 'Driver::getStatuses' . implode(',', $idList);
        if ($memcache) {
            $statuses = $memcache->get($cacheKey);
            if ($statuses !== false) {
                $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
                return $statuses;
            }
        }
        $statuses = array();

        // get status from ILS
        if (!empty($idList)) {
            $unsorted_statuses = parent::getStatuses($idList);
            foreach ($unsorted_statuses as $group) {
                $smil_items = array();
                $other_items = array();
                // put SMIL items first
                foreach ($group as $item) {
                    if ($item['location'] == 'Sound and Moving Image Library') {
                        $smil_items[] = $item;
                    } else {
                        $other_items[] = $item;
                    }
                }
                $sorted_group = array_merge($smil_items, $other_items);
                
                // put LOST items LAST
                $lost_items = array();
                $other_items = array();
                foreach ($sorted_group as $item) {
                    if ($item['location'] == 'Lost' || $item['location'] == 'Missing') {
                        $lost_items[] = $item;
                    } else {
                        $other_items[] = $item;
                    }
                }
                $sorted_group = array_merge($other_items, $lost_items);
                
                $statuses[] = $sorted_group;
            }
        }
        if ($memcache) {
            if ($memcache->set($cacheKey, $statuses, 0, $configArray['Caching']['memcache_ils_status_expiry']) !== false) {
                $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
            }
        }
        return $statuses;
    }

    protected function parseStatusLine($line)
    {
        $item = parent::parseStatusLine($line);

        // make sure e-reserves records have location INTERNET
        if ($item['item_type'] == 'E-RESERVES' || $item['callnumber'] == 'ELECTRONIC') {
            $item['location_code'] = 'INTERNET';
            $item['current_location_code'] = 'INTERNET';
        }

        // Things get moved around, current location is more reliable than home location
        // Use the current location as the "home" location unless it is CHECKEDOUT
        if ($item['current_location_code'] != 'CHECKEDOUT'
                && $item['current_location_code'] != 'HOLDS') {
            $item['location_code'] = $item['current_location_code'];
        }

        // we map "home" location to the physical home library
        // eg: SCOTT-CIRC => "Scott Library", SCOTT-MAPS => "Map Library" etc...
        $item['location'] = $this->mapLibrary($item['location_code']);

        // if status is "Checked Out", but current location is NOT CHECKEDOUT
        // then it IS Available, this will need some further confirmation from CIRC staff
        // eg: http://www.library.yorku.ca/find/Record/2529502
        // the record in example is charged out, but location is NOT CHECKEDOUT
        if ($item['status'] == 'Checked Out' && $item['current_location_code'] != 'CHECKEDOUT') {
            $item['availability'] = 1;
            $item['status'] = 'Available';
        }

        // Checkout items have generic CHECKEDOUT current location, which is not helpful
        // We return the home location instead. However, if the item is on reserve
        // the home location is most likely not a reserve desk, so
        // return the reserve desk instead of the home location for checkedout reserve items
        if ($item['current_location_code'] == 'CHECKEDOUT' || $item['current_location_code'] == 'HOLDS') {
            if ($item['reserve'] == 'Y' ) {
                if (isset($this->ilsConfigArray['ReserveDesks'][$item['item_type']])) {
                    $item['current_location'] = $this->ilsConfigArray['ReserveDesks'][$item['item_type']];
                    $item['location'] = $this->mapLibrary($item['current_location']);
                } elseif (isset($this->ilsConfigArray['ReserveDesks'][$item['location']])) {
                    $item['current_location'] = $this->ilsConfigArray['ReserveDesks'][$item['location']];
                    $item['location'] = $this->mapLibrary($item['current_location']);
                } else {
                    $item['current_location'] = $item['home_location'];
                }
            } else {
                $item['current_location'] = $item['home_location'];
            }
        }

        if ($item['duedate'] && $item['duedate_raw']) {
            $dateTimeString = strftime('%m/%d/%Y %H:%M', $item['duedate_raw']);
            if (strrpos($dateTimeString, '23:59') === false) {
                $dateFormat = new VuFindDate();
                $timeString = $dateFormat->convertToDisplayTime(
                        'm/d/Y H:i', $dateTimeString
                );
                $item['duedate'] .= ' ' . $timeString;
            }
        }

        return $item;
    }

    public function getPatronByAltId($id)
    {
        return $this->getPatron($id, true);
    }
    
    public function getPatronByEmail($email)
    {
        $email = trim($email);
        if (empty($email)) {
            return false;
        }
    	//query sirsi
    	$params = array(
    			'query' => 'find_patron_by_email',
    			'email' => $email
    	);
    	$response = $this->querySirsi($params);
    	if (empty($response)) {
    		return false;
    	}
    	$matches = explode('^UO', $response);
    	foreach ($matches as $match) {
    	    list($id) = explode('^', $match);
    	    if ($id) {
        	    $result = trim($this->querySirsi(array('query'=>'get_email', 'patronId'=>$id)));
        	    if (strtolower($email) == strtolower($result)) {
        	        return $this->getPatron($id);
        	    }
    	    }
    	}
    	return false;
    }

    public function getPatron($id, $isAltId=false)
    {
        //query sirsi
        $params = array(
                'query' => ($isAltId ? 'get_patron_by_alt_id' : 'get_patron'),
                'patronId' => $id
        );
        $response = $this->querySirsi($params);

        if (empty($response)) {
            return null;
        }

        list($user_key, $alt_id, $barcode, $name, $library, $profile,
                $cat1, $cat2, $cat3, $cat4, $cat5, $pin) = explode('|', $response);

        list($last, $first) = explode(',', $name);
        $first = rtrim($first, " ");

        return array(
                'user_key' => $user_key,
                'alt_id' => $alt_id,
                'barcode' =>  $barcode,
                'name' => $name,
                'firstname' => $first,
                'lastname' => $last,
                'library' => $library,
                'profile' => $profile,
                'cat1' => $cat1,
                'cat2' => $cat2,
                'cat3' => $cat3,
                'cat4' => $cat4,
                'cat5' => $cat5,
                'pin' => $pin
        );
    }

    /**
     * Override to map pickup location to actual LIBR policy since most of our libraries
     * are actually LOCN in Symphony instead of LIBR.
     * @param Array $holdDetails
     */
    public function placeHold($holdDetails)
    {
        $pickup = $holdDetails['pickUpLocation'];
        switch ($pickup) {
            case 'LAW':
                $holdDetails['pickUpLocation'] = 'YORK-LAW';
                break;
            case 'ERC':
                $holdDetails['pickUpLocation'] = 'YORK-EDUC';
                break;
            case 'NELLIE':
                $holdDetails['pickUpLocation'] = 'YORK-NELLIE';
                break;
            default:
                $holdDetails['pickUpLocation'] = 'YORK';
        }
        return parent::placeHold($holdDetails);
    }

    protected function processMarcHoldingLocation($field)
    {
        $library_code  = $field->getSubfield('b')->getData();
        $location_code = $field->getSubfield('c')->getData();
        $location = array(
                'library_code'  => $library_code,
                'library'       => $this->mapLibrary($library_code),
                'location_code' => $location_code,
                'location'      => $this->mapLibrary($location_code),
                'notes'   => array(),
                'marc852' => $field,
                'textual_holdings' => array()
        );
        foreach ($field->getSubfields('z') as $note) {
            $location['notes'][] = $note->getData();
        }
        return $location;
    }

    protected function decodeMarcHoldingRecord($record)
    {
        $locations = array();
        $holdings = array();
        $textuals = array();
        $count = 0;
        foreach ($record->getFields('852|866', true) as $field) {
            switch ($field->getTag()) {
                case '852':
                    $locations[] = $this->processMarcHoldingLocation($field);
                    $count++;
                    break;
                case '866':
                    if ($count > 0) {
                        $textual = '';
                        $subfields = $field->getSubfields();
                        foreach ($subfields as $subfield) {
                            if ($subfield->getCode() != 'x') {
                                $textual .= $subfield->getData() . ' ';
                            }
                        }
                        $locations[$count - 1]['textual_holdings'][] = trim($textual);
                    }
                    break;
            }
        }
        return array($locations, $holdings);
    }

    public function getDepartments()
    {
        return array('RESERVES'=>'Course Reserves');
    }

    public function findReserves($courseId, $instructorId, $departmentId)
    {
        global $configArray;
        
        // load suppressed list
        $suppressedFile = $configArray['Site']['local'] . '/conf/' . $this->ilsConfigArray['Catalog']['suppressed_records_file'];
        $suppressed = file_exists($suppressedFile) ? file($suppressedFile) : array();
        $suppressed = array_map('trim', $suppressed);
        
        // find the reserve records from ILS
        $items = parent::findReserves($courseId, $instructorId, $departmentId);
        
        // filter out suppressed items
        $results = array();
        foreach ($items as $item) {
            if (!in_array($item['BIB_ID'], $suppressed)) {
                $item['DEPARTMENT_ID'] = '';
                $results[] = $item;
            }
        }
        return $results;
    }
}
?>
