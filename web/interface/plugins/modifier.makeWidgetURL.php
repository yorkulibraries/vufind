<?php
/**
 * makeWidgetURL Smarty plugin
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
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_plugin Wiki
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     makeWidgetURL
 * Purpose:  Take a search result URL and turn it into a Widget url
 * -------------------------------------------------------------
 *
 * @param string $url             URL to modify
 *
 * @return string                 URL 
 */ // @codingStandardsIgnoreStart
function smarty_modifier_makeWidgetURL($url, $widget='Carousel', $preview=1)
{   // @codingStandardsIgnoreEnd
    $replacement = '/Widget/' . $widget;
    $url = preg_replace('/\/Search\/(Results|Reserves)/', $replacement, $url);
    $url = str_replace('view=rss', 'view=list', $url);
    if ($preview) {
        $url .=  '&preview=1';
    }
    return $url;
}
?>