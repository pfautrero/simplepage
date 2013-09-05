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


include_once('../lib/completionlib.php');

/**
 * General class used as simplepage model.
 * 
 * @package    simplepage
 * @subpackage model
 * @copyright  2013 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class SimplePage {

    /**
     * Build html/css drop-down menu
     *
     * method called by DisplayTabs
     *
     * @param array $tabs
     * @param int $index
     * @return string 
     */		
	
	public static function DisplaySpecialTopTabs($tabs, $index) {
		//var_dump($tabs);
		global $DB,$COURSE,$PAGE;
		
		$editing = $PAGE->user_is_editing();
		
		$result = "<div id='simplepage_menu_container'>";
		
		$result .="<ul id='menu'>";
		foreach ($tabs as $tab) {
                    $pages = $DB->get_records_sql("SELECT * FROM {format_page} WHERE parent = '".$tab->tabid."' ORDER BY sortorder ASC");
                    if ($tab->id == $index) {
                            $selected = "selected";
                    }
                    else {
                            $selected= "";
                    }
                    if (!empty($pages)) {
                            $result .= "<li class='menu_left $selected'><a href='".$tab->link."' class='drop'>";
                    }
                    else {
                            $result .= "<li class='menu_left $selected'><a href='".$tab->link."'>";
                    }
                    $result .= $tab->text;
                    $result .= "</a>";
                    if (!empty($pages)) {
                        $result .= '<div class="dropdown_1column">';
                        $result .= '<div class="col_1">';
                        $result .= '<ul class="simple">';
                        //$result .= '<li><a href="'.$tab->link.'">'.get_string("introductionTitle", "format_page").'</a></li>';
                        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
                        foreach ($pages as $page) {
                            if (($page->display & AFFICHER_COMME_ONGLET) && ($page->display & PUBLISH)) {
                                    $result .= '<li><a href="view.php?id='.$page->courseid.'&page='.$page->id.'">'.stripslashes($page->nameone).'</a></li>';
                            }
                            else if (has_capability('moodle/course:manageactivities', $coursecontext)) {
                                    $result .= '<li><a href="view.php?id='.$page->courseid.'&page='.$page->id.'">'.stripslashes($page->nameone).'*</a></li>';
                            }
                        }
                        $result .= '</ul>';
                        $result .= '</div>';
                        $result .= '</div>';
                    }
                    $result .= "</li>";
		}
		$result .= "</ul>";
		$result .= "</div>";
		return $result;
	}
	
    /**
     * Build html/css drop-down menu
     * 
     * @param int $courseid
     * @param int $selectedpage
     * @param array $final_tabs			
     * 
     */	
	public static function DisplayTabs($courseid, $selectedpage, &$final_tabs) {
		global $DB,$COURSE;
		
		$tabs = $row = $inactive = $active = $currenttab = array();
		$result = array();
		$pages = $DB->get_records_sql("SELECT * FROM {format_page} WHERE courseid = $courseid ORDER BY parent,sortorder ASC");
		$i = 0;
		if ($pages) {
			$coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
			foreach ($pages as $page) {
                            if (($page->display & AFFICHER_COMME_ONGLET) && ($page->display & PUBLISH)&& ($page->parent == '0')) {
                                $row[$i] = new tabobject('tab'.$page->id, 'view.php?id='.$page->courseid.'&page='.$page->id, $page->nameone);
                                $row[$i]->tabid=$page->id;
                                $i++;
                            }
                            else if (has_capability('moodle/course:manageactivities', $coursecontext) && ($page->parent == '0')) {
                                $row[$i] = new tabobject('tab'.$page->id, 'view.php?id='.$page->courseid.'&page='.$page->id, $page->nameone."*");
                                $row[$i]->tabid=$page->id;
                                $i++;					
                            }
			}
			$tabs[] = $row;
			//var_dump($tabs);
			reset($pages);
			$index = current($pages)->id;
			if ($selectedpage) {
                            if ($pages[$selectedpage]->parent == '0') {
                                    $index = $selectedpage;
                            }
                            else if ($pages[$pages[$selectedpage]->parent]->parent == '0') {
                                    $index = $pages[$selectedpage]->parent;
                            }
                            else {
                                    $index = $pages[$pages[$selectedpage]->parent]->parent;
                            }
			}
			
			$final_tabs = SimplePage::DisplaySpecialTopTabs($tabs[0], 'tab'.$index);
			$array_pages = array_values($pages);

			for ($j = 0;$j < count($array_pages);$j++) {
				if ($array_pages[$j]->parent == '0') {
					$result[] = $array_pages[$j];
					for($i = 0;$i < count($array_pages);$i++) {
						if ($array_pages[$i]->parent == $array_pages[$j]->id) {
							$result[] = $array_pages[$i];
							for($k = 0;$k < count($array_pages);$k++) {
								if ($array_pages[$k]->parent == $array_pages[$i]->id) {
									$result[] = $array_pages[$k];
								}
							}						
						}
					}
				}
			}
		}
		return $result;

	}

	
    /**
     * generate adminblock for edit page
     * 			
     * 
     */		

	public static function getAdminBlock($id) 
	{	
            global $OUTPUT;
            $adminBlock = null;
            $bc = new block_contents();
            $content = "<ul>";
            $content .= "<a href='view.php?id=$id'><li>".get_string('seeCourse', 'format_page')."</li></a>";
            $content .= "<a href='view.php?id=$id&action=add'><li>".get_string('addPage', 'format_page')."</li></a>";
            $content .= "</ul>";
            $bc->content = $content;
            //$bc->footer = "test de pied";
            $bc->title = get_string('titleAdminPanel', 'format_page');
            $bc->collapsible = block_contents::VISIBLE;
            $bc->blockinstanceid = "admin1337"; // indispensable pour que collapsible fonctionne
            $bc->id = "admin1337";
            $bc->attributes = array("id"=>"inst".$bc->id, "class"=>"block");
            $adminBlock = $OUTPUT->block($bc, null);
            $adminBlock = SimplePage::stringFilter($adminBlock);	
            return $adminBlock;
	}
	
    /**
     *  generate adminblock for add page
     * 			
     * @param int $courseid     course id
     * @return string
     * 
     */	

	public static function getAdminBlockAdd($courseid) 
	{	
            global $OUTPUT;
            $adminBlock = null;
            $bc = new block_contents();
            $content = "<ul>";
            $content .= "<a href='view.php?id=$courseid'><li>".get_string('seeCourse', 'format_page')."</li></a>";
            $content .= "<a href='view.php?id=$courseid&action=editcourse'><li>".get_string('editCourse', 'format_page')."</li></a>";
            $content .= "</ul>";
            $bc->content = $content;
            //$bc->footer = "test de pied";
            $bc->title = get_string('titleAdminPanel', 'format_page');
            $bc->collapsible = block_contents::VISIBLE;
            $bc->blockinstanceid = "admin1337"; // indispensable pour que collapsible fonctionne
            $bc->id = "admin1337";
            $bc->attributes = array("id"=>"inst".$bc->id, "class"=>"block");
            $adminBlock = $OUTPUT->block($bc, null);
            $adminBlock = SimplePage::stringFilter($adminBlock);	
            return $adminBlock;
	}
	
    /**
     *  generate adminblock for index page only
     * 			
     * @param int $id   course id
     * @return string
     */		
	
	public static function getAdminBlockIndex($id) 
	{	
            global $OUTPUT;
            $adminBlock = null;
            $bc = new block_contents();
            $content = "<ul>";
            $content .= "<a href='view.php?id=$id&action=add'><li>".get_string('addPage', 'format_page')."</li></a>";
            $content .= "<a class='addnewmodule' href='#'><li>".get_string('addModule', 'format_page')."</li></a>";
            $content .= "<a href='view.php?id=$id&action=editcourse'><li>".get_string('editCourse', 'format_page')."</li></a>";
            $content .= "</ul>";
            $bc->content = $content;
            //$bc->footer = "test de pied";
            $bc->title = get_string('titleAdminPanel', 'format_page');			//"panneau d'admin";
            $bc->collapsible = block_contents::VISIBLE;
            $bc->blockinstanceid = "admin1337"; // indispensable pour que collapsible fonctionne
            $bc->id = "admin1337";
            $bc->attributes = array("id"=>"inst".$bc->id, "class"=>"block");
            $adminBlock = $OUTPUT->block($bc, null);
            $adminBlock = SimplePage::stringFilter($adminBlock);	
            return $adminBlock;
	}	

    /**
     * retrieve courseid from pageid
     * 
     * @param int $pageid
     * @return int
     * 
     */
	
	public static function getCourseidForPage($pageid) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = '".$pageid."' ");
            return $rec->courseid;
	}	


    /**
     * method used to retrieve pagemenu links from flexpage 1 plugin (used for retro-compatibility)
     * 			
     * 
     */	
	public static function getPagemenuLinks($id, $courseid) {
            global $DB, $PAGE;
            $pagemenu_links = $DB->get_records_sql("SELECT * FROM {pagemenu_links} WHERE pagemenuid = $id ORDER BY previd ASC ");
            $i = key($pagemenu_links);
            $value = "<ul>";
            while ($pagemenu_links[$i]->nextid != '0') {
                $pagelinks = $DB->get_records_sql("SELECT * FROM {pagemenu_link_data} WHERE linkid = $i ORDER BY name ASC");
                $link2add = null;

                foreach ($pagelinks as $pagelink) {
                    if ($pagelink->name == "moduleid") {
                        $module = $DB->get_record_sql("SELECT m.name as module_name, cm.instance as module_instance
                                                            FROM {course_modules} as cm, {modules} as m 
                                                            WHERE cm.id = '".$pagelink->value."' 
                                                            AND m.id = cm.module");
                        if ($module) {													
                            $module_item = $DB->get_record_sql("SELECT name FROM {".$module->module_name."} 
                                                                    WHERE id = '".$module->module_instance."' ");
                            $link2add = "<li>
                                            <a href='/mod/".$module->module_name."/view.php?id=".$pagelink->value."'>".
                                            $module_item->name.
                                            "</a>
                                         </li>";
                        }

                    }
                    elseif ($pagelink->name == "linkname") {
                            $linkname = $pagelink->value;

                    }
                    elseif ($pagelink->name == "linkurl") {
                            $link2add = "<li><a href='".$pagelink->value."'>".$linkname."</a></li>";

                    }				
                    elseif ($pagelink->name == "pageid") {
                            $page = $DB->get_record_sql("SELECT nameone FROM {format_page} WHERE id = '".$pagelink->value."' ");
                            $link2add = "<li>
                                            <a href='course/view.php?id=".$courseid."&page=".$pagelink->value."'>".
                                            $page->nameone.
                                            "</a>
                                         </li>";
                    }			
                }
                $value .= $link2add;
                $i = $pagemenu_links[$i]->nextid;
            }
            $value .="</ul>";

            return $value;

	}


	
    /**
     * 
     * 
     * 
     */
	
	public static function getTab($id) {
            global $DB;
            $tab = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = $id");
            return $tab;
	}
    /**
     * Retrieve blocks included in pages from flexpage 1 (retro-compatibility)
     * 
     * 
     */
	
	public static function getBlocks($page) {
            global $DB;
            $rec = $DB->get_records_sql("SELECT fpi.*, bi.*, fpi.id as item_id
                                            FROM {format_page_items} as fpi, {block_instances} as bi
                                            WHERE fpi.pageid = '$page'
                                            AND fpi.visible = '1'
                                            AND fpi.cmid = '0'
                                            AND bi.id = fpi.blockinstance
                                            ORDER BY fpi.sortorder ASC
                                              ");
              return $rec;

	}

    /**
     * used for debug and maintenance only
     * 
     * 
     */
	
	public static function getCourseBlocks($course) {
            global $DB;
            $rec = $DB->get_records_sql("SELECT p.id, pi.pageid,  bi.blockname, bi.parentcontextid
                                            FROM {format_page_items} as pi, {block_instances} as bi, {format_page} as p
                                            WHERE p.courseid = '$course'
                                            AND pi.pageid = p.id
                                            AND pi.cmid = '0'
                                            AND bi.id = pi.blockinstance
                                            AND pi.visible = '1'
                                                              ");
            return $rec;

	}
    /**
     * used for debug and maintenance only
     * 
     * 
     */
	
	public static function getBlocksCoursesFlexPage() {
            global $DB;

            $recs = $DB->get_records_sql("SELECT  bi.*
                                            FROM {format_page_items} as fpi, {block_instances} as bi
                                            WHERE fpi.cmid = '0'
                                            AND bi.id = fpi.blockinstance
                                                             ");
            foreach ($recs as $rec) {
                    $rec->parentcontextid = 0;
                    $DB->update_record('block_instances', $rec);
            }
            return count($recs);
	}

    /**
     * Retrieve SimplePage courses that should be deleted - for debug only
     *	
     * 
     * 
     */
	
	public static function getCoursesFlexPage() {
            global $DB;

            $rec = $DB->get_records_sql("SELECT DISTINCT fp.courseid FROM {format_page} as fp
                                                WHERE fp.courseid NOT IN 
                                                (SELECT c.id FROM {course} as c)
                                                              ");
            return $rec;
	}
    /**
     * Retrieve number of Simplepage courses 
     *	
     * 
     * 
     */

    public static function getMoodleCourses() {
            global $DB;
            $rec = $DB->get_records_sql("SELECT DISTINCT c.id FROM {course} as c WHERE format='page' ");
            return count($rec);
    }
    /**
     * 
     * 
     * 
     */
	
	public static function getBlockInstance($id) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT * FROM {block_instances} WHERE id = '".$id."' ");
            $result = array_values($rec);
            return $result[0];
	}
    /**
     * Retrieve modules from a specific page according to specific user permissions
     * 
     * @param $mypage   page id
     * @param $uid      user id
     * @return array
     * 
     */	
	public static function getCourseModules($mypage, $uid) {
            global $DB, $COURSE, $PAGE;
            $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
            $manageactivities = false;$viewhiddenactivities = false;
            if (has_capability('moodle/course:manageactivities', $coursecontext)) {
                    $manageactivities = true;
            }
            if (has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
                    $viewhiddenactivities = true;
            }		

            if ($manageactivities || $viewhiddenactivities) {
                    $recs = $DB->get_records_sql("SELECT *
                                                        FROM {format_page_items}
                                                        WHERE pageid = '$mypage' 
                                                        AND blockinstance = '0'
                                                        ORDER BY sortorder ASC");
            }
            else {
                    $recs = $DB->get_records_sql("SELECT *
                                                        FROM {format_page_items}
                                                        WHERE pageid = '$mypage' 
                                                        AND blockinstance = '0'
                                                        AND visible = '1'
                                                        ORDER BY sortorder ASC");		
            }

            $config = $DB->get_record_sql("SELECT * FROM {config} WHERE name='enableavailability'");

            $completion = new completion_info($COURSE);
            $completion_enabled = false;
            if ($completion->is_enabled_for_site() && $completion->is_enabled()) {
                $completion_enabled = true;
            }                

            $modules = array();$i = 0;
            foreach ($recs as $rec) {
                $course_module = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '".$rec->cmid."'");
                if ($course_module) {
                    $display = 0;
                    $module_completion = 0;
                    // ===================== Verify user privileges
                    if ($manageactivities || !$course_module->groupmembersonly) {
                            $display = 1;
                    }
                    else {
                        if ($course_module->groupmembersonly) {
                            $rec2 = $DB->get_record_sql("SELECT gm.id
                                                         FROM {groups_members} as gm, {groupings_groups} as gg
                                                         WHERE gg.groupingid = '".$course_module->groupingid."' 
                                                         AND gg.groupid = gm.groupid 
                                                         AND gm.userid = '".$uid."'
                                                         ");			
                            if ($rec2) $display = 1;
                        } 
                    }

                    // ===================== Manage display if completion is used
                    
                    if ($completion_enabled && $completion->is_enabled($course_module)) {
                        if ($course_module->completion == COMPLETION_TRACKING_MANUAL) {
                            $current = $completion->get_data($course_module,null,$uid);
                            if ($current->completionstate == COMPLETION_COMPLETE) {
                                $module_completion = 1;
                            }
                            else {
                                $module_completion = 2;
                            }
                        }
                    }

                    // ===================== Manage display if conditional date system is used

                    if ($config) {
                        if ($config->value == 1) {
                            if ($course_module->availablefrom) {
                                $display = 0;
                                if (time() > $course_module->availablefrom) {
                                        $display = 1;
                                }
                                elseif (($course_module->showavailability == 1) || $manageactivities) $display = 2;  							
                            }
                            if ($course_module->availableuntil) {
                                $display = 0;
                                if (time() < $course_module->availableuntil) {
                                        $display = 1;
                                }
                                elseif(($course_module->showavailability == 1) || $manageactivities) $display = 2;							
                            }
                        }							
                    }
                    // ===================== If user has right permissions, display current module
                    if ($display) {
                        $module = $DB->get_record_sql("SELECT * FROM {modules} WHERE id = '".$course_module->module."'");
                        $modulecontext = $DB->get_record_sql("SELECT * FROM {context} WHERE instanceid = '".$rec->cmid."' AND contextlevel = '70'");
                        $modules[$i]['object'] = $DB->get_record_sql("SELECT * FROM {".$module->name."} WHERE id = '".$course_module->instance."'");
                        $modules[$i]['type'] = $module->name;
                        $modules[$i]['cmid'] = $rec->cmid;
                        $modules[$i]['position'] = $rec->position;
                        $modules[$i]['sortorder'] = $rec->sortorder;
                        $modules[$i]['visible'] = $rec->visible;
                        $modules[$i]['id'] = $rec->id;
                        $modules[$i]['context'] = $modulecontext->id;
                        $modules[$i]['display_mode'] = $display;
                        $modules[$i]['completion'] = $module_completion;
                        $i++;
                    }
                }
            }
            return $modules;

	}
    /**
     * 
     * 
     * 
     */	
	public static function getParentPages($courseid) {
            global $DB;
            $result = array();
            $pages_parentes = $DB->get_records_sql("SELECT *
                                                       FROM {format_page}
                                                       WHERE courseid = $courseid
                                                       AND parent='0'
                                                       ORDER BY sortorder ASC");
            $i = 0;
            foreach ($pages_parentes as $page_parente) {
                $pages_filles = $DB->get_records_sql("SELECT *
                                                       FROM {format_page}
                                                       WHERE parent = '".$page_parente->id."'
                                                       ORDER BY sortorder ASC
                                                       ");
                $result[$i]['name'] = $page_parente->nameone;
                $result[$i]['id'] = $page_parente->id;
                $result[$i]['level'] = 0;
                $i++;
                foreach($pages_filles as $page_fille) {
                        $result[$i]['name'] = $page_fille->nameone;
                        $result[$i]['id'] = $page_fille->id;				
                        $result[$i]['level'] = 1;
                        $i++;
                }
            }
            return $result;
    }
    /**
     * 
     * 
     * 
     * 
     */	
	public static function getChainedPages($courseid) {
            global $DB;
            $tab = array();
            $pages = $DB->get_records_sql("SELECT * FROM {format_page} WHERE courseid = $courseid ORDER BY parent, sortorder ASC");
            foreach ($pages as $page) {
                    $tab[$page->id] = $page;
            }
            SimplePage::generateChain($tab);

            return $tab;
	}
	
    /**
     * 
     * 
     * 
     */
	public static function generateChain(&$t) {
            $frere = array();$enfant = array();
            while (current($t)) {
                $i = key($t);
                $parent = $t[$i]->parent;
                if ($parent === NULL) $parent = 0;
                if (isset($frere[$parent])) {
                        $t[$i]->previous = $frere[$parent];
                        $t[$frere[$parent]]->next = $i;
                        $frere[$parent] = $i;
                }
                else {
                        $frere[$parent] = $i;
                        if ((!isset($enfant[$parent])) && ($parent !=0)) {
                                $enfant[$parent] = 'done';
                                $t[$parent]->child = $i;
                        }
                }
                next($t);
            }
	}
	
    /**
     * create HTML string representing pages tree (old)
     * 
     * @param int $i
     * @param array $tab
     * @param string $indent
     */
	public static function generateHtmlPagesTree($i,$tab, $indent) {
            $end = false;

            while (isset($tab[$i]->id) && !$end) {

                echo "<li style='margin-left:".$indent."px'>\n";
                echo "<div class='moveable' style='background-color:#eeeeee;float:left;padding:0px;'>\n";
                echo "<img  style='margin:2px;' src='".MOVE."' alt='".get_string('movePageAlternate','format_page')."' title='".get_string('movePageAlternate','format_page')."' />";	
                echo "</div>";
                echo "<div style='background-color:#eeeeee;float:left;padding:0px;'>\n";
                if ($tab[$i]->display == 0) {			
                        echo "<img class='showhide' style='margin:2px;' src='".EYE_CLOSED."' alt='".get_string('showhidePageAlternate','format_page')."' title='".get_string('title2', 'format_page')."' />";	
                }
                else {
                        echo "<img class='showhide hidepage' style='margin:2px;' src='".EYE_OPENED."' alt='cacher-afficher' title='".get_string('title3', 'format_page')."' />";				
                }
                echo "<img class='showactivities' style='margin:2px;' src='".EXPAND."' alt='afficher activites' title='".get_string('title4', 'format_page')."' />";
                echo "<img class='addmodule' style='margin:2px;' src='".ADD_MODULE."' alt='ajouter module' title='".get_string('title5', 'format_page')."' />";	
                if ($tab[$i]->showbuttons & PREVIOUS_LINK) {			
                        echo "<img class='linkpreviouspage showlink' style='margin:2px;width:17px;' src='".PREVIOUS_ENABLED."' alt='lien precedent' title='".get_string('title6', 'format_page')."' />";
                }
                else {
                        echo "<img class='linkpreviouspage' style='margin:2px;width:17px;' src='".PREVIOUS_DISABLED."' alt='lien precedent' title='".get_string('title6', 'format_page')."' />";
                }

                if ($tab[$i]->showbuttons & NEXT_LINK) {			
                        echo "<img class='linknextpage showlink' style='margin:2px;width:17px;' src='".NEXT_ENABLED."' alt='lien suivant' title='".get_string('title7', 'format_page')."' />";
                }
                else {
                        echo "<img class='linknextpage' style='margin:2px;width:17px;' src='".NEXT_DISABLED."' alt='lien suivant' title='".get_string('title7', 'format_page')."' />";
                }
                echo "</div>\n";
                echo "<input class='input_course' name=\"".$tab[$i]->id."\" value=\"".stripslashes($tab[$i]->nameone)."\" size='35' title='".get_string('title8', 'format_page')."'/>";
                echo "<a href='/course/view.php?id=".$tab[$i]->courseid."&page=".$tab[$i]->id."'><img style='margin:2px;width:17px;' src='".SEE_PAGE."' alt='voir la page' title='Voir la page' /></a>";
                echo "<div style='float:right;'><img class='deletepage' style='margin:2px;' src='".CROSS."' alt='supprimer' title='".get_string('title9', 'format_page')."' /></div>";	
                echo "<div style='clear:both;'></div>";
                echo "<div class='activities_container hideactivities' style='margin-left:20px;margin-right:20px;'></div>";

                if (isset($tab[$i]->child)) {
                        echo "<ol>";
                        SimplePage::generateHtmlPagesTree($tab[$i]->child, $tab, $indent);
                        echo "</ol>";
                }
                if (!isset($tab[$i]->next)) {
                        $end = true;
                }
                else {
                        $i = $tab[$i]->next;
                }
                echo "</li>\n";
            }
	}

    /**
     * create HTML string representing pages tree (version 2)
     * 
     * @param int $i
     * @param array $tab
     * @param string $indent
     */
	public static function generateHtmlPagesTree2($i,$tab, $indent) {
            $end = false;

            while (isset($tab[$i]->id) && !$end) {

                echo "<li class='dd-item' id='page_".$tab[$i]->id."'>\n";
                echo "<span class='dd-handle'>\n";
                echo "<img src='".MOVE."' alt='".get_string('movePageAlternate','format_page')."' title='".get_string('movePageAlternate','format_page')."' />";	
                echo "</span>";
                if ($tab[$i]->display == 0) {	
                    echo "<img class='showhide' src='".EYE_CLOSED."' alt='".get_string('showhidePageAlternate','format_page')."' title='".get_string('title2', 'format_page')."' />";	
                }
                else {
                    echo "<img class='showhide hidepage' src='".EYE_OPENED."' alt='cacher-afficher' title='".get_string('title3', 'format_page')."' />";				
                }
                echo "<img class='showactivities' src='".FOLDER."' alt='afficher activites' title='".get_string('title4', 'format_page')."' />";
                echo "<img class='addmodule' src='".ADD_MODULE."' alt='ajouter module' title='".get_string('title5', 'format_page')."' />";	
                if ($tab[$i]->showbuttons & PREVIOUS_LINK) {			
                    echo "<img class='linkpreviouspage showlink' src='".PREVIOUS_ENABLED."' alt='lien precedent' title='".get_string('title6', 'format_page')."' />";
                }
                else {
                    echo "<img class='linkpreviouspage' src='".PREVIOUS_DISABLED."' alt='lien precedent' title='".get_string('title6', 'format_page')."' />";
                }

                if ($tab[$i]->showbuttons & NEXT_LINK) {			
                    echo "<img class='linknextpage showlink' src='".NEXT_ENABLED."' alt='lien suivant' title='".get_string('title7', 'format_page')."' />";
                }
                else {
                    echo "<img class='linknextpage' src='".NEXT_DISABLED."' alt='lien suivant' title='".get_string('title7', 'format_page')."' />";
                }
                echo "<input class='input_course' name=\"".$tab[$i]->id."\" value=\"".stripslashes($tab[$i]->nameone)."\" size='35' title='".get_string('title8', 'format_page')."'/>";
                echo "<img class='edit_title' src='".EDIT."' alt='éditer le titre' title='".get_string('title7', 'format_page')."' />";                        
                //echo "<a href='/course/view.php?id=".$tab[$i]->courseid."&page=".$tab[$i]->id."'><img style='margin:2px;width:17px;' src='".SEE_PAGE."' alt='voir la page' title='Voir la page' /></a>";
                echo "<img class='deletepage' src='".DELETE."' alt='supprimer' title='".get_string('title9', 'format_page')."' />";	
                echo "<table class='dd modules_table hideactivities' id='table_".$tab[$i]->id."'>
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Position</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th></th>
                            </tr>										
                        </thead>
                    </table>";
                if (isset($tab[$i]->child)) {
                    echo "<ol class='dd-list'>";
                    SimplePage::generateHtmlPagesTree2($tab[$i]->child, $tab, $indent);
                    echo "</ol>";
                }
                if (!isset($tab[$i]->next)) {
                    $end = true;
                }
                else {
                    $i = $tab[$i]->next;
                }
                echo "</li>\n";
            }
	}        
        
        
        
    /**
     * Recursive method used to generate the entire tree pages of selected course
     * 
     * 
     * 
     */
	public static function generatePagesTree($i,$tab, $indent) {
            $end = false;
            while (isset($tab[$i]->id) && !$end) {
                echo "<option style='padding-left:".$indent."px;' value='".$tab[$i]->id."'>".$tab[$i]->nameone."</option>"; 
                if (isset($tab[$i]->child)) {
                        SimplePage::generatePagesTree($tab[$i]->child, $tab, $indent+10);
                }
                if (!isset($tab[$i]->next)) {
                        $end = true;
                }
                else {
                        $i = $tab[$i]->next;
                }
            }
	}	
	
	
    /**
     * When a page 'n' is moved from a parent page, we can fill the gap by reordering pages 'n+1', 'n+2'...
     * 
     * @param int $current
     * @param int $courseid
     * 
     */	
	public static function reorderCurrentBrothers($pageid,$courseid) {
            global $DB;

            $rec = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = '".$pageid."' ");

            $parent = $rec->parent;
            $rank = $rec->sortorder;
            $recs = $DB->get_records_sql("SELECT * FROM {format_page} WHERE parent = '".$parent."' AND courseid='".$courseid."' ORDER BY sortorder ASC ");
            foreach($recs as $rec) {
                if ($rec->sortorder > $rank) {
                        $rec->sortorder = $rec->sortorder-1;
                        $DB->update_record('format_page', $rec);
                }
            }
	}
    /**
     * When a page is added just behind page 'n', we first reorder page 'n+1', 'n+2'
     * 
     * 
     */
     	
	public static function reorderPreviousBrothers($previous, $parent, $current,$id) {
            global $DB;

            if ($previous != 'undefined') {
                    $rec = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = '".$previous."' ");
                    $parent = $rec->parent;
                    $rank = $rec->sortorder;
            }
            else {
                    $rank = 0;
            }

            if ($parent == 'undefined') $parent = 0;
            $recs = $DB->get_records_sql("SELECT *
                                                FROM {format_page}
                                                WHERE parent = '".$parent."'
                                                AND courseid='".$id."'
                                                ORDER BY sortorder ASC
                                                  ");
            foreach($recs as $rec) {
                    if ($rec->sortorder > $rank) {
                            $rec->sortorder = $rec->sortorder+1;
                            $DB->update_record('format_page', $rec);
                    }
            }
            $rec = $DB->get_record_sql("SELECT *
                                                FROM {format_page}
                                                WHERE id = '".$current."'
                                                  ");
            $rec->sortorder = $rank+1;
            $rec->parent = $parent;
            $DB->update_record('format_page', $rec);
	}
    /**
     * 
     * 
     * 
     */
	
	public static function showhidePage($current) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page}
                                            WHERE id = '".$current."'
                                              ");
            if ($rec->display == 0) {
                    $rec->display = 7;
            }
            else {
                    $rec->display = 0;
            }
            $DB->update_record('format_page', $rec);
	}

    /**
     * 
     * 
     * 
     */
	
	public static function isPageHidden($pageid) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page}
                                            WHERE id = '".$pageid."'
                                              ");
            if ($rec) {		
                if ($rec->display == 0) {
                    return true;
                }
            }
            return false;
	}
	
    /**
     * 
     * 
     * 
     */
	
	public static function renamePage($current, $name) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page}
                                            WHERE id = '".$current."'
                                              ");
            $rec->nameone = addslashes($name);
            $DB->update_record('format_page', $rec);
	}
	
    /**
     * Display modules and blocks for a specific page
     * 
     * @param int $current
     * @return string
     * 
     */	
	
	public static function getPageItems($current) {
            global $DB;
            $recs = $DB->get_records_sql("SELECT *
                                            FROM {format_page_items}
                                            WHERE pageid = '".$current."'
                                            ORDER BY position, sortorder ASC
                                              ");

            $result = "<tbody class='dd-list-table' id='page-content-".$current." '>";
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
                                $result .= "<img class='duplicate duplicate_".$current."' src='".DUPLICATE."' alt='dupliquer' title='".get_string('title10', 'format_page')."' />";
                                if ($rec->visible == 0) {
                                        $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$current." hideactivity' style='margin:2px;' src='".EYE_CLOSED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                                }
                                else {
                                        $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$current."' style='margin:2px;' src='".EYE_OPENED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                                }
                                $result .= "<td class='cell'><input class='defineposition' style='display:none;' type='textbox' size='1' value='".$rec->position."'/>";
                                if ($rec->position == 'l') {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='l' id='radio1_".$rec->id."' checked />";
                                }
                                else {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='l' id='radio1_".$rec->id."' />";
                                }
                                if ($rec->position == 'c') {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='c' id='radio2_".$rec->id."' checked />";
                                }
                                else {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='c' id='radio2_".$rec->id."' />";
                                }
                                if ($rec->position == 'r') {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='r' id='radio3_".$rec->id."' checked />";
                                }
                                else {
                                    $result .= "<input class='defineposition_radio defineposition_radio_".$current."' name='position_".$rec->id."' type='radio' value='r' id='radio3_".$rec->id."' />";
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
                                $result .= "<td class='cell'><img class='deleteitem deleteitem_".$current."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
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
                                $result .= "<td class='cell'><img class='deleteitem deleteitem_".$current."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
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
                                    $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$current." hideactivity' style='margin:2px;' src='".EYE_CLOSED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                            }
                            else {
                                    $result .= "<td class='cell'>"."<img class='showhideactivities showhideactivities_".$current."' style='margin:2px;' src='".EYE_OPENED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' />"."</td>";
                            }
                            $result .= "<td class='cell'><input class='defineposition' type='textbox' size='1' value='".$rec->position."' /></td>";
                            $result .= "<td class='cell'></td>";
                            $result .= "<td class='cell' style='display:none;'><input class='defineorder' type='textbox' size='1' value='".$rec->sortorder."' /></td>";
                            $result .= "<td class='cell object_name' style='display:none;' class='type'>bloc</td>";
                            $result .= "<td class='cell'>".$block->blockname."</td>";
                            $result .= "<td class='id' style='display:none;'>".$rec->id."</td>";
                            $result .= "<td class='cell'><img class='deleteitem deleteitem_".$current."' src='".DELETE."' alt='supprimer' title=\"".get_string('title14', 'format_page')."\" /></td>";
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
                            $result .= "<td class='cell'><img class='deleteitem deleteitem_".$current."' src='".DELETE."' alt='supprimer' title='".get_string('title13', 'format_page')."' /></td>";
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
            $result .= "$('.showhideactivities_".$current."').on('click',showhide_activities);
                        $('.duplicate_".$current."').on('click', duplicate_item);					
                        $('.deleteitem_".$current."').on('click', deleteitem);
                        $('.defineposition_radio_".$current."').on('click', defineposition_radio);					
                        ";				
            $result .= "</script>";



            return $result;
	}
    /** 
     * 
     * 
     * 
     */
	
	public static function showhideActivity($current) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page_items}
                                            WHERE id = '".$current."'
                                              ");
            if ($rec->visible == 0) {
                    $rec->visible = 1;
            }
            else {
                    $rec->visible = 0;
            }
            $DB->update_record('format_page_items', $rec);
		
	}
    /**
     * 
     * 
     * 
     */
	
	public static function setItemPosition($current,$position) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page_items}
                                            WHERE id = '".$current."'
                                              ");
            if ($position == "l") {
                    $rec->position = "l";
            }
            else if ($position == "r") {
                    $rec->position = "r";
            }
            else {
                    $rec->position = "c";
            }
            $DB->update_record('format_page_items', $rec);
	}
    /** 
     * 
     * 
     * 
     */
	
	public static function setItemOrder($current,$order) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page_items}
                                            WHERE id = '".$current."'
                                              ");
            if (is_numeric($order)) {
                    $rec->sortorder = $order;
            }
            else {
                    $rec->sortorder = 0;
            }
            $DB->update_record('format_page_items', $rec);
	}
    /**
     * 
     * 
     * 
     */
	
	public static function moveModule($moduleid,$displacement) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT * FROM {format_page_items} WHERE id = '".$moduleid."' ");
            $old_position = $rec->sortorder;
            if ($displacement == "up") {
                    $rec->sortorder--;
                    $recs2 = $DB->get_records_sql("SELECT * FROM {format_page_items} 
                                    WHERE sortorder <= '".$rec->sortorder."' AND 
                                    position='".$rec->position."' AND 
                                    pageid='".$rec->pageid."' 
                                    ORDER BY sortorder DESC");


            }
            if ($displacement == "down") {
                    $rec->sortorder++;
                    $recs2 = $DB->get_records_sql("SELECT * FROM {format_page_items} 
                                    WHERE sortorder >= '".$rec->sortorder."' AND 
                                    position='".$rec->position."' AND 
                                    pageid='".$rec->pageid."' 
                                    ORDER BY sortorder ASC");
            }


            if ($recs2) {
                    $recs2 = array_values($recs2);
                    $next_module_order = $recs2[0]->sortorder;
                    foreach ($recs2 as $rec2) {		
                        if ($rec2->sortorder == $next_module_order) {
                            $rec2->sortorder = $old_position;
                            $DB->update_record('format_page_items', $rec2);				
                        }
                    }
            }
            $DB->update_record('format_page_items', $rec);				
	}
    /**
     * 
     * 
     * 
     */
	
	public static function getLastIdModuleOfPage($current) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page}
                                            WHERE id = '".$current."'
                                              ");

            $module = $DB->get_record_sql("SELECT MAX(id) as maxid
                                            FROM {course_modules}
                                            WHERE course = '".$rec->courseid."'
                                              ");									  
            return $module->maxid;
	}	
        
    /**
     * Retrieve id of last orphan module
     * 
     * @param   int     $id
     * @return  int     module id
     */
    public static function getLastIdOrphanModule($pageid,$timestamp) {
        global $DB;
        $rec = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = '".$pageid."' ");
        $module = $DB->get_record_sql(" SELECT cm.id 
                                        FROM {course_modules} AS cm LEFT OUTER JOIN {format_page_items} AS fpi ON fpi.cmid=cm.id
                                        WHERE cm.course='".$rec->courseid."'
                                        AND added > '".$timestamp."'
                                        AND fpi.cmid IS NULL");
        if ($module) {
            $return = $module->id;
        }
        else {
            $return = NULL;
        }
        return $return;
    }   

    /**
     * Retrieve Max Timestamp of modules from a specific course
     * 
     * @param   int     $id
     * @return  int     maxid
     */
    public static function getLastModuleTimestamp($pageid) {
        global $DB;
        $rec = $DB->get_record_sql("SELECT * FROM {format_page} WHERE id = '".$pageid."' ");
        if ($rec->courseid == $_SESSION['courseid']) {
            $module = $DB->get_record_sql("SELECT MAX(added) as max_timestamp FROM {course_modules} WHERE course = '".$rec->courseid."' ");									  
            $return=$module->max_timestamp;            
        }
        else {
            $return=NULL;
        }
        return $return;
    }    
    
    /**
     * 
     * 
     * 
     */
	
	public static function setModuleInPage($current, $newlastmoduleid) {
            global $DB,$USER;
            $module = new stdClass;
            $module->pageid = $current;
            $module->cmid = $newlastmoduleid;
            $module->blockinstance = 0;
            $module->position = 'c';
            $entry = 'formatpage'.$USER->id;
            if (isset($_SESSION[$entry]['position'])) {
                $position  = $_SESSION[$entry]['position'];
                if ($position == "leftposition") $module->position = "l";
                if ($position == "centerposition") $module->position = "c";
                if ($position == "rightposition") $module->position = "r";
            }
            $max = $DB->get_record_sql("SELECT MAX(sortorder) as maxsortorder
                                            FROM {format_page_items}
                                            WHERE pageid = '".$current."'
                                            AND position ='".$module->position."'
                                              ");
            $module->sortorder = $max->maxsortorder + 1;
            $module->visible = 1;
            $new_module = $DB->insert_record('format_page_items', $module, true);
	}	
    /**
     * 
     * 
     * 
     */
	
	public static function insertNewItem($current, $pageid) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page_items}
                                            WHERE id = '".$current."'
                                             ");
            $max = $DB->get_record_sql("SELECT MAX(sortorder) as maxsortorder
                                            FROM {format_page_items}
                                            WHERE pageid = '".$pageid."'
                                              ");
            $rec->pageid = $pageid;
            $rec->sortorder = $max->maxsortorder + 1;
            $new_module = $DB->insert_record('format_page_items', $rec, true);
            return $new_module;
	}

    /**
     * 
     * 
     * 
     */
	
	public static function deletePage($pageid) {
            global $DB;
            $rec = $DB->get_records_sql("SELECT * FROM {format_page}  WHERE parent = '".$pageid."' ");
            if (!$rec) {
                $rec = $DB->get_records_sql("SELECT * FROM {format_page_items}  WHERE pageid = '".$pageid."' ");
                if (!$rec) {
                    $page = array();
                    $page['id'] = $pageid;
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
     * 
     * 
     * 
     */
	
	public static function deleteSections($courseid) {
            global $DB;
            $sections = array();
            $sections['course'] = $courseid;
            $DB->delete_records('course_sections', $sections);	
            $new_section = new stdClass;
            $new_section->course = $courseid;
            $new_section->section = 0;
            $new_section->visible = 0;
            $new_section->name = "Parcours";
            $insert_section = $DB->insert_record('course_sections', $new_section, true);
	}

    /**
     * 
     * 
     * 
     */
	
	public static function getPageFromSection($section,$courseid) {
            global $DB;
            $sec = $DB->get_record_sql("SELECT (count(*)) as rank FROM {course_sections} WHERE section < $section AND course=$courseid");
            if ($sec) {
                $linear_pages = array();$pages=array();
                $pages = SimplePage::getChainedPages($courseid);
                reset($pages);current($pages);$i = key($pages);
                SimplePage::linearizePages($i,$pages,$linear_pages);
                if (isset($linear_pages[$sec->rank])) return $linear_pages[$sec->rank]->id;
            }
            return 0;		
	}


    /**
     * 
     * 
     * 
     */
	
	public static function associateSections($courseid) {
            global $DB;
            $sections = array();
            $sections['course'] = $courseid;
            $DB->delete_records('course_sections', $sections);	

            $new_section = new stdClass;$update_module = new stdClass;
            $linear_pages = array();$pages=array();
            $pages = SimplePage::getChainedPages($courseid);
            reset($pages);current($pages);$i = key($pages);
            SimplePage::linearizePages($i,$pages,$linear_pages);
            $i = 0;
            foreach($linear_pages as $linear_page) {
                $new_section->course = $linear_page->courseid;
                $new_section->section = $i;
                $new_section->visible = 1;
                $new_section->name = $linear_page->nameone;
                $id_section = $DB->insert_record('course_sections', $new_section, true);

                $items = $DB->get_records_sql("SELECT * 
                                                FROM {format_page_items}  
                                                WHERE pageid = '".$linear_page->id."' 
                                                AND blockinstance='0' ");
                $section_module = null;
                foreach($items as $item) {
                    $update_module->id = $item->cmid;
                    $update_module->section = $id_section;
                    $DB->update_record('course_modules', $update_module);
                    if($section_module) {
                            $section_module .=",";
                    }
                    $section_module .= $item->cmid;
                }
                $new_section->id = $id_section;
                $new_section->sequence = $section_module;
                $DB->update_record('course_sections', $new_section);
                $i++;
            }
	}

    /**
     * 
     * 
     * 
     */
	public static function linearizePages($i,$tab,&$linear_tab) {
            $end = false;
            while (isset($tab[$i]->id) && !$end) {
                array_push($linear_tab, $tab[$i]);
                if (isset($tab[$i]->child)) {
                        SimplePage::linearizePages($tab[$i]->child, $tab,$linear_tab);
                }
                if (!isset($tab[$i]->next)) {
                        $end = true;
                }
                else {
                        $i = $tab[$i]->next;
                }
            }
	}

    /**
     * 
     * 
     * 
     */	
	public static function modifyLinkState($current,$link) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT *
                                            FROM {format_page}
                                            WHERE id = '".$current."' ");
            if ($link=="previous") {
                $rec->showbuttons = $rec->showbuttons ^ PREVIOUS_LINK;
            }
            else {
                $rec->showbuttons = $rec->showbuttons ^ NEXT_LINK;	
            }
            $DB->update_record('format_page', $rec);
	}
	
    /**
     * 
     * 
     * 
     */	
	
	public static function getActiveFilters() {
            global $DB;

            $recs = $DB->get_records_sql("SELECT  *
                                                FROM {filter_active}
                                                WHERE active = '1'
                                                ORDER BY sortorder ASC ");
            foreach ($recs as $rec) {
                $rec->filter = substr($rec->filter,7);
            }
            return $recs;
        }

    /**
     * look for modules not yet included in pages 
     *		
     * @param int courseid
     * @return string
     * 
     */

	public static function lookforOrphans($courseid) {
            global $DB, $USER,$MAIN_DIR;

//            $recs = $DB->get_records_sql("SELECT cm.*
//                                            FROM {course_modules} as cm 
//                                            WHERE cm.id NOT IN (SELECT cmid FROM {format_page_items} WHERE blockinstance = '0')
//                                            AND cm.course = '$courseid'
//
//                                                              ");
            
            $recs = $DB->get_records_sql("SELECT cm.*
                                            FROM {course_modules} as cm LEFT OUTER JOIN {format_page_items} AS fpi ON fpi.cmid=cm.id 
                                            WHERE fpi.cmid IS NULL
                                            AND cm.course = '$courseid' ");
            $result = "<table class='training_table'>";
            $result .= "<tr>";
            $result .= "<th></th>";
            $result .= "<th>Nom</th>";
            $result .= "<th>Type</th>";
            $result .= "<th></th>";
            $result .= "</tr>";

            foreach ($recs as $rec) {
                $module = $DB->get_record_sql("SELECT * FROM {modules} WHERE id = '".$rec->module."'");
                $object = $DB->get_record_sql("SELECT * FROM {".$module->name."} WHERE id = '".$rec->instance."'");
                $result .= "<tr class='dd-item' id='item_".$rec->id."' >";
                $result .= "<td class='dd-handle2 displaynone'><img src='".MOVE."' alt='".get_string('movePageAlternate','format_page')."' title='".get_string('movePageAlternate','format_page')."' /></td>";
                $result .= "<td class='cell'><img class='duplicate moveorphan' style='width:20px;' src='".DUPLICATE."' alt='dupliquer' title='".get_string('title15', 'format_page')."' /></td>";
                $result .= "<td class='cell displaynone'><img class='showhideactivities' style='margin:2px;' src='".EYE_OPENED."' alt='".get_string('title12', 'format_page')."' title='".get_string('title12', 'format_page')."' /></td>";
                $result .= "<td class='cell displaynone'>";
                $result .= "<input class='defineposition_radio defineposition_radio_orphans' name='position_".$rec->id."' type='radio' value='l' id='radio1_".$rec->id."' />";
                $result .= "<input class='defineposition_radio defineposition_radio_orphans' name='position_".$rec->id."' type='radio' value='c' id='radio2_".$rec->id."' checked />";
                $result .= "<input class='defineposition_radio defineposition_radio_orphans' name='position_".$rec->id."' type='radio' value='r' id='radio3_".$rec->id."' />";
                $result .= "</td>";
                $result .= "<td class='cell object_name'>".$object->name."</td>";
                $result .= "<td>".$module->name."</td>";
                $result .= "<td><img class='deletemodule' src='".DELETE."' alt='supprimer' title='".get_string('title1', 'format_page')."' /></td>";
                $result .= "<td style='display:none;' class='id'>".$rec->id."</td>";
                $result .= "</tr>";
            }
            $result .= "</table>";

            $result .= "<script>";
            $result .=     "$('.moveorphan').on('click',move_orphan);					
                            $('.deletemodule').on('click',delete_module);";				
            $result .= "</script>";
            return $result;								  
	}
    /**
     * called by ajaxdeleteitem - delete an item from a specific page
     * 
     * @param int $current  item id
     * @return string       state
     * 
     */

	public static function deleteItem($current) {
            global $DB;
            $item = array();
            $state = 'do_nothing';
            $item['id'] = $current;
            $rec = $DB->get_record_sql("SELECT * FROM {format_page_items}  WHERE id = '".$current."' ");
            $cmid = $rec->cmid;
            $pageid = $rec->pageid;
            $get_page = $DB->get_record_sql("SELECT courseid FROM {format_page}  WHERE id = '".$pageid."' ");
            $blockinstance = $rec->blockinstance;
            $DB->delete_records('format_page_items', $item);

            if ($cmid != 0) $rec2 = $DB->get_record_sql("SELECT fpi.* FROM {format_page_items} as fpi,{format_page} as fp
                                                            WHERE fp.id = fpi.pageid
                                                            AND fp.courseid = '".$get_page->courseid."'
                                                            AND fpi.cmid = '".$cmid."' 
                                                            ");
            if ($blockinstance != 0) $rec2 = $DB->get_record_sql("SELECT fpi.* FROM {format_page_items} as fpi,{format_page} as fp
                                                            WHERE fp.id = fpi.pageid
                                                            AND fp.courseid = '".$get_page->courseid."'
                                                            AND fpi.blockinstance = '".$blockinstance."' ");
            if(!$rec2) {
                
                $state = $cmid;
                /**
                 * disable module deletion if there is no more items
                 * if item is deleted, corresponding module becomes an orphan module
                 */
                /*if ($cmid != 0) {
                    $item['id'] = $cmid;
                    $DB->delete_records('course_modules', $item);
                    $rec3 = $DB->get_record_sql("SELECT * FROM {format_page}  WHERE id = '".$pageid."' ");
                    if ($rec3) {
                            SimplePage::clearModinfo($rec3->courseid);	
                    }
                }*/
            }
            return $state;
	}

    /**
     * 
     * 
     * 
     */
	
    public static function deleteModule($current) {
        global $DB;
        $item = array();
        $item['id'] = $current;
        $DB->delete_records('course_modules', $item);		
    }
    /**
     * 
     * 
     * 
     */
	
    public static function clearModinfo($courseid) {
            global $DB;
            $rec = $DB->get_record_sql("SELECT  *
                                            FROM {course}
                                            WHERE id = '".$courseid."'									
                                      ");
            $rec->modinfo = '';
            $DB->update_record('course', $rec);		
    }
    /**
     * Reorder a module in a page or move a module to a new page
     * 
     * @param int $moduleid     id of the selected module (activity or block)
     * @param int $pageid       id of the targeted page
     * @param int $previousid     id of the previous module in the DOM
     * 
     */
        public static function moduleDisplacement($moduleid, $pageid,$previousid) {
            global $DB;
            
            $currentmodule = $DB->get_record_sql("SELECT * FROM {format_page_items} WHERE id = '".$moduleid."' ");
            if ($currentmodule) {
                $currentmodule->pageid = $pageid;
                if ($previousid != "undefined") {
                    $previousmodule = $DB->get_record_sql("SELECT * FROM {format_page_items} WHERE id = '".$previousid."'
                        AND pageid = '".$pageid."' ");
                    if ($previousmodule) {
                        $uppermodules = $DB->get_records_sql("SELECT * FROM {format_page_items} WHERE pageid = '".$pageid."' 
                            AND sortorder > '".$previousmodule->sortorder."' ");
                        foreach ($uppermodules as $uppermodule) {
                            $uppermodule->sortorder++;
                            $DB->update_record('format_page_items', $uppermodule);
                        }
                        $currentmodule->sortorder = $previousmodule->sortorder + 1;
                    }
                    else {
                        $currentmodule->sortorder = 0;
                    }
                }
                else {
                    $allmodules = $DB->get_records_sql("SELECT * FROM {format_page_items} WHERE pageid = '".$pageid."' ");
                    foreach ($allmodules as $allmodule) {
                        $allmodule->sortorder++;
                        $DB->update_record('format_page_items', $allmodule);
                    }
                    $currentmodule->sortorder = 0;
                }
                
                $DB->update_record('format_page_items', $currentmodule);                    
            }
            else {
                echo "module does not exist";
            }
        }
    /**
     * Called when a user manually change the assignment state of a module
     * 
     * @param int   $module_id
     * 
     */	
	public static function toggleAssignment($module_id) {
            global $DB,$COURSE,$USER;
            $completion = new completion_info($COURSE);
            $course_module = $DB->get_record_sql("SELECT * FROM {course_modules} WHERE id = '".$module_id."'");
            $current = $completion->get_data($course_module,null,$USER->id);
            if ($current->completionstate == COMPLETION_COMPLETE) {
                $completion->update_state($course_module,COMPLETION_INCOMPLETE,$USER->id);
            }
            else {
                $completion->update_state($course_module,COMPLETION_COMPLETE,$USER->id);
            }            
	}        
    /**
     * 
     * 
     * 
     */
	
	public static function moveModuleToPage($moduleid, $pageid) {
            global $DB;
            $max = $DB->get_record_sql("SELECT MAX(sortorder) as maxsortorder
                                                                    FROM {format_page_items}
                                                                    WHERE pageid = '".$pageid."'
                                                                      ");
            $module = new StdClass;
            $module->pageid = $pageid;
            $module->cmid = $moduleid;
            $module->blockinstance = 0;
            $module->position = 'c';
            $module->sortorder = $max->maxsortorder + 1;
            $module->visible = 1;
            $new_module = $DB->insert_record('format_page_items', $module, true);
            return $new_module;
	}
    /**
     * Avoid javascript errors
     * 			
     * 
     */      
    public static function stringFilter($string) 
    {    
        $result = $string;
        $lut = array("\r\n" => " ", "\n" =>" ", "\r" =>" ", '"' =>"'", "\t" => " ");
        foreach ($lut as $key => $value) {
            $result = str_replace($key, $value, $result);
        }
        return $result;
    }
}
 ?>
