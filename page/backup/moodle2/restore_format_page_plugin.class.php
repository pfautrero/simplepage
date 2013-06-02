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
 * File defining class used to restore simplepage courses
 *
 * @package simplepage
 */

/**
 * Class used to restore simplepage courses
 *
 * @package simplepage
 */
class restore_format_page_plugin extends restore_format_plugin {
/**
 * map xml elements from the backup xml file
 *
 * @return array
 */
    public function define_course_plugin_structure() {
        $paths = array();
        $paths[] = new restore_path_element('format_page', $this->get_pathfor('/pages/page'));
        $paths[] = new restore_path_element('format_page_items', $this->get_pathfor('/pages/page/items/item'));
        error_log('define_course_plugin_structure');
        //var_dump($this);
        return $paths;
    }
/**
 * Insert new pages in format_page table and map IDs
 *
 * @param array $data
 * @return null
 */
    public function process_format_page($data) {
        global $DB;
        $data = (object)$data;
        $oldpageid = $data->id;
        $new_courseid = $this->step->get_task()->get_courseid();
        $data->courseid = $new_courseid; 
        $newpageid = $DB->insert_record('format_page', $data);
        $this->set_mapping('format_page',$oldpageid,$newpageid);
        
    }
/**
 * Insert new items in format_page_items table and map IDs
 *
 * @param array $data
 * @return null
 */    
    public function process_format_page_items($data) {
        global $DB;
        $data = (object)$data;
        $olditemid = $data->id;
        $oldpageid = $data->pageid;
        $data->pageid = $this->get_mappingid('format_page',$oldpageid);
        $newitemid = $DB->insert_record('format_page_items', $data);
	//$this->set_mapping('format_page_items',$olditemid,$newitemid);        
        error_log('process_format_page_items');
    }

/**
 * Fix format_page and format_page_items tables using mapped IDs
 * Update courseid from format_page
 * Update pageid from format_page_items
 * @return null
 */
    public function after_restore_course() {
        global $DB;
        $new_courseid = $this->step->get_task()->get_courseid();
        $pages = $DB->get_records_sql("SELECT * FROM {format_page} WHERE courseid='".$new_courseid."'");
        foreach($pages as $page) {
            if ($page->parent != 0) {
                $oldparentid = $page->parent;
                $page->parent = $this->get_mappingid('format_page',$oldparentid);
                $DB->update_record('format_page', $page);
            }

            $items = $DB->get_records_sql("SELECT * FROM {format_page_items} WHERE pageid='".$page->id."'");
            foreach($items as $item) {
                if ($item->cmid != 0) {
                    $oldmoduleid = $item->cmid;
                    $item->cmid = $this->get_mappingid('course_module',$oldmoduleid);							
                }			      		
                if ($item->blockinstance != 0) {
                    $oldblockid = $item->blockinstance;
                    $item->blockinstance = $this->get_mappingid('block_instance',$oldblockid);							
                }			      	
                $DB->update_record('format_page_items', $item);
            }
        }        
        error_log('after_restore_course');
    }
    
}




