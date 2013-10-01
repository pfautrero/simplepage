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
 * @subpackage actions
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
include_once($CFG->dirroot . "/course/format/page/lib/actions/action.class.php");
include_once($CFG->dirroot . "/course/format/page/lib/model/lib.php");

class pdfAction extends Action 
{

    public function launch(Request $request, Response $response) 
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $LOCAL_PATH, $USER;
        header_remove();

        // ===========	Retrieve GET variables
        $page = $request->getParam('page');
        $id = $request->getParam('id');

        // ======================================================
        //
		//					 Affichage des onglets
        //
		// ======================================================
        //$pages = SimplePage::DisplayTabs($id, $page, $tabs);
        // ======================================================
        //
		//					Affichage des modules 
        //
		// ======================================================
//		if (!$page) {	
//			if ($pages) {
//				reset($pages);
//				$page = current($pages)->id;
//			}
//		}	


        $course = $DB->get_record('course', array('id' => $id));
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        if (SimplePageLib::isPageHidden($page)) {
            if (!has_capability('moodle/course:manageactivities', $coursecontext)) {
                $page = null;
            }
        }
        if ($page) {

            $tab = SimplePageLib::getTab($page);

            $Column = array();
            $Column['l'] = null;
            $Column['r'] = null;
            $Column['c'] = null;

            $Column2 = array();
            $Column2['l'] = null;
            $Column2['r'] = null;
            $Column2['c'] = null;
            $TempColumn = array();


            // ======================================================
            //
			//					Generate modules
            //
			// ======================================================

            $course_modules = SimplePageLib::getCourseModules($page, $USER->id);
            $i = 0;
            $doc = new DOMDocument();
            foreach ($course_modules as $course_module) {

                $Column2[$course_module['position']][$course_module['sortorder']]['content'] = "<input class='input_id' type='hidden' name='" . $course_module['id'] . "' value='' />";
                // ==================== Module Page
                if ($course_module['type'] == "page") {
                    if ($course_module['object']->display != DISPLAY_POPUP) {
                        $filtered_content = file_rewrite_pluginfile_urls($course_module['object']->content, 'pluginfile.php', $course_module['context'], 'mod_page', 'content', 0);
                        $page_options = unserialize($course_module['object']->displayoptions);
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div class='page'>";
                        if ($page_options['printheading'])
                            $Column2[$course_module['position']][$course_module['sortorder']]['content'] .="<h1>" . $course_module['object']->name . "</h1>";
                        if ($page_options['printintro'])
                            $Column2[$course_module['position']][$course_module['sortorder']]['content'] .="<p style='font-style:italic;'>" . $course_module['object']->intro . "</p>";
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= $filtered_content . "</div>";
                    }
                    else {
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
							<div class='page_popup'>
							<a href='" . $CFG->wwwroot . "/mod/resource/view.php?id=" . $course_module['cmid'] . "' target='_blank'>" . $course_module['object']->name . "
							</a>
							</div>";
                    }
                }
                // ==================== Module PageMenu
                else if ($course_module['type'] == "pagemenu") {
                    if ($course_module['object']->displayname)
                        $Column[$course_module['position']] .= $course_module['object']->name;
                    $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= SimplePageLib::getPagemenuLinks($course_module['object']->id, $id);
                }
                // ==================== Module Label
                else if ($course_module['type'] == "label") {
                    $filtered_content = file_rewrite_pluginfile_urls($course_module['object']->intro, 'pluginfile.php', $course_module['context'], 'mod_label', 'intro');
                    @$doc->loadHTML($filtered_content);
                    $tags = $doc->getElementsByTagName('img');
                    foreach ($tags as $tag) {
                        $url = $tag->getAttribute('src');
                        $extension = end(explode('.', $url));
                        $b64image = base64_encode(file_get_contents($url));
                        $new_img_tag = "data:image/" . $extension . ";base64," . $b64image;
                        $filtered_content = str_replace($url, $new_img_tag, $filtered_content);
                    }
                    $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div style='padding-bottom:20px;'>" . $filtered_content . "</div>";
                }
                // ==================== Module url
                else if ($course_module['type'] == "url") {
                    if ($course_module['object']->name) {
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
							<div class='module'><a href='" . $course_module['object']->externalurl . "'>" . $course_module['object']->name . "</a></div>";
                    } else {
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div><a href='" . $course_module['object']->externalurl . "'>" . $course_module['object']->externalurl . "</a></div>";
                    }
                }
                // ==================== Module choice
                else if ($course_module['type'] == "choice") {
                    if ($course_module['object']->name) {
                        $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
							<div class='module'><a href='" . $CFG->wwwroot . "/mod/choice/view.php?id=" . $course_module['cmid'] . "'>" . $course_module['object']->name . "</a></div>";
                    }
                }
                // ==================== Module non reconnu
                else {
                    $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "<div class='module'>";
                    $Column2[$course_module['position']][$course_module['sortorder']]['content'] .= "
						<a href='" . $CFG->wwwroot . "/mod/" . $course_module['type'] . "/view.php?id=" . $course_module['cmid'] . "'>" . $course_module['object']->name . "</a></div>";
                }
                $Column2[$course_module['position']][$course_module['sortorder']]['moduleid'] = $course_module['id'];
                $Column2[$course_module['position']][$course_module['sortorder']]['display_mode'] = $course_module['display_mode'];
                $i++;
            }

            // ======================================================
            //
			//					Generate blocks
            //
			// ======================================================

            $blocks = SimplePageLib::getBlocks($page);
            include_once('../../../blocks/moodleblock.class.php');
            foreach ($blocks as $block) {
                include_once('../../../blocks/' . $block->blockname . '/block_' . $block->blockname . '.php');
                $classname = 'block_' . $block->blockname;
                $block_instance = new $classname;
                $block_instance->config = new StdClass;
                $block_instance->context = new StdClass;
                $block_instance->instance = new StdClass;
                $block_instance->page = $PAGE;
                $block_instance->instance->parentcontextid = $block->blockinstance;
                $block_instance->config = unserialize(base64_decode($block->configdata));
                $block_instance->context->id = $PAGE->context->id; //context::instance_by_id($PAGE->context->id);
                $bc = new block_contents();
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
                $Column2[$block->position][$block->sortorder]['moduleid'] = $block->item_id;
                $Column2[$block->position][$block->sortorder]['display_mode'] = 1;
            }


            // ======================================================
            //
			//					Build output
            //
			// ======================================================

            ksort($Column2['l']);
            ksort($Column2['c']);
            ksort($Column2['r']);
            foreach ($Column2 as $key => $selected_column) {
                if ($selected_column)
                    $selected_column = array_values($selected_column);
                for ($i = 0; $i < count($selected_column); $i++) {
                    if ($selected_column[$i]['display_mode'] == 2) {
                        $Column[$key] .= "<div style='border:2px dashed black;border-radius:10px;margin-bottom:5px;background-color:#aaaaaa;'><div class='header_module'>" . $selected_column[$i]['header'] . "</div>" . $selected_column[$i]['content'] . "</div>";
                    } else {
                        $Column[$key] .= "<div><div class='header_module'>" . $selected_column[$i]['header'] . "</div>" . $selected_column[$i]['content'] . "</div>";
                    }
                }
            }

            // ==================================================
            //
			//				Apply filters if necessary
            //
			// ==================================================		

            $active_filters = SimplePageLib::getActiveFilters();
            $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
            foreach ($active_filters as $currentfilter) {
                if (file_exists($CFG->dirroot . '/filter/' . $currentfilter->filter . '/filter.php')) {
                    require_once($CFG->dirroot . '/filter/' . $currentfilter->filter . '/filter.php');
                    $class_filter = "filter_" . $currentfilter->filter;
                    $filterplugin = new $class_filter($coursecontext, array());
                    $content_center = $Column['c'];
                    $content_left = $Column['l'];
                    $content_right = $Column['r'];
                    $Column['c'] = $filterplugin->filter($content_center);
                    $Column['r'] = $filterplugin->filter($content_right);
                    $Column['l'] = $filterplugin->filter($content_left);
                }
            }
        }
        require_once($CFG->dirroot . "/course/format/page/lib/model/dompdf/dompdf_config.inc.php");

        $html = '<html><body>';
        $html .='<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $html .='<link rel="stylesheet" type="text/css" href="format/page/lib/template/css/style.css"></head>';
        $html .= $Column['c'];
        $html .= '</body></html>';

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream("athena.pdf");
    }
}