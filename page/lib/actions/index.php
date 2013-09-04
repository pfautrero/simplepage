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


 
global $LOCAL_PATH,$MAIN_DIR;
include($LOCAL_PATH."/lib/actions/action.class.php");
include($LOCAL_PATH."/lib/model/lib.php");

/**
 * prepare a page display
 *
 * @package    simplepage
 * @subpackage actions
 * @copyright  2013 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class indexAction extends Action {

    public function launch(Request $request, Response $response)
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH, $USER, $COURSE, $MAIN_DIR;

        $_SESSION['courseid'] = $COURSE->id;
        // ===========	Retrieve GET variables
        $page=$request->getParam('page');
        $id=$request->getParam('id');
        $topic=$request->getParam('topic');
        $section=$request->getParam('section');
        $moduleid=$request->getParam('module');
        $displacement=$request->getParam('displacement');	

        $entry = 'formatpage'.$USER->id;

        // ============ retrieve created module and set the correct page owner.		
        if (isset($_SESSION[$entry]['idpage'])) {
            $current = $_SESSION[$entry]['idpage'];
            $last_added = SimplePage::getLastModuleTimestamp($current);
            if ($last_added && ($_SESSION[$entry]['timestamp'] < $last_added)) {
                $new_module_id = SimplePage::getLastIdOrphanModule($current,$_SESSION[$entry]['timestamp']);
                if ($new_module_id) {
                    unset($_SESSION[$entry]['idpage']);
                    unset($_SESSION[$entry]['lastmoduleid']);
                    SimplePage::setModuleInPage($current, $new_module_id);
                    $page = $current;                    
                }
                else {
                    $_SESSION[$entry]['timestamp'] = time();
                }
            }
        }

        // =============== 	Clic sur une section du bloc de navigation
        // 						Il faut récupérer la page correspondante.
        if (isset($_GET['topic'])) {
                $page = SimplePage::getPageFromSection($topic,$COURSE->id);
        }
        else if (isset($_GET['section'])) {
                $page = SimplePage::getPageFromSection($section,$COURSE->id);
        }
        else if ($page === NULL) {
                if (isset($_SESSION[$entry][$id]['lastpagevisited'])) $page = $_SESSION[$entry][$id]['lastpagevisited'];
        }


        // =============== add a page
        if($request->getParam('addpage')!==null) {
                $params = new stdClass;
                if($request->getParam('page_name')) {
                        if(($request->getParam('pageparente')!==null) && (is_numeric($request->getParam('pageparente')))) {
                                $pageparente = $request->getParam('pageparente');
                        }
                        else {
                                $pageparente = 0;
                        }
                        $params->parent = $pageparente;
                        $max = $DB->get_records_sql("SELECT MAX(sortorder) as max
                                                     FROM {format_page}
                                                     WHERE courseid = $id
                                                     AND parent='".$pageparente."'
                                                     ");
                        $max = array_values($max);

                        $params->sortorder = $max[0]->max + 1;
                        $params->nameone = addslashes($request->getParam('page_name'));
                        $params->nametwo = addslashes($request->getParam('page_name'));
                        $params->display = 7;
                        $params->courseid = $id;
                        $params->prefcenterwidth = 600;
                        $params->showbuttons = 3;

                        $new_id = $DB->insert_record('format_page', $params, true);
                        $message = get_string('successAddPage', 'format_page');
                        $page = $new_id;
                }
                else {
                        $message = get_string('voidNamePage', 'format_page');
                }
        }

        $pages = SimplePage::DisplayTabs($id, $page, $tabs);

        if (!$page) {	
            if ($pages) {
                reset($pages);
                $page = current($pages)->id;
            }
        }	

        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);	
        if (SimplePage::isPageHidden($page)) {
            if (has_capability('moodle/course:manageactivities', $coursecontext)) {
                echo "<div class='alert'>Page cachée.</div>";	
            } else {
                echo "<div class='alert'>".get_string('pageForAdministratorsOnly', 'format_page')."</div>";	
                $adminBlock = SimplePage::getAdminBlock($id);
                $response->addVar('editing', $PAGE->user_is_editing());
                $response->addVar('adminBlock', $adminBlock);
                $this->render($LOCAL_PATH."/lib/template/voidSuccess.php");
                $this->printOut();				
                return;
            }
        }	
        if ($page) {
                $_SESSION[$entry][$id]['lastpagevisited'] = $page;
                $tab = SimplePage::getTab($page);
                $Column = array();
                $Column['l'] = null;
                $Column['r'] = null;
                $Column['c'] = null;
                $Column2 = array();
                $Column2['l'] = null;
                $Column2['r'] = null;
                $Column2['c'] = null;
                $TempColumn = array();

                // ============ Manage modules displacements			
                if ($displacement == "up" || $displacement == "down") {
                    if ($PAGE->user_is_editing() && has_capability('moodle/course:manageactivities', $coursecontext)) {
                        if ($moduleid) {
                                SimplePage::moveModule($moduleid,$displacement);
                        }
                    }
                }

                // ======================================================
                //
                // Retrieve modules
                //
                // ======================================================

                $course_modules = SimplePage::getCourseModules($page, $USER->id);
                $i = 0;
                foreach($course_modules as $course_module) {

                        if ($PAGE->user_is_editing() && has_capability('moodle/course:manageactivities', $coursecontext)) {
                                $Column2[$course_module['position']][$course_module['sortorder']]['header'] = "
                                        <a href='modedit.php?update=".$course_module['cmid']."&return=0'>
                                        <img style='margin:2px;'  src='".EDIT."' alt='editer' title='editer' />
                                        </a>";

                                if ($course_module['visible'] == 0) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['header'] .= "
                                                <img    class='showhideactivities hideactivity' 
                                                        style='margin:2px;' src='".EYE_CLOSED."' alt='montrer-cacher' 
                                                        title='montrer-cacher' />";
                                }
                                else {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['header'] .= "
                                                <img    class='showhideactivities' 
                                                        style='margin:2px;' src='".EYE_OPENED."' 
                                                        alt='montrer-cacher' 
                                                        title='montrer-cacher' />";
                                }
                                $Column2[$course_module['position']][$course_module['sortorder']]['header'] .="
                                        <img    class='deleteitem' 
                                                style='margin:2px;' 
                                                src='".DELETE."' 
                                                alt='supprimer' 
                                                title='suppression de cet item' />";

                                $Column2[$course_module['position']][$course_module['sortorder']]['header'] .="
                                        <img    class='duplicate' 
                                                style='margin:2px;' 
                                                src='".DUPLICATE."' 
                                                alt='dupliquer' 
                                                title='Afficher également sur une autre page' />";
                        }
                        elseif ($course_module['completion']) {
                            if ($course_module['completion'] == 1) {
                                $Column2[$course_module['position']][$course_module['sortorder']]['header'] .="
                                    <div class='squaredOne'>
                                            <input type='checkbox' id='checkbox_".$course_module['cmid']."' checked />
                                            <label for='checkbox_".$course_module['cmid']."'></label>
                                    </div>

                                        ";
                            }
                            if ($course_module['completion'] == 2) {
                                $Column2[$course_module['position']][$course_module['sortorder']]['header'] .="
                                    <div class='squaredOne'>
                                            <input type='checkbox' id='checkbox_".$course_module['cmid']."' />
                                            <label for='checkbox_".$course_module['cmid']."'></label>
                                    </div>                                        
                                        ";
                            }                            
                            
                        }
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] = "<input class='input_id' type='hidden' name='".$course_module['id']."' value='' />";
                        // ==================== Module Page
                        if ($course_module['type'] == "page") {
                                if ($course_module['object']->display != DISPLAY_POPUP) {
                                        $filtered_content = file_rewrite_pluginfile_urls($course_module['object']->content, 'pluginfile.php',$course_module['context'],'mod_page', 'content',0);
                                        $page_options = unserialize($course_module['object']->displayoptions);
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div class='page'>";						
                                        if ($page_options['printheading']) $Column2[$course_module['position']][$course_module['sortorder']]['content'] .="<h1>".$course_module['object']->name."</h1>";
                                        if ($page_options['printintro']) $Column2[$course_module['position']][$course_module['sortorder']]['content'] .="<p style='font-style:italic;'>".$course_module['object']->intro."</p>";  
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= $filtered_content."</div>";
                                }
                                else {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='page_popup'>
                                                <a href='".$CFG->wwwroot."/mod/resource/view.php?id=".$course_module['cmid']."' target='_blank'>".$course_module['object']->name."
                                                </a>
                                                </div>";
                                }
                        }
                        // ==================== Module PageMenu
                        else if ($course_module['type'] == "pagemenu") {
                                if ($course_module['object']->displayname) $Column[$course_module['position']] .= $course_module['object']->name;
                                $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= SimplePage::getPagemenuLinks($course_module['object']->id, $id);
                        }
                        // ==================== Module Label
                        else if ($course_module['type'] == "label") {
                                $filtered_content = file_rewrite_pluginfile_urls($course_module['object']->intro, 'pluginfile.php',$course_module['context'],'mod_label', 'intro');
                                $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div style='padding-bottom:20px;'>".$filtered_content."</div>";
                        }
                        // ==================== Module url
                        else if ($course_module['type'] == "url") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='module'><a href='".$course_module['object']->externalurl."'>".$course_module['object']->name."</a></div>";
                                }
                                else {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div><a href='".$course_module['object']->externalurl."'>".$course_module['object']->externalurl."</a></div>";
                                }				
                        }
                        // ==================== Module choice
                        else if ($course_module['type'] == "choice") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-sondage'>
                                                <span>Sondage</span>
                                                <p><a href='".$CFG->wwwroot."/mod/choice/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a></p>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module forum
                        else if ($course_module['type'] == "forum") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-forum'>
                                                <span>Forum</span>
                                                <br><a href='".$CFG->wwwroot."/mod/forum/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module quiz
                        else if ($course_module['type'] == "quiz") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-test'>
                                                <span>Test</span>
                                                <br><a href='".$CFG->wwwroot."/mod/quiz/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module wiki
                        else if ($course_module['type'] == "wiki") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-wiki'>
                                                <span>Wiki</span>
                                                <br><a href='".$CFG->wwwroot."/mod/wiki/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module data
                        else if ($course_module['type'] == "data") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-bdd'>
                                                <span>Base de données</span>
                                                <br><a href='".$CFG->wwwroot."/mod/data/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module feedback
                        else if ($course_module['type'] == "feedback") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-feedback'>
                                                <span>Feedback</span>
                                                <p><a href='".$CFG->wwwroot."/mod/feedback/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </p>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module chat
                        else if ($course_module['type'] == "chat") {
                                if ($course_module['object']->name) {
                                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                                <div class='boite-dial' style='margin:5px auto;'>
                                                <div class='boite-head-chat'>
                                                <span>Chat</span>
                                                <p><a href='".$CFG->wwwroot."/mod/chat/view.php?id=".$course_module['cmid']."'>".
                                                $course_module['object']->name."</a>
                                                </p>
                                                </div>
                                                </div>";
                                }
                        }
                        // ==================== Module non reconnu
                        else {
                                $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div class='module'>";
                                $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
                                        <a href='".$CFG->wwwroot."/mod/".$course_module['type']."/view.php?id=".$course_module['cmid']."'>".$course_module['object']->name."</a></div>";
                        }
                        $Column2[$course_module['position']][$course_module['sortorder']]['moduleid']  = $course_module['id'];
                        $Column2[$course_module['position']][$course_module['sortorder']]['display_mode']  = $course_module['display_mode'];
                        $Column2[$course_module['position']][$course_module['sortorder']]['completion']  = $course_module['completion'];
                        $i++;		
                }



                // ======================================================
                //
                // Retrieve blocks
                //
                // ======================================================

                $blocks = SimplePage::getBlocks($page);
                include_once('../blocks/moodleblock.class.php');
                foreach ($blocks as $block) {
                        include_once('../blocks/'.$block->blockname.'/block_'.$block->blockname.'.php');	
                        $classname = 'block_'.$block->blockname;
                        $block_instance = new $classname;
                        $block_instance->config = new StdClass;
                        $block_instance->context = new StdClass;
                        $block_instance->instance = new StdClass;
                        $block_instance->page = $PAGE;
                        $block_instance->instance->parentcontextid = $block->blockinstance;
                        $block_instance->config = unserialize(base64_decode($block->configdata));
                        $block_instance->context->id = $PAGE->context->id;//context::instance_by_id($PAGE->context->id);
                        $bc = new block_contents();
                        if ($PAGE->user_is_editing()) {
                                /*$bc->controls[0]['icon'] = "s/biggrin";
                                $bc->controls[0]['caption'] = "test";
                                $bc->controls[0]['url'] = "/";	//$actionurl . '&bui_moveid=' . $block->blockinstance;
                                $bc->controls[1]['icon'] = "s/approve";
                                $bc->controls[1]['caption'] = "test";
                                $bc->controls[1]['url'] = "/";*/
                        }
                        $block_instance->parentcontextid = $PAGE->context->id;
                        $block_instance->content = null;
                        $bc->content = $block_instance->get_content()->text;
                        //$bc->footer = "test de pied";
                        $bc->title = $block_instance->title;
                        $bc->collapsible = block_contents::VISIBLE;
                        $bc->blockinstanceid = $block->id; // indispensable pour que collapsible fonctionne
                        $bc->id = $block->id;
                        //$bc->attributes = array("id"=>"inst".$bc->id, "class"=>"block");
                        $Column2[$block->position][$block->sortorder]['header'] = ""; 
                        $Column2[$block->position][$block->sortorder]['content'] = $OUTPUT->block($bc, null);
                        $Column2[$block->position][$block->sortorder]['moduleid']  = $block->item_id;			
                        $Column2[$block->position][$block->sortorder]['display_mode']  = 1;
                }


                // ======================================================
                //
                //  Build output
                //
                // ======================================================

                ksort($Column2['l']);ksort($Column2['c']);ksort($Column2['r']);
                foreach($Column2 as $key => $selected_column) {
                    if ($selected_column) $selected_column = array_values($selected_column);
                    for($i=0;$i < count($selected_column);$i++) {
                        if ($PAGE->user_is_editing() && has_capability('moodle/course:manageactivities', $coursecontext)) {
                            if ($i != 0) {
                                $selected_column[$i]['header'] .= "
                                            <a href='/course/view.php?id=".$COURSE->id."&module=".$selected_column[$i]['moduleid']."&displacement=up'>
                                            <img class='moveup' style='width:20px;margin:2px;' src='".ARROW_UP."' alt='monter' title='Monter le module' />
                                            </a>	";
                            }
                            if ($i < count($selected_column) -1) {
                            $selected_column[$i]['header'] .= "
                                            <a href='/course/view.php?id=".$COURSE->id."&module=".$selected_column[$i]['moduleid']."&displacement=down'>
                                            <img class='movedown' style='width:20px;margin:2px;' src='".ARROW_DOWN."' alt='descendre' title='Descendre le module' />
                                            </a>	";				
                            }
                            if ($selected_column[$i]['display_mode'] == 2) {
                                    $Column[$key] .= "  <div class='module_mode_unavailable'>
                                                        <div class='header_module'>".
                                                        $selected_column[$i]['header'].
                                                        "</div>".
                                                        $selected_column[$i]['content'].
                                                        "</div>";							
                            }
                            else {
                                    $Column[$key] .= "  <div class='module_mode_edit'>
                                                        <div class='header_module'>".
                                                        $selected_column[$i]['header'].
                                                        "</div>".
                                                        $selected_column[$i]['content'].
                                                        "</div>";							
                            }						
                        }
                        else {
                            if ($selected_column[$i]['display_mode'] == 2) {
                                    $Column[$key] .= "  <div class='module_mode_unavailable'>
                                                        <div class='header_module'>".
                                                        $selected_column[$i]['header'].
                                                        "</div>".
                                                        $selected_column[$i]['content'].
                                                        "</div>";
                            }
                            elseif (isset($selected_column[$i]['completion']) && $selected_column[$i]['completion'] != 0) {
                                    $Column[$key] .= "  <div class='module_mode_edit'>
                                                        <div class='header_module'>".
                                                        $selected_column[$i]['header'].
                                                        "</div>".
                                                        $selected_column[$i]['content'].
                                                        "</div>";							
                            }                            
                            else {
                                    $Column[$key] .= "  <div>
                                                        <div class='header_module'>".
                                                        $selected_column[$i]['header'].
                                                        "</div>".
                                                        $selected_column[$i]['content'].
                                                        "</div>";	
                            }
                        }
                    }				
                }

                $adminBlock = null;
                if ($PAGE->user_is_editing()) {
                        $adminBlock = SimplePage::getAdminBlockIndex($id);
                }



                // ==================================================
                //
                // Apply filters if necessary
                //
                // ==================================================		

                $active_filters = SimplePage::getActiveFilters();
                $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
                foreach ($active_filters as $currentfilter) {
                    if (file_exists($CFG->dirroot . '/filter/'.$currentfilter->filter.'/filter.php')) {
                        require_once($CFG->dirroot . '/filter/'.$currentfilter->filter.'/filter.php');
                        $class_filter = "filter_".$currentfilter->filter;
                        $filterplugin = new $class_filter($coursecontext,array());
                        $content_center = $Column['c'];
                        $content_left = $Column['l'];
                        $content_right = $Column['r'];
                        $Column['c'] = $filterplugin->filter($content_center);
                        $Column['r'] = $filterplugin->filter($content_right);
                        $Column['l'] = $filterplugin->filter($content_left);
                    }
                }

                // ======= éviter les échappements js
                $Column['l'] = SimplePage::stringFilter($Column['l']);	
                $Column['r'] = SimplePage::stringFilter($Column['r']);

                //var_dump($Column['l']);
                // ==================================================
                //
                // Display columns
                // Colonne de gauche : injection des blocks dans la colonne existante
                // Colonnes centrale et de droite : Ajout de div
                // ==================================================		
                $response->addVar('tabs', $tabs);	
                $response->addVar('leftColumn', $Column['l']);
                $response->addVar('leftColumnWidth', $tab->prefleftwidth);
                $response->addVar('centerColumn', $Column['c']);
                $response->addVar('centerColumnWidth', $tab->prefcenterwidth);
                $response->addVar('rightColumn', $Column['r']);
                $response->addVar('rightColumnWidth', $tab->prefrightwidth);
                $response->addVar('adminBlock', $adminBlock);
                $response->addVar('editing', $PAGE->user_is_editing());


                // ======================================================
                //
                // display links at the bottom of the page 
                // ======================================================

                $array_pages = array_values($pages);
                $prev_page = $next_page = null;
                for($i = 0;$i < count($array_pages);$i++) {
                    if ($array_pages[$i]->id == $page) {
                        if ((isset($array_pages[$i-1])) && ($array_pages[$i]->showbuttons & PREVIOUS_LINK)) 
                            $prev_page = $array_pages[$i-1]->id;
                        if (isset($array_pages[$i+1]) && ($array_pages[$i]->showbuttons & NEXT_LINK)) {
                            if ($array_pages[$i+1]->display & PUBLISH) {
                                    $next_page = $array_pages[$i+1]->id;
                            }
                        }
                    }
                }

                $tab = SimplePage::getChainedPages($id);
                reset($tab);current($tab);$i = key($tab);
                ob_start();
                SimplePage::generatePagesTree($i,$tab, 0);
                $pagestree = ob_get_contents();
                ob_end_clean();	

                $course = $DB->get_record_sql("SELECT * FROM {course} WHERE id='$id'");
                $section = 0;
                $vertical = true;
                $return = true;
                get_all_mods($course->id, $mods, $modnames, $modnamesplural, $modnamesused);
                foreach($modnames as $key=>$value) {
                    $modnames[$key] = get_string($key, 'format_page');
                }
                $response->addVar('pagestree', $pagestree);			
                if ($PAGE->user_is_editing()) {
                    $addmodule = $OUTPUT->box_start('generalbox sitetopic');
                    $addmodule.= print_section_add_menus($course, $section, $modnames, $vertical, $return);
                    $addmodule.= $OUTPUT->box_end();
                    $response->addVar('addmodule', $addmodule);			
                }
                else {
                    $response->addVar('addmodule','');
                }
                $response->addVar('prev_page', $prev_page);
                $response->addVar('next_page', $next_page);
                $response->addVar('id', $id);
                $response->addVar('pageid', $page);
                $response->addVar('sesskey', $USER->sesskey);
                $response->addVar('maindir', $MAIN_DIR);
                $this->render($LOCAL_PATH."/lib/template/indexSuccess.php");
                $this->printOut();
        }
        else {
                $adminBlock = null;
                if ($PAGE->user_is_editing()) {
                        echo "<div class='alert'>".get_string('courseIsEmpty', 'format_page')."</div>";	
                        $adminBlock = SimplePage::getAdminBlock($id);
                }
                else {
                        echo "<div class='alert'>".get_string('courseUnderConstruction', 'format_page')."</div>";	
                }
                $response->addVar('editing', $PAGE->user_is_editing());
                $response->addVar('adminBlock', $adminBlock);
                $this->render($LOCAL_PATH."/lib/template/voidSuccess.php");
                $this->printOut();
        }

    }
}

?>
