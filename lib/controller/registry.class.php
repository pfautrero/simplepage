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
class Registry
{

    public $course;
    public $db;
    public $cfg;
    public $page;
    public $user;
    
    public function __construct()
    {
        global $COURSE, $DB, $CFG, $PAGE, $USER;
        $this->course   = &$COURSE;
        $this->db       = &$DB;
        $this->cfg      = &$CFG;
        $this->page     = &$PAGE;
        $this->user     = &$USER;
     
    }
    
}

