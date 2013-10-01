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
 * Abstract class for actions classes
 *
 * @package    simplepage
 * @subpackage frontcontroller
 * @copyright  2012 Pascal Fautrero - CRDP Versailles
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class Action {

    protected $_controller;
    protected $_registry;

    public function __construct($controller) {
        $this->_controller = $controller; 
        $this->_registry = $controller->_registry; 
    }

    abstract public function launch(Request $request, Response $response);

    public function render($file) {
        $this->_controller->render($file);
    }

    public function printOut() {
        $this->_controller->getResponse()->printOut();
    }

    protected function _forward($module, $action) {
        $this->_controller->forward($module, $action);
    }

    protected function _redirect($url) {
        $this->_controller->redirect($url);
    }
}