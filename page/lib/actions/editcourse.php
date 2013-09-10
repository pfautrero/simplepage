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


global $LOCAL_PATH,$MAIN_DIR;
include($LOCAL_PATH."/lib/actions/action.class.php");
include($LOCAL_PATH."/lib/model/lib.php");
/**
 * Class used to display edit page
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editcourseAction extends Action {

    public function launch(Request $request, Response $response)
    {
		global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH, $USER, $MAIN_DIR;

		global $COURSE;
		$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
                if ($coursecontext == null) {
                    $content = "context_null";
                    $response->addVar('content', $content);
                    $this->render($LOCAL_PATH."/lib/template/ajaxSuccess.php");
                    $this->printOut();      
                    return;
                }                
		if (!$PAGE->user_is_editing() || !has_capability(PERMISSION_ACCESS_EDIT_PAGE, $coursecontext)) {
			$this->render($LOCAL_PATH."/lib/template/forbiddenSuccess.php");
			$this->printOut();		
			return;
		}
		$_SESSION['courseid'] = $COURSE->id;
		$message = null;
		$id=$request->getParam('id');
		// =============== delete sections
		if ($request->getParam('suppress_sections')) {
			$sesskey=$request->getParam('sesskey');
			if ($sesskey != $USER->sesskey) {
				$message = get_string('invalidToken', 'format_page');
			}		
			else {
				$message = get_string('successSessionDeletion', 'format_page');
			}
		}
		// =============== associate sections and pages
		if ($request->getParam('associate_sections')) {
			$sesskey=$request->getParam('sesskey');
			if ($sesskey != $USER->sesskey) {
				$message = get_string('invalidToken', 'format_page');
			}		
			else {
				$message = get_string('successAssociation', 'format_page');
			}		
		}
		
		$adminBlock = null;
		$tab = SimplePageLib::getChainedPages($id);
		reset($tab);current($tab);$i = key($tab);
		ob_start();
		SimplePageLib::generateHtmlPagesTree($i,$tab, 0);
		$tree = ob_get_contents();
		ob_end_clean();

		reset($tab);current($tab);$i = key($tab);
		ob_start();
		SimplePageLib::generateHtmlPagesTree2($i,$tab, 0);
		$tree2 = ob_get_contents();
		ob_end_clean();                
                
		reset($tab);current($tab);$i = key($tab);
		ob_start();
		SimplePageLib::generatePagesTree($i,$tab, 0);
		$pagestree = ob_get_contents();
		ob_end_clean();
		
		if ($PAGE->user_is_editing()) {
			$adminBlock = SimplePageLib::getAdminBlock($id);
		}

		$course = $DB->get_record_sql("SELECT * FROM {course} WHERE id='$id'");
		$section = 0;
		$vertical = true;
		$return = true;
		get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
		foreach($modnames as $key=>$value) {
			$modnames[$key] = get_string($key, 'format_page');
		}		
		
		// moodle 2.3 : test to see if activity chooser is activated
		$usemodchooser = get_user_preferences('usemodchooser', 1);
		$response->addVar('usemodchooser', $usemodchooser);		
		
		$addmodule = $OUTPUT->box_start('generalbox sitetopic');
		$addmodule.= print_section_add_menus($course, $section, $modnames, $vertical, $return);
		$addmodule.= $OUTPUT->box_end();
		
		$response->addVar('addmodule', $addmodule);

		$response->addVar('tree', $tree);
                $response->addVar('tree2', $tree2);
		$response->addVar('pagestree', $pagestree);
		$response->addVar('message', $message);
		$response->addVar('adminBlock', $adminBlock);
		$response->addVar('id', $id);
		$response->addVar('sesskey', $USER->sesskey);
		$response->addVar('editing', $PAGE->user_is_editing());
                $response->addVar('maindir', $MAIN_DIR);
		$this->render($LOCAL_PATH."/lib/template/editcourseSuccess.php");
		$this->printOut();
    }
}

?>