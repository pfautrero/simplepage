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

global $CFG;
include_once($CFG->dirroot . "/course/format/page/lib/actions/action.class.php");
include_once($CFG->dirroot . "/course/format/page/lib/model/lib.php");

/**
 * Version details
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ajaxmodulepositionAction extends Action 
{

    public function launch(Request $request, Response $response) 
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH, $USER;
        $course = $DB->get_record('course', array('id' => $_SESSION['courseid']));
        $coursecontext = context_course::instance($course->id);

        if (!has_capability('moodle/course:manageactivities', $coursecontext)) {
            $this->render($CFG->dirroot . "/course/format/page/lib/template/forbiddenSuccess.php");
            $this->printOut();
            return;
        }
        global $USER;
        $sesskey = $request->getParam('sesskey');
        if ($sesskey != $USER->sesskey) {
            $response->addVar('content', "Token non valide");
            $this->render($CFG->dirroot . "/course/format/page/lib/template/ajaxSuccess.php");
            $this->printOut();
            return;
        }
        $position = $request->getParam('position');
        $entry = 'formatpage' . $USER->id;
        if ($position == "leftposition")
            $_SESSION[$entry]['position'] = $position;
        if ($position == "rightposition")
            $_SESSION[$entry]['position'] = $position;
        if ($position == "centerposition")
            $_SESSION[$entry]['position'] = $position;
        $content = $position;
        $response->addVar('content', $content);
        $this->render($CFG->dirroot . "/course/format/page/lib/template/ajaxSuccess.php");
        $this->printOut();
    }
}
