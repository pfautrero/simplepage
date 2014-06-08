<?php

/**
 * Unit tests
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package simplepage
 */
global $CFG, $MAIN_DIR;
$MAIN_DIR = 'page';
if (!isset($plugin))
    $plugin = new stdClass();
include_once($CFG->dirroot . '/course/format/page/version.php');
include_once($CFG->dirroot . '/course/format/page/lib/model/lib.php');
include_once($CFG->dirroot . '/course/format/page/lib/model/page.php');
include_once($CFG->dirroot 
    . '/course/format/page/lib/controller/frontcontroller.class.php');
include_once($CFG->dirroot . '/course/format/page/globals.php');

class ajaxdisplay_test extends advanced_testcase
{
    function testAjaxDisplay() 
    {
        global $USER, $COURSE, $PAGE, $DB;
        $this->resetAfterTest(true);

        $category = $this->getDataGenerator()
                         ->create_category(array('name' => 'My Category'));
        $COURSE = $this->getDataGenerator()
                       ->create_course(
                           array(
                                   'name' => 'My first Course', 
                                   'category' => $category->id, 
                                   'format' => 'page'
                                   )
                       );

        $page = new simplepage\Page();
        $page->_courseid = $COURSE->id;
        $pageid = $page->save();
        unset($page);
        
        $PAGE = new moodle_page();
        $PAGE->set_context(context_course::instance($COURSE->id));

        $this->setAdminUser();
        $USER->editing = 1;

        $_SESSION['courseid'] = $COURSE->id;
        $_SERVER['REQUEST_URI'] = "/course/view.php?id=" 
            . $COURSE->id . "&action=ajaxdisplay";
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_POST['current'] = $pageid;
        $_POST['sesskey'] = $USER->sesskey;

        ob_start();
        $front = frontController::getInstance()->dispatch();
        $out = ob_get_contents();
        ob_end_clean();
        
        $page = new simplepage\Page($pageid);
        $this->assertTrue($page->isHidden());
    }
}