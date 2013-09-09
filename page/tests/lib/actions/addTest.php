<?php

/**
 * Unit tests
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package simplepage
 */
global $CFG,$MAIN_DIR;
$MAIN_DIR = 'page';
if (!isset($plugin)) $plugin = new stdClass();
include($CFG->dirroot.'/course/format/page/lib/controller/frontcontroller.class.php');
include($CFG->dirroot.'/course/format/page/version.php');
include($CFG->dirroot.'/course/format/page/globals.php');

class add_test extends advanced_testcase {

     function testDisplayAdd() {
         global $USER,$COURSE,$PAGE;
         $this->resetAfterTest(true);
         
         $category = $this->getDataGenerator()->create_category(array('name'=>'My Category'));
         $COURSE = $this->getDataGenerator()->create_course(array('name'=>'My first Course','category'=>$category->id,'format'=>'page'));
         
         $PAGE = new moodle_page();
         $PAGE->set_context(get_context_instance(CONTEXT_COURSE, $COURSE->id));         
         
         /**
          * Try to access 'Add Page' with authentication and edition activated
          */
         $this->setAdminUser();
         $USER->editing = 1;
         $_SERVER['REQUEST_URI'] = "/course/view.php?id=".$COURSE->id."&action=add";
         $_SERVER['REQUEST_METHOD'] = "GET";
         $_GET['id'] = $COURSE->id;
         ob_start();
         $front = frontController::getInstance()->dispatch();
         $out = ob_get_contents();
         ob_end_clean();
         $this->assertContains("Ajouter une nouvelle page",$out);

         /**
          * Try to access 'Add Page' without authentication
          */         
         $this->setUser(null);
         $USER->editing = 0;
         $_SERVER['REQUEST_URI'] = "/course/view.php?id=".$COURSE->id."&action=add";
         $_SERVER['REQUEST_METHOD'] = "GET";
         $_GET['id'] = $COURSE->id;
         ob_start();
         $front = frontController::getInstance()->dispatch();
         $out = ob_get_contents();
         ob_end_clean();
         $this->assertContains("page à accès restreint",$out);
         
     }
     
     
}
?>
