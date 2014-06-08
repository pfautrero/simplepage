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
include_once($CFG->dirroot . "/course/format/page/lib/actions/action.class.php");
include_once($CFG->dirroot . "/course/format/page/lib/model/lib.php");
/**
 * Class used to export a list of users in a specified course 
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exportAction extends Action 
{
    public function generateCsv($courseid) 
    {

        $csv = utf8_decode("nom;prénom;rôle;email\n");
        $coursecontext = context_course::instance($courseid);
        $users = $this->_registry->db
                      ->get_records_sql("SELECT   ra.id,
                                                ra.userid, 
                                                u.firstname, 
                                                u.lastname, 
                                                u.email ,
                                                r.shortname as rolename
            FROM {role} as r
            INNER JOIN {role_assignments} as ra ON r.id = ra.roleid
            AND ra.contextid = '".$coursecontext->id."'
            RIGHT OUTER JOIN {user} as u ON ra.userid = u.id
            INNER JOIN {user_enrolments} as ue ON ue.userid = u.id
            INNER JOIN {enrol} as e ON e.id = ue.enrolid
            AND e.courseid = '" . $courseid . "'
            ORDER BY u.lastname ASC");                
       
        $i=0;
        //var_dump($users);
        foreach ($users as $user) {
            $i++;
            $csv .= utf8_decode("\"" 
                    . $user->lastname 
                    . "\";\"" 
                    . $user->firstname 
                    . "\";\"" 
                    . $user->rolename
                    . "\";\""                     
                    . $user->email 
                    . "\"\n");
        }
          return $csv;
        
    }
    
    public function launch(Request $request, Response $response) 
    {
        
        if (isset($_GET['id'])) {
            $course = $this->_registry->db
                            ->get_record('course', array('id' => $_GET['id']));
            $coursecontext = context_course::instance($course->id);

            if (
                !has_capability(
                    'moodle/course:manageactivities', 
                    $coursecontext
                )
            ) {
                $this->render($this->_registry->cfg->dirroot . "/course/format/page/lib/template/forbiddenSuccess.php");
                $this->printOut();
                return;
            }

            $csv = $this->generateCsv($course->id);

            $response->clearHeaders();
            $response->setHeader('Content-Disposition', 'attachment; filename="participants_' . $course->id . '.csv"');
            $response->setHeader('Content-Type', 'text/csv; charset=ISO-8859-1'); // iso-8859 to be compatible with excel !
            $response->addVar('content', $csv);
            $this->render($this->_registry->cfg->dirroot . "/course/format/page/lib/template/ajaxSuccess.php");
            $this->printOut();

        }
    }
}
