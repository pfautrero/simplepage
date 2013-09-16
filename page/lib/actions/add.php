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
include($CFG->dirroot . "/course/format/page/lib/actions/action.class.php");
include_once($CFG->dirroot . "/course/format/page/lib/model/lib.php");

/**
 * Class used to add a new page and to display the dedicated page for this stuff
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2013 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addAction extends Action
{
    public function launch(Request $request, Response $response) 
    {
        global $COURSE, $PAGE, $USER, $CFG;
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);

        if (
            !$PAGE->user_is_editing() || 
            !has_capability(PERMISSION_ADD_NEW_PAGE, $coursecontext)
           ) {
            $this->render(
                $CFG->dirroot 
                . "/course/format/page/lib/template/forbiddenSuccess.php"
            );
            $this->printOut();
            return;
        }
        $id = $request->getParam('id');
        $message = null;
        $adminBlock = null;
        if ($PAGE->user_is_editing()) {
            $adminBlock = SimplePageLib::getAdminBlockAdd($id);
        }
        $pagesparentes = SimplePageLib::getParentPages($id);
        $response->addVar('pagesparentes', $pagesparentes);
        $response->addVar('message', $message);
        $response->addVar('adminBlock', $adminBlock);
        $response->addVar('id', $id);
        $response->addVar('sesskey', $USER->sesskey);
        $response->addVar('editing', $PAGE->user_is_editing());
        $this->render(
            $CFG->dirroot 
            . "/course/format/page/lib/template/addSuccess.php"
        );
        $this->printOut();
    }

}