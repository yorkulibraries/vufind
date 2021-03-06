<?php
/**
 * minifycss function Smarty plugin
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
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
 * @package  Smarty_Plugins
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_plugin Wiki
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.minifycss.php
 * Type:     function
 * Name:     css
 * Purpose:  Compile LESS files, combine CSS files and then minify
 * -------------------------------------------------------------
 *
 * @param array  $params  Incoming parameter array
 * @param object &$smarty Smarty object
 *
 * @return string        <link> tag for including CSS
 */ // @codingStandardsIgnoreStart
function smarty_function_minifycss($params, &$smarty)
{   // @codingStandardsIgnoreEnd
    // Extract details from the config file, Smarty interface and parameters
    // so we can find CSS files:
    global $configArray;
    require_once('sys/lessc.inc.php');
    
    $path = $configArray['Site']['path'];
    $url = $configArray['Site']['url'];
    $local = $configArray['Site']['local'];
    $themes = explode(',', $smarty->getVuFindTheme());
    $theme = trim($themes[0]);
    $files = explode(',', $params['files']);
    
    $cssFiles = array();
    foreach ($files as $file) {
        $file = trim($file);
        $localFile = "{$local}/interface/themes/{$theme}/css/{$file}";
        if (strrpos($localFile, '.css.less') !== false) {
            $less = $localFile;
            $localFile = substr($localFile, 0, strlen($localFile)-5);
            $lessc = new lessc;
            try {
                $lessc->setVariables(array(
                  "css_url" => "'{$path}/interface/themes/{$theme}/css'"
                ));
                $lessc->checkedCompile($less, $localFile);
                $cssFiles[] = 'css/' . basename($localFile);
            } catch (Exception $e) {
                // TODO: perhaps log this ?
            }
        } else {
            $cssFiles[] = 'css/' . basename($file);
        }
    }

    if (empty($cssFiles)) {
        return '';
    }
    
    $css = implode(',', $cssFiles);
    $baseURL = $params['absolute'] ? $url : $path;
    $href = "{$baseURL}/interface/themes/{$theme}/min/f={$css}";
    $media = isset($params['media']) ? " media=\"{$params['media']}\"" : '';
    return "<link rel=\"stylesheet\" type=\"text/css\"{$media} href=\"{$href}\" />";
}
?>