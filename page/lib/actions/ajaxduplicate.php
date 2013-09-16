﻿<?php
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
include($LOCAL_PATH . "/lib/actions/action.class.php");
include($LOCAL_PATH . "/lib/model/lib.php");

/**
 * Version details
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ajaxduplicateAction extends Action 
{

    public function launch(Request $request, Response $response) 
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH;
        $course = $DB->get_record('course', array('id' => $_SESSION['courseid']));
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

        if (!has_capability(PERMISSION_DUPLICATE_ITEM, $coursecontext)) {
            $this->render($LOCAL_PATH . "/lib/template/forbiddenSuccess.php");
            $this->printOut();
            return;
        }
        global $USER;
        $sesskey = $request->getParam('sesskey');
        if ($sesskey != $USER->sesskey) {
            $response->addVar('content', "Token non valide");
            $this->render($LOCAL_PATH . "/lib/template/ajaxSuccess.php");
            $this->printOut();
            return;
        }
        $current = $request->getParam('current');
        $pageid = $request->getParam('pageid');
        $newmoduleid = SimplePageLib::insertNewItem($current, $pageid);
        $response->addVar('content', $newmoduleid);
        $this->render($LOCAL_PATH . "/lib/template/ajaxSuccess.php");
        $this->printOut();
    }
}