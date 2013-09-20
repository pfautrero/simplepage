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
 * global constants
 *
 * @package simplepage
 * @subpackage config
 */

/**
 * @todo : convert these globals into a singleton
 */
        global $MAIN_DIR;
	// ============ Define images theme

        /** just choose one style for your icons : 'default', 'grey' */
        define('IMG_FOLDER','grey');
        
        // ============ Define paths to images
        
	define('CROSS','/pix/t/delete.gif');
	define('CROSS2','/pix/t/edit-delete.gif');
        define('ARROW_UP','/pix/t/up.gif');
	define('ARROW_DOWN','/pix/t/down.gif');
	define('EXPAND','/pix/t/expanded.png');
	define('SEE_PAGE','/pix/f/web-32.png');
        
	define('ITEM_MENU_VISIBLE','/course/format/'.$MAIN_DIR.'/images/ok.png');
	define('ITEM_MENU_HIDDEN','/course/format/'.$MAIN_DIR.'/images/nok.png');
        define('PDF','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/pdf.png');
        define('DUPLICATE','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/edit-copy.png');
        define('EDIT','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/edit.png');
        define('EDIT_OK','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/edit_ok.png');
        define('GHOST','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/ghost.png');
        define('DELETE','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/delete.png');
        define('ADD_MODULE','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/addfile.png');
        define('FOLDER','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/folder.png');
        define('MOVE','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/move.png');
        define('EYE_OPENED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/opened.png');
        define('EYE_CLOSED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/closed.png');
	define('PREVIOUS_ENABLED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/previous.png');
	define('PREVIOUS_DISABLED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/noprevious.png');
	define('NEXT_ENABLED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/next.png');
	define('NEXT_DISABLED','/course/format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/nonext.png');
	define('AJAX_LOADER','format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/ajax-loader.gif');
	define('TOOLBOX','format/'.$MAIN_DIR.'/lib/template/images/'.IMG_FOLDER.'/outils.jpg');

	// ============ Define path to scripts and css
	define('JQUERY','format/'.$MAIN_DIR.'/lib/template/js/jquery-1.8.2.js');
	define('JQUERYUI','format/'.$MAIN_DIR.'/lib/template/js/jquery-ui-1.9.1.custom.min.js');
	define('JQUERYUICSS','format/'.$MAIN_DIR.'/lib/template/css/jquery-ui-1.9.1.custom.css');
        define('JQUERYNESTABLE','format/'.$MAIN_DIR.'/lib/template/js/jquery.nestable.js');
        define('STYLECSS','format/'.$MAIN_DIR.'/lib/template/css/style.css?version='.$plugin->version);
	// ============= Globals
	define('NO_LINK', 0);
	define('PREVIOUS_LINK', 1);
	define('NEXT_LINK', 2);
	define('AFFICHER_COMME_ONGLET', 0x2);
	define('PUBLISH', 0x1);
	define('DISPLAY_POPUP', 0x6);
        
        
        // ============= Permissions
        define('PERMISSION_DELETE_MODULE', 'moodle/course:manageactivities');
        define('PERMISSION_ACCESS_EDIT_PAGE', 'moodle/course:manageactivities');
        define('PERMISSION_DELETE_ITEM', 'moodle/course:manageactivities');
        define('PERMISSION_DUPLICATE_ITEM', 'moodle/course:manageactivities');
        define('PERMISSION_DELETE_PAGE', 'moodle/course:manageactivities');
        define('PERMISSION_SHOW_HIDE_PAGE', 'moodle/course:manageactivities');
        define('PERMISSION_ADD_NEW_PAGE', 'moodle/course:manageactivities');
        define('PERMISSION_ADD_NEW_MODULE', 'moodle/course:manageactivities');
        /**
         * @todo : centralize ALL permissions here
         */
        
        

?>
