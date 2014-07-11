<?php
/**
 * Push data out to the my.yorku.ca students portal.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'StudentPortal.php';

/**
 * Push data out to the my.yorku.ca students portal.
 *
 * @category VuFind
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class StudentLibrarian extends StudentPortal
{
    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        global $configArray;
        
        $this->cacheKey = '/student-librarian/';
        $this->cacheExpiry = isset($configArray['Caching']['student_librarian_expiry']) 
            ? $configArray['Caching']['student_librarian_expiry']
            : $configArray['Caching']['memcache_expiry'];
        $this->htmlTemplate = 'Feeds/student_librarian_html.tpl';
        $this->erisURL = $configArray['ErisFeeds']['student_librarian'];
    }
}
?>