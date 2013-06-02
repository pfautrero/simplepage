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
 * This file contains general functions for the course format Simplepage
 *
 * @package simplepage
 * @subpackage config
 * @copyright 2013 Pascal Fautrero
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates this format does not use sections.
 *
 * @return bool Returns true
 */
function callback_page_uses_sections() {
    return false;
}

/**
 * Used to display the course structure for a course where format=page (moodle version <= 2.3)
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_page_load_content(&$navigation, $course, $coursenode) {
	global $PAGE,$DB ;	
	$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        /**
         * Manage breadcrumb
         */
        $PAGE->navbar->ignore_active();
        $PAGE->navbar->add('Cours',new moodle_url('/course/'));
        $categ = $DB->get_record_sql("SELECT name FROM {course_categories}  WHERE id = '".$course->category."' ");
        $PAGE->navbar->add($categ->name,new moodle_url('/course/category.php?id='.$course->category));
        $PAGE->navbar->add($course->fullname);

        /**
         * Manage navigation block
         */
	if (has_capability('moodle/course:manageactivities', $coursecontext)) {
		$coursenode = $PAGE->navigation->find($course->id, navigation_node::TYPE_COURSE);
		$children = $coursenode->get_children_key_list();
		$thingnode = $coursenode->find($children[0], navigation_node::TYPE_CONTAINER)
                                        ->add('Export', new moodle_url('/course/format/page/ajax.php?id='.$course->id.'&action=export'));
	}
	return $navigation->load_generic_course_sections($course, $coursenode, 'page');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_page_definition() {
	
	return 'page';
}

/**
 * The GET argument variable that is used to identify the section being
 * viewed by the user (if there is one)
 *
 * @return string
 */
function callback_page_request_key() {
    return 'page';
}

function callback_page_get_section_name($course, $section) {
    // We can't add a node without any text
    if (!empty($section->name)) {
        return format_string($section->name, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
    } else if ($section->section == 0) {
        return get_string('section0name', 'format_page');
    } else {
        return get_string('topic').' '.$section->section;
    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_page_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 9.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}

/**
 * Returns a URL to arrive directly at a section
 *
 * @param int $courseid The id of the course to get the link for
 * @param int $sectionnum The section number to jump to
 * @return moodle_url
 */
function callback_page_get_section_url($courseid, $sectionnum) {
    return new moodle_url('/course/view.php', array('id' => $courseid, 'topic' => $sectionnum));
}

/**
 * class used since moodle 2.4
 * 
 */

if (class_exists('format_base')) {
class format_page extends format_base {
    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return false;
    }
    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                    array('context' => context_course::instance($this->courseid)));
        } else if ($section->section == 0) {
            return get_string('section0name', 'format_page');
        } else {
            return get_string('topic').' '.$section->section;
        }
    } 
    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        $ajaxsupport->testedbrowsers = array('MSIE' => 9.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
        return $ajaxsupport;
    }   
    /**
     * used to manage navigation block and breadcrumb (navbar)
     * for moodle 2.4 and upper
     */    
    public function extend_course_navigation($navigation, navigation_node $coursenode) {

        
	global $PAGE,$DB,$course ;	
        /**
         * Manage breadcrumb
         */
        $PAGE->navbar->ignore_active();
        $PAGE->navbar->add('Cours',new moodle_url('/course/'));
        $categ = $DB->get_record_sql("SELECT name FROM {course_categories}  WHERE id = '".$course->category."' ");
        $PAGE->navbar->add($categ->name,new moodle_url('/course/category.php?id='.$course->category));
        $PAGE->navbar->add($course->fullname,'');        
        /**
         * Manage navigation block
         */        
	$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
	if (has_capability('moodle/course:manageactivities', $coursecontext)) {
		$coursenode = $PAGE->navigation->find($course->id, navigation_node::TYPE_COURSE);
		$children = $coursenode->get_children_key_list();
		$thingnode = $coursenode->find($children[0], navigation_node::TYPE_CONTAINER)
                                        ->add('Export', new moodle_url('/course/format/page/ajax.php?id='.$course->id.'&action=export'));
	}
        
        parent::extend_course_navigation($navigation, $coursenode);        
        
        
    }
    
}
}
