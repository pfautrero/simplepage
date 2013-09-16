<?php

// This file is part of Simplepage
//
// Simplepage is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Simplepage is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    simplepage
 * @subpackage frontcontroller
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Request 
{
    public function getParam($key) 
    {
        //return filter_var($this->getTaintedParam($key), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        return $this->getTaintedParam($key);
    }

    public function getTaintedParam($key) 
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST[$key])) {
                return $_POST[$key];
            } else {
                return null;
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            if (isset($_GET[$key])) {
                return $_GET[$key];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function route() 
    {
        $matches = array();
        $args = explode('&', parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
        foreach ($args as $arg) {
            $pos = strpos($arg, "action=");
            if ($pos !== false) {
                $matches['action'] = substr($arg, $pos + 7);
            }
        }
        return $matches;
    }
}
