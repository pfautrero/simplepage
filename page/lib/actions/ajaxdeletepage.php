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

global $CFG;
include_once($CFG->dirroot."/course/format/page/lib/actions/action.class.php");
include_once($CFG->dirroot."/course/format/page/lib/model/lib.php");


/**
 * Version details
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class ajaxdeletepageAction extends Action {

    public function launch(Request $request, Response $response)
    {
		global $CFG, $DB;	
		
		$course = $DB->get_record('course', array('id'=>$_SESSION['courseid']));
		$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                if ($coursecontext == null) {
                    $content = "context_null";
                    $response->addVar('content', $content);
                    $this->render($CFG->dirroot."/course/format/page/lib/template/ajaxSuccess.php");
                    $this->printOut();      
                    return;
                }                
		if (!has_capability(PERMISSION_DELETE_PAGE, $coursecontext)) {
			$this->render($CFG->dirroot."/course/format/page/lib/template/forbiddenSuccess.php");
			$this->printOut();		
			return;
		}
		global $USER;
		$sesskey=$request->getParam('sesskey');
		if ($sesskey != $USER->sesskey) {
			$response->addVar('content', "Token non valide");
			$this->render($CFG->dirroot."/course/format/page/lib/template/ajaxSuccess.php");
			$this->printOut();		
			return;
		}

		$current=$request->getParam('current');
		if (is_numeric($current)) {
			$message = SimplePageLib::deletePage($current);
		}
		$content = $message;
		$response->addVar('content', $content);
		$this->render($CFG->dirroot."/course/format/page/lib/template/ajaxSuccess.php");
		$this->printOut();
    }
}

?>