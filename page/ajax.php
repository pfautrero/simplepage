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
 * This file is called by ajax requests and special requests like download file
 *
 * @package simplepage
 * @subpackage frontcontroller
 * @copyright 2013 Pascal Fautrero
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// ===== required files come from /course/view.php
require_once('../../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/filelib.php');

global $LOCAL_PATH, $MAIN_DIR;
/** $MAIN_DIR = "page" **/
$MAIN_DIR = substr(dirname(__FILE__),strpos(dirname(__FILE__),"course/format")+14);
$LOCAL_PATH = ".";
if (file_exists("./format/page/lib/controller/frontcontroller.class.php")) {
	$LOCAL_PATH = "./format/page";
}
include('globals.php');
include_once("../../../lib/setup.php");
include($LOCAL_PATH."/lib/controller/frontcontroller.class.php");
$front = frontController::getInstance()->dispatch();

?>
