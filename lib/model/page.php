<?php
namespace simplepage;
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
include_once($CFG->dirroot.'/lib/completionlib.php');

/**
 * Class used to manage Pages
 * One page must be defined in one course
 * 
 * @package    simplepage
 * @subpackage model
 * @copyright  2013 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Page {
    
    public $_id=NULL;
    public $_courseid=0;
    public $_pagename="NEW PAGE";
    public $_pageparentid=0;
    public $_sortorder=0;
    public $_display=7;    
    public $_showbuttons=3;       
    
    function __construct($id=NULL) {
        global $DB;
        if ($id) {
            $page = $DB->get_record_sql("SELECT *
                                        FROM {format_page}
                                        WHERE id = '".$id."'
                                          ");            
            $this->_id = $page->id;
            $this->_courseid = $page->courseid;
            $this->_pagename = $page->nameone;
            $this->_pageparentid = $page->parent;
            $this->_sortorder = $page->sortorder;
            $this->_display = $page->display;
            $this->_showbuttons = $page->showbuttons;            
        }
    }

    /**
     * save page to database
     * 
     */

    public function save() {
        global $DB;        
        $params = new \stdClass;
        if (!$this->_id) {
            $max = $DB->get_records_sql("SELECT MAX(sortorder) as max
                                         FROM {format_page}
                                         WHERE courseid = '".$this->_courseid."'
                                         AND parent='".$this->_pageparentid."'
                                         ");
            $max = array_values($max);
            $params->parent = $this->_pageparentid;
            $params->sortorder = $max[0]->max + 1;
            $params->nameone = addslashes($this->_pagename);
            $params->nametwo = addslashes($this->_pagename);
            $params->display = $this->_display;
            $params->courseid = $this->_courseid;
            $params->prefcenterwidth = 600;
            $params->showbuttons = $this->_showbuttons;
            $new_id = $DB->insert_record('format_page', $params, true);
            $this->_id = $new_id;
            return $new_id;
        }
        else {
            $params->id = $this->_id;
            $params->parent = $this->_pageparentid;            
            $params->sortorder = $this->_sortorder;
            $params->nameone = addslashes($this->_pagename);
            $params->nametwo = addslashes($this->_pagename);
            $params->display = $this->_display;
            $params->courseid = $this->_courseid;
            $params->showbuttons = $this->_showbuttons;            
            $DB->update_record('format_page', $params);
            return $this->_id;
        }

    }  
    /**
     * rename page
     * 
     * @param string $pagename
     * 
     */

    public function rename($pagename) {
        $this->_pagename = $pagename;
    }

    /**
     * check if page is hidden or not
     * 
     * 
     */
    
    public function isHidden() {
        $result = false;
        if ($this->_display == 0) {
            $result = true;
        }
        return $result;
    }     
    /**
     * delete the specified page
     * 
     * 
     */
         
    public function delete() {
        global $DB;
        $rec = $DB->get_records_sql("SELECT * FROM {format_page}  WHERE parent = '".$this->_id."' LIMIT 1");
        if (!$rec) {
            $rec = $DB->get_records_sql("SELECT * FROM {format_page_items}  WHERE pageid = '".$this->_id."' LIMIT 1");
            if (!$rec) {
                $page = array();
                $page['id'] = $this->_id;
                $DB->delete_records('format_page', $page);	
                $message = 'done';
            }
            else {
                $message = get_string('warningDeletePage', 'format_page');
            }
        }
        else {
            $message = "La page ne peut pas être supprimée car elle contient au moins une sous-page.";
        }
        return trim($message);
    }     

    /**
     * NOT YET AVAILABLE
     * Display modules and blocks for a specific page
     * 
     * @param int $this->_id
     * @return string
     * 
     */	
	
    public function getPageItems() {
        global $DB;
        $recs = $DB->get_records_sql("SELECT *
                                        FROM {format_page_items}
                                        WHERE pageid = '".$this->_id."'
                                        ORDER BY position, sortorder ASC
                                          ");

        $result = "<tbody class='dd-list-table' id='page-content-".$this->_id." '>";
        $rank = 0;
        foreach ($recs as $rec) {
                $rec->sortorder = $rank;
                $DB->update_record('format_page_items', $rec);
                $rank++;                        
                if ($rec->blockinstance == '0') {
                        $course_module = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '".$rec->cmid."'");
                        if ($course_module) {
                            $module = $DB->get_record_sql("SELECT * FROM {modules} WHERE id = '".$course_module->module."'");
                            $object = $DB->get_record_sql("SELECT * FROM {".$module->name."} WHERE id = '".$course_module->instance."'");
                            $result .= "<tr class='dd-item' id='item_".$rec->id."' >";
                            $result .= "<td class='dd-handle2'><img src='".MOVE."' alt='".get_string('movePageAlternate','format_page')."' title='".get_string('movePageAlternate','format_page')."' /></td>";
                            $result .= "<td class='cell'>";
                            $result .= "<img class='duplicate duplicate_".$this->_id."' src='".DUPLICATE."' alt='dupliquer' title='".get_string('title10', 'format_page')."' />";
                            if ($rec->visible == 0) {
                                    $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$this->_id." hideactivity' style='margin:2px;' src='".EYE_CLOSED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                            }
                            else {
                                    $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$this->_id."' style='margin:2px;' src='".EYE_OPENED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                            }
                            $result .= "<td class='cell'><input class='defineposition' style='display:none;' type='textbox' size='1' value='".$rec->position."'/>";
                            if ($rec->position == 'l') {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='l' id='radio1_".$rec->id."' checked />";
                            }
                            else {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='l' id='radio1_".$rec->id."' />";
                            }
                            if ($rec->position == 'c') {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='c' id='radio2_".$rec->id."' checked />";
                            }
                            else {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='c' id='radio2_".$rec->id."' />";
                            }
                            if ($rec->position == 'r') {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='r' id='radio3_".$rec->id."' checked />";
                            }
                            else {
                                $result .= "<input class='defineposition_radio defineposition_radio_".$this->_id."' name='position_".$rec->id."' type='radio' value='r' id='radio3_".$rec->id."' />";
                            }
                            $result .= "</td>";


                            $result .= "<td class='cell object_name'><a href='modedit.php?update=".$rec->cmid."&return=0'>".$object->name."</a>";
                            if ($course_module->groupmembersonly) {
                                    $result .= "<img style='width:20px;' src='".GHOST."' alt='fantome' title=\"".get_string('title11', 'format_page')."\" />";
                            }
                            $result .= "</td>";
                            $result .= "<td  style='display:none;'>";

                            $result .= "<input class='defineorder' type='textbox' size='1' value='".$rec->sortorder."'/>";
                            $result .= "</td>";
                            $result .= "<td  style='display:none;' class='type'>module</td>";
                            $result .= "<td class='cell'>".$module->name."</td>";
                            $result .= "<td  style='display:none;' class='id'>".$rec->id."</td>";
                            $result .= "<td class='cell'><img class='deleteitem deleteitem_".$this->_id."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
                            $result .= "</tr>";
                        }
                        else {
                            $result .= "<tr class='dd-item' id='item_".$rec->id."' >";
                            $result .= "<td class='cell'></td>";
                            $result .= "<td class='cell'></td>";
                            $result .= "<td class='cell'></td>";
                            $result .= "<td class='cell'></td>";
                            $result .= "<td class='cell object_name'>".$object->name."</td>";
                            $result .= "<td class='cell'>".$module->name."</td>";
                            $result .= "<td  style='display:none;' class='id'>".$rec->id."</td>";
                            $result .= "<td class='cell'><img class='deleteitem deleteitem_".$this->_id."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
                            $result .= "</tr>";                                    
                        }
                }
                else {
                    $block = $DB->get_record_sql("SELECT * FROM {block_instances} WHERE id = '".$rec->blockinstance."'");
                    if ($block) {
                        $result .= "<tr class='dd-item' id='item_".$rec->id."'>";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell'></td>";
                        if ($rec->visible == 0) {
                                $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$this->_id." hideactivity' style='margin:2px;' src='".EYE_CLOSED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                        }
                        else {
                                $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$this->_id."' style='margin:2px;' src='".EYE_OPENED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                        }
                        $result .= "<td class='cell'><input class='defineposition' type='textbox' size='1' value='".$rec->position."' /></td>";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell' style='display:none;'><input class='defineorder' type='textbox' size='1' value='".$rec->sortorder."' /></td>";
                        $result .= "<td class='cell object_name' style='display:none;' class='type'>bloc</td>";
                        $result .= "<td class='cell'>".$block->blockname."</td>";
                        $result .= "<td class='id' style='display:none;'>".$rec->id."</td>";
                        $result .= "<td class='cell'><img class='deleteitem deleteitem_".$this->_id."' src='".DELETE."' alt='supprimer' title=\"".get_string('title14', 'format_page')."\" /></td>";
                        $result .= "</tr>";
                    }
                    else {
                        $result .= "<tr class='dd-item' id='item_".$rec->id."' >";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell'></td>";
                        $result .= "<td class='cell object_name'>".$object->name."</td>";
                        $result .= "<td class='cell'>".$module->name."</td>";
                        $result .= "<td  style='display:none;' class='id'>".$rec->id."</td>";
                        $result .= "<td class='cell'><img class='deleteitem deleteitem_".$this->_id."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
                        $result .= "</tr>";                                    
                    }                                
                }
        }
        if ($rank == 0) {
            $result .= "<tr><td colspan=7 class='dd-empty'></td></tr>";
        }
        $result .= "</tbody>";

        $result .= "<script>";
        $result .= "$('.dd-handle2').on('mousedown', drag_item);";
        $result .= "$('.showhideactivities_".$this->_id."').on('click',showhide_activities);
                    $('.duplicate_".$this->_id."').on('click', duplicate_item);					
                    $('.deleteitem_".$this->_id."').on('click', deleteitem);
                    $('.defineposition_radio_".$this->_id."').on('click', defineposition_radio);					
                    ";				
        $result .= "</script>";
        return $result;
    }    
    
    /**
     * 
     * 
     * 
     */
	
    public function insertNewItem($item_id) {
        global $DB;
        $rec = $DB->get_record_sql("SELECT *
                                        FROM {format_page_items}
                                        WHERE id = '".$item_id."'
                                         ");
        $max = $DB->get_record_sql("SELECT MAX(sortorder) as maxsortorder
                                        FROM {format_page_items}
                                        WHERE pageid = '".$this->_id."'
                                          ");
        $rec->pageid = $this->_id;
        $rec->sortorder = $max->maxsortorder + 1;
        $new_module = $DB->insert_record('format_page_items', $rec, true);
        return $new_module;
    }  
    
    /**
     * 
     * 
     * 
     */	
	public static function modifyLinkState($link) {

            if ($link=="previous") {
                $this->_showbuttons = $this->_showbuttons ^ PREVIOUS_LINK;
            }
            else {
                $this->_showbuttons = $this->_showbuttons ^ NEXT_LINK;	
            }
	}    

    /**
     * 
     * 
     * 
     */
	
    public function toggleVisibility() {
        if ($this->_display == 0) {
                $this->_display = 7;
        }
        else {
                $this->_display = 0;
        }

    }
   
    
}