<?php
/**
 * York authentication module.
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
 * @package  Authentication
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
require_once 'LDAPAuthentication.php';

/**
 * York authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class YorkAuthentication extends LDAPAuthentication
{
    private $_barcodePattern = '/^29007[0-9]{9}$|^OCULVR$/i';
    private $_username;
    private $_password;
    private $_catalog;
    private $_patron;
    private $_blacklistConfig;
    
    /**
     * Constructor
     *
     * @param string $configurationFilePath Optional configuration file path.
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        parent::__construct($configurationFilePath);
        
        // Load blacklist configuration
        $this->_blacklistConfig = parse_ini_file('conf/blacklist.ini', true);
    }

    /**
     * Attempt to authenticate the current user.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access public
     */
    public function authenticate()
    {
        global $logger;
        $_POST['username'] = str_replace(' ', '', $_POST['username']);
        $_POST['username'] = str_replace('@yorku.ca', '', $_POST['username']);
        $this->_username = trim($_POST['username']);
        $this->_password = trim($_POST['password']);
        if ($this->_username == '' || $this->_password == '') {
            return new PEAR_Error('authentication_error_blank');
        }

        // check REMOTE_ADDR for blacklisted IPs
        foreach ($this->_blacklistConfig['ip_blacklist'] as $blacklist) {
        	$match = strpos($_SERVER['REMOTE_ADDR'], $blacklist) ;
        	if ($match !== false && $match == 0) {
        	    $logger->log('IP address is blacklisted: ' . $_SERVER['REQUEST_URI'], PEAR_LOG_ALERT);
        		return new PEAR_Error('access_not_allowed_ip_blacklisted');
        	}
        }
        
        // Connect to catalog:
        $catalog = ConnectionManager::connectToCatalog();
        if (!($catalog && $catalog->status)) {
            return new PEAR_Error('authentication_error_technical');
        }
        
        if (!preg_match($this->_barcodePattern, $this->_username)) {
            // if user is loggin in with Passport York credentials
            // then we need to authenticate the username/password
            // against Passport York LDAP server FIRST
            $user = parent::authenticate();
            if (Pear::isError($user)) {
                return $user;
            }
            // we got an authenticated VuFind user record
            // whose cat_username SHOULD be the YORK ID
            // because we set cat_username=pyCyin in config.ini
            // now we need to find that user record in Sirsi
            $logger->log('Searching for patron from ILS driver using alt_id=' 
                    . $user->cat_username, PEAR_LOG_NOTICE);
            $this->_patron = $catalog->getPatronByAltId($user->cat_username);
            if (!$this->_patron || PEAR::isError($this->_patron)) {
                $logger->log('No patron record found for alt_id='
                		. $user->cat_username, PEAR_LOG_ERR);
                if (!empty($user->email)) {
                    $logger->log('Searching for patron from ILS driver using email='
                    		. $user->email, PEAR_LOG_NOTICE);
                    $this->_patron = $catalog->getPatronByEmail(trim($user->email));
                    if (!$this->_patron || PEAR::isError($this->_patron)) {
                    	$logger->log('No patron record found for email='
                    			. $user->email, PEAR_LOG_ERR);
                    	return new PEAR_Error('authentication_error_no_patron_record');
                    } else {
                        $logger->log('Found a patron from ILS driver using email='
                        		. $user->email, PEAR_LOG_NOTICE);
                    }
                }
            } else {
                $logger->log('Found a patron from ILS driver using alt_id='
                		. $user->cat_username, PEAR_LOG_NOTICE);
            }
        } else {
            $logger->log('Attempting to login using ILS driver with barcode='
            		. $this->_username, PEAR_LOG_DEBUG);
            $this->_patron = $catalog->patronLogin(
                strtoupper($this->_username), strtoupper($this->_password)
            );
            if (!$this->_patron || PEAR::isError($this->_patron)) {
                $logger->log('Failed to login using ILS driver with barcode='
                		. $this->_username, PEAR_LOG_ERR);
                return new PEAR_Error('authentication_error_invalid');
            } else {
                $logger->log('Login successfully using ILS driver with barcode='
                		. $this->_username, PEAR_LOG_NOTICE);
            }
            $this->_patron['pin'] = strtoupper($this->_password);
        }
        
        // update VuFind user database with patron information and we're done
        return $this->synchronizeVufindDatabaseWithPatron();
    }

    protected function synchronizeVufindDatabaseWithPatron()
    {
        global $logger;
        
        // Check to see if we already have an account for this user:
        $user = new User();
        $user->username = $this->_patron['user_key'];
        $insert = !$user->find(true);
    
        // Update user information based on ILS data:
        $user->firstname = trim($this->_patron['firstname']);
        $user->lastname = trim($this->_patron['lastname']);
        $user->cat_username = trim($this->_patron['barcode']);
        $user->cat_password = trim($this->_patron['pin']);
    
        // Either insert or update the database entry depending on whether or not
        // it already existed:
        if ($insert) {
            $user->created = date('Y-m-d');
            if($user->insert()) {
                $logger->log('Successfully inserted new vufind user username=' 
                        . $user->username . ', cat_username=' 
                        . $user->cat_username, PEAR_LOG_NOTICE);
            } else {
                $logger->log('Error trying to insert new vufind user username=' 
                        . $user->username . ' reason=' . mysql_error(), PEAR_LOG_ERR);
            }
        } else {
            if ($user->update() !== false) {
                $logger->log('Successfully updated existing vufind user username='
                		. $user->username . ', cat_username=' 
                        . $user->cat_username, PEAR_LOG_NOTICE);
            } else {
                 $logger->log('Error trying to update vufind user username='
                		. $user->username . ' reason=' . mysql_error(), PEAR_LOG_ERR);
            }
        }
        setcookie('yulauth', time(), 0, '/', '.library.yorku.ca');
        return $user;
    }
    
    /**
     * Update VuFind's local database with details obtained via LDAP.
     *
     * @param bool $userIsInVufindDatabase Is this a new user (false) or an existing
     * one (true)?
     * @param User $user                   User object to store.
     *
     * @return void
     * @access protected
     */
    protected function synchronizeVufindDatabaseWithLDAPEntries(
        $userIsInVufindDatabase, $user
    ) {
        // nothing to do here
        // we synchronize VuFind database with Sirsi Patron info instead
    }
}
?>
