<?php

/**
 * Unit tests
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package simplepage
 */
global $CFG, $MAIN_DIR;
$MAIN_DIR = 'page';
if (!isset($plugin)) {
    $plugin = new stdClass();
}
include_once($CFG->dirroot . '/course/format/page/version.php');
include_once($CFG->dirroot . '/course/format/page/lib/actions/export.php');
include_once($CFG->dirroot 
    . '/course/format/page/lib/controller/frontcontroller.class.php');
include_once($CFG->dirroot . '/course/format/page/globals.php');

class export_test extends advanced_testcase
{
    function testExport() 
    {
        $this->resetAfterTest(true);

        // create a categorized course
        $category = $this->getDataGenerator()
                         ->create_category(array('name' => 'My Category'));
        $course = $this->getDataGenerator()
                       ->create_course(
                           array(
                                'name' => 'My first Course', 
                                'category' => $category->id, 
                                'format' => 'page'
                           )
                       );
        // create users and enrol them in the course
        $user = array();
        for ($i=0;$i<100;$i++) {
            $user[$i] = $this->getDataGenerator()
                             ->create_user(
                                array(
                                    'email' => 'user' . $i . '@test.com', 
                                    'username' => 'user' . $i,
                                    'firstname' => 'firstname_user' . $i,
                                    'lastname' => 'lastname_user' . $i
                                )
                             );        
            if ($i < 50) {
                $this->getDataGenerator()->enrol_user($user[$i]->id,$course->id,2);
            }
            else {
                $this->getDataGenerator()->enrol_user($user[$i]->id,$course->id);
            }
        }
        
        $export = new exportAction(frontController::getInstance());
        $csv = $export->generateCsv($course->id);
        $csv_array=explode(PHP_EOL,$csv);
        $this->assertEquals(count($user)+2, count($csv_array));
    }
}