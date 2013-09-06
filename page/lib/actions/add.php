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
 * Class used to add a new page and to display the dedicated page for this stuff
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2013 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addAction extends Action {

    public function launch(Request $request, Response $response)
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH, $USER;	
        global $COURSE;
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if ($coursecontext == null) {
            $content = "context_null";
            $response->addVar('content', $content);
            $this->render($LOCAL_PATH."/lib/template/ajaxSuccess.php");
            $this->printOut();      
            return;
        }
        if (!$PAGE->user_is_editing() || !has_capability(PERMISSION_ADD_NEW_PAGE, $coursecontext)) {
            $this->render($LOCAL_PATH."/lib/template/forbiddenSuccess.php");
            $this->printOut();		
            return;
        }		
        $id=$request->getParam('id');
        $message = null;
        $adminBlock = null;
        if ($PAGE->user_is_editing()) {
            $adminBlock = SimplePage::getAdminBlockAdd($id);
        }
        $pagesparentes = SimplePage::getParentPages($id);
        $response->addVar('pagesparentes', $pagesparentes);
        $response->addVar('message', $message);
        $response->addVar('adminBlock', $adminBlock);
        $response->addVar('id', $id);
        $response->addVar('sesskey', $USER->sesskey);
        $response->addVar('editing', $PAGE->user_is_editing());
        $this->render($LOCAL_PATH."/lib/template/addSuccess.php");
        $this->printOut();
    }
}

?>