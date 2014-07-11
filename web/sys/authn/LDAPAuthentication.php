<?php
/**
 * LDAP authentication module.
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
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
require_once 'PEAR.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'Authentication.php';
require_once 'LDAPConfigurationParameter.php';

/**
 * LDAP authentication module.
 *
 * @category VuFind
 * @package  Authentication
 * @author   Franck Borel <franck.borel@gbv.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_an_authentication_handler Wiki
 */
class LDAPAuthentication implements Authentication
{
    private $_username;
    private $_password;
    private $_ldapConfigurationParameter;

    /**
     * Constructor
     *
     * @param string $configurationFilePath Optional configuration file path.
     *
     * @access public
     */
    public function __construct($configurationFilePath = '')
    {
        $this->_ldapConfigurationParameter
            = new LDAPConfigurationParameter($configurationFilePath);
    }

    /**
     * Attempt to authenticate the current user.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access public
     */
    public function authenticate()
    {
        $this->_username = $_POST['username'];
        $this->_password = $_POST['password'];
        if ($this->_username == '' || $this->_password == '') {
            return new PEAR_Error('authentication_error_blank');
        }
        $this->_trimCredentials();
        return $this->_bindUser();
    }

    /**
     * Trim the credentials stored in the object.
     *
     * @return void
     * @access private
     */
    private function _trimCredentials()
    {
        $this->_username = trim($this->_username);
        $this->_password = trim($this->_password);
    }

    /**
     * Communicate with LDAP and obtain user details.
     *
     * @return object User object if successful, PEAR_Error otherwise.
     * @access private
     */
    private function _bindUser()
    {
        global $logger;
        
        $ldapConnectionParameter
            = $this->_ldapConfigurationParameter->getParameter();
        
        // Try to connect to LDAP and die if we can't; note that some LDAP setups
        // will successfully return a resource from ldap_connect even if the server
        // is unavailable -- we need to check for bad return values again at search
        // time!
        $logger->log('Attempting to connect to LDAP server' 
                . ' host=' . $ldapConnectionParameter['host'] 
                . ' port=' . $ldapConnectionParameter['port'], PEAR_LOG_DEBUG);
        $ldapConnection = @ldap_connect(
            $ldapConnectionParameter['host'], $ldapConnectionParameter['port']
        );
        if (!$ldapConnection) {
            $logger->log('Failed to connect to LDAP server', PEAR_LOG_ERR);
            return new PEAR_ERROR('authentication_error_technical');
        }
        
        // Set LDAP options -- use protocol version 3
        if (!@ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            $logger->log('Failed to set protocol version 3', PEAR_LOG_WARNING);
        }

        // if the host parameter is not specified as ldaps://
        // then we need to initiate TLS so we
        // can have a secure connection over the standard LDAP port.
        if (stripos($ldapConnectionParameter['host'], 'ldaps://') === false) {
            $logger->log('Attempting to start TLS', PEAR_LOG_DEBUG);
            if (!@ldap_start_tls($ldapConnection)) {
                $logger->log('Failed to start TLS', PEAR_LOG_ERR);
                return new PEAR_ERROR('authentication_error_technical');
            }
        }

        // If bind_username and bind_password were supplied in the config file, use
        // them to access LDAP before proceeding.  In some LDAP setups, these
        // settings can be excluded in order to skip this step.
        if (isset($ldapConnectionParameter['bind_username'])
            && isset($ldapConnectionParameter['bind_password'])
        ) {
            $logger->log('Attempting to bind as ' 
                    . $ldapConnectionParameter['bind_username'], PEAR_LOG_DEBUG);
            $ldapBind = @ldap_bind(
                $ldapConnection, $ldapConnectionParameter['bind_username'],
                $ldapConnectionParameter['bind_password']
            );
            if (!$ldapBind) {
                $logger->log('Failed to bind as '
                        . $ldapConnectionParameter['bind_username']
                        . ' reason=' . ldap_error($ldapConnection), PEAR_LOG_ERR);
                return new PEAR_ERROR('authentication_error_technical');
            }
        }

        // Search for username
        $ldapFilter = $ldapConnectionParameter['username'] . '=' . $this->_username;
        $logger->log('Attempting to search for ' . $ldapFilter
                . ' using basedn='. $ldapConnectionParameter['basedn'], PEAR_LOG_DEBUG);
        $ldapSearch = ldap_search(
            $ldapConnection, $ldapConnectionParameter['basedn'], $ldapFilter
        );
        if (!$ldapSearch) {
            $logger->log('Failed to search for ' . $ldapFilter
                    . ' using basedn='. $ldapConnectionParameter['basedn']
                    . ' reason=' . ldap_error($ldapConnection), PEAR_LOG_ERR);
            return new PEAR_ERROR('authentication_error_technical');
        }

        $info = ldap_get_entries($ldapConnection, $ldapSearch);
        if ($info['count']) {
            // Validate the user credentials by attempting to bind to LDAP:
            $logger->log('Attempting to bind as '
                    . $info[0]['dn'], PEAR_LOG_DEBUG);
            $ldapBind = ldap_bind(
                $ldapConnection, $info[0]['dn'], $this->_password
            );
            if ($ldapBind) {
                $logger->log('Bind successfully as '
                        . $info[0]['dn'], PEAR_LOG_NOTICE);
                // If the bind was successful, we can look up the full user info:
                $ldapSearch = ldap_read(
                    $ldapConnection, $info[0]['dn'], 'objectclass=*'
                );
                $data = ldap_get_entries($ldapConnection, $ldapSearch);
                if ($data === false) {
                    $logger->log('Error reading user details from LDAP'
                            . ' reason=' . ldap_error($ldapConnection), PEAR_LOG_ERR);
                    return new PEAR_ERROR('authentication_error_technical');
                }
                return $this->_processLDAPUser($data, $ldapConnectionParameter);
            } else {
                $logger->log('Failed to bind as ' . $info[0]['dn']
                        . ' reason=' . ldap_error($ldapConnection), PEAR_LOG_ERR);
            }
        } else {
            if ($info === false) {
                $logger->log('Error reading LDAP search results'
                        . ' reason=' . ldap_error($ldapConnection), PEAR_LOG_ERR);
            } else {
                $logger->log('LDAP search found 0 match for ' . $ldapFilter
                    . ' using basedn='. $ldapConnectionParameter['basedn'], PEAR_LOG_ERR);
            }
        }

        return new PEAR_ERROR('authentication_error_invalid');
    }

