<?php

/**
 * Unit tests for main simplepage library
 * Reminder :
 * php admin/tool/phpunit/cli/init.php
 * vendor/bin/phpunit simplepage_lib_test course/format/page/tests/simplepage_test.php
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package simplepage
 */
global $CFG;
include_once($CFG->dirroot.'/course/format/page/lib/model/lib.php');

class simplepage_lib_test extends advanced_testcase {

    /** 
     * test add and delete operarions on a page
     * 
     * @global type $DB
     */
    
     function testPageAddDelete() {
         global $DB;
         $this->resetAfterTest(true);
         $this->setAdminUser();
         $category = $this->getDataGenerator()->create_category(array('name'=>'My Category'));
         $course = $this->getDataGenerator()->create_course(array('name'=>'My first Course','category'=>$category->id,'format'=>'page'));
         $pageid =  SimplePageLib::addPage("First Page", $course->id, 0);
         $this->assertTrue($DB->record_exists('format_page',array('id'=>$pageid)));
         SimplePageLib::deletePage($pageid);
         $this->assertFalse($DB->record_exists('format_page',array('id'=>$pageid)));
     }
     
     
}
?>
