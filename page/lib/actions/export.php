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

 
global $LOCAL_PATH; 
include($LOCAL_PATH."/lib/actions/action.class.php");
include($LOCAL_PATH."/lib/model/lib.php");

/**
 * Class used to export a list of users in a specified course 
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exportAction extends Action {
	public function getRecords($coursecontextid){
		global $DB;	
	
		$users = $DB->get_records_sql("SELECT u.firstname, u.lastname, u.email 
				FROM {role_assignments} as ra, {user} as u 
				WHERE ra.contextid = '".$coursecontextid."' 
				AND u.id = ra.userid				 
				ORDER BY u.lastname ASC");
		return $users;	
	}
	public function launch(Request $request, Response $response)
	 {
		global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH;	
		if (isset($_GET['id'])) {
			$course = $DB->get_record('course', array('id'=>$_GET['id']));
			$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                        if ($coursecontext == null) {
                            $content = "context_null";
                            $response->addVar('content', $content);
                            $this->render($LOCAL_PATH."/lib/template/ajaxSuccess.php");
                            $this->printOut();      
                            return;
                        }
			if (!has_capability('moodle/course:manageactivities', $coursecontext)) {
				$this->render($LOCAL_PATH."/lib/template/forbiddenSuccess.php");
				$this->printOut();		
				return;
			}
		}
		$rows = array();
		$csv = utf8_decode("nom;prénom;email\n");
		$users = $this->getRecords($coursecontext->id);
		foreach ($users as $user) {
			$csv .= utf8_decode("\"".$user->lastname."\";\"".$user->firstname."\";\"".$user->email."\"\n");	
		}
		header_remove();
		header('Content-Disposition: attachment; filename="participants_'.$course->id.'.csv"');
		header('Content-Type: text/csv; charset=ISO-8859-1');
		echo $csv;
	}
}

?>