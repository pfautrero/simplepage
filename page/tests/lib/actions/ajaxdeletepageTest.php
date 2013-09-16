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
include_once($CFG->dirroot . '/course/format/page/lib/model/lib.php');
include_once($CFG->dirroot . '/course/format/page/lib/controller/frontcontroller.class.php');
include_once($CFG->dirroot . '/course/format/page/version.php');
include_once($CFG->dirroot . '/course/format/page/globals.php');

class ajaxdeletepage_test extends advanced_testcase 
{

    function testAjaxDeletePage() 
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
        $pageid = SimplePageLib::addPage("First Page", $COURSE->id, 0);

        $PAGE = new moodle_page();
        $PAGE->set_context(get_context_instance(CONTEXT_COURSE, $COURSE->id));

        $this->setAdminUser();
        $USER->editing = 1;

        $_SESSION['courseid'] = $COURSE->id;
        $_SERVER['REQUEST_URI'] = "/course/view.php?id=" 
            . $COURSE->id 
            . "&action=ajaxdeletepage";
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_POST['id'] = $COURSE->id;
        $_POST['current'] = $pageid;
        $_POST['sesskey'] = $USER->sesskey;
        ob_start();
        $front = frontController::getInstance()->dispatch();
        $out = ob_get_contents();
        ob_end_clean();
        $this->assertFalse(
            $DB->record_exists('format_page', array('id' => $pageid))
        );
    }
}