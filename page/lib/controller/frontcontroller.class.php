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


global $CFG;
include_once($CFG->dirroot."/course/format/page/lib/controller/request.class.php");
include_once($CFG->dirroot."/course/format/page/lib/controller/response.class.php");
include_once($CFG->dirroot."/course/format/page/lib/view/view.class.php");
/**
 * Front controller (routing, rendering)
 *
 * @package    simplepage
 * @subpackage frontcontroller
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class FrontController
{
    private $_defaults = array('action' => 'index');
    private $_request;
    private $_response;
    private static $_instance = null;

    private function __construct()
    {

        $this->_request = new Request();
        $this->_response = new Response();
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function dispatch($defaults = null)
    {
        $parsed = $this->_request->route();
        $parsed = array_merge($this->_defaults, $parsed);
        $this->forward($parsed['action']);
    }

    public function forward($action)
    {
        global $COURSE;
        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if ($coursecontext == null) {
            $content = "context_null";
            $this->_response->addVar('content', $content);
            $this->render($CFG->dirroot."/course/format/page/lib/template/ajaxSuccess.php");
            $this->_response->printOut;      
            return;
        }        
        $command = $this->_getCommand($action);
        $command->launch($this->_request, $this->_response);
    }

    private function _getCommand($action)
    {
        global $CFG;
        $path = $CFG->dirroot."/course/format/page/lib/actions/$action.php";
        if(!file_exists($path)){
                $action="index";
                $path = $CFG->dirroot."/course/format/page/lib/actions/$action.php";			
        }
        require_once($path);
        $class = $action.'Action';

        return new $class($this);
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function redirect($url)
    {
        $this->_response->redirect($url);
    }

    public function render($file)
    {
        $view = new View();
        $this->_response->setBody($view->render($file,$this->_response->getVars()));
    }
}

?>