    /**
     * Build a User object from details obtained via LDAP.
     *
     * @param array $data                    Details from ldap_get_entries call.
     * @param array $ldapConnectionParameter LDAP settings from config.ini.
     *
     * @return User
     * @access private
     */
    private function _processLDAPUser($data, $ldapConnectionParameter)
    {
        global $logger;
        $user = new User();
        $user->username = $this->_username;
        $userIsInVufindDatabase = $this->_isUserInVufindDatabase($user);
        for ($i=0; $i<$data["count"];$i++) {
            for ($j=0;$j<$data[$i]["count"];$j++) {
                if (($data[$i][$j] == $ldapConnectionParameter['firstname'])
                    && ($ldapConnectionParameter['firstname'] != "")
                ) {
                    $user->firstname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['lastname']
                    && ($ldapConnectionParameter['lastname'] != "")
                ) {
                    $user->lastname = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['email']
                    && ($ldapConnectionParameter['email'] != "")
                ) {
                     $user->email = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_username']
                    && ($ldapConnectionParameter['cat_username'] != "")
                ) {
                     $user->cat_username = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['cat_password']
                    && ($ldapConnectionParameter['cat_password'] != "")
                ) {
                     $user->cat_password = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['college']
                    && ($ldapConnectionParameter['college'] != "")
                ) {
                     $user->college = $data[$i][$data[$i][$j]][0];
                }

                if ($data[$i][$j] == $ldapConnectionParameter['major']
                    && ($ldapConnectionParameter['major'] != "")
                ) {
                     $user->major = $data[$i][$data[$i][$j]][0];
                }
            }
        }
        $msg = 'Got the following information from LDAP ' 
            . 'username=' . $user->username . ',firstname=' . $user->firstname
            . ',lastname=' . $user->lastname . ',email=' . $user->email
            . ',cat_username=' . $user->cat_username 
            . ',cat_password=' . $user->cat_password
            . ',college=' . $user->college . ',major=' . $user->major; 
        $logger->log($msg, PEAR_LOG_DEBUG);
        $this->synchronizeVufindDatabaseWithLDAPEntries(
            $userIsInVufindDatabase, $user
        );
        return $user;
    }

    /**
     * Is the specified user already in VuFind's local database?
     *
     * @param User $user User to check
     *
     * @return bool
     * @access private
     */
    private function _isUserInVufindDatabase($user)
    {
        return $user->find(true);
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
        if ($userIsInVufindDatabase) {
            $user->update();
        } else {
            $user->created = date('Y-m-d');
            $user->insert();
        }
    }
}

?>