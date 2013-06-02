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
 * File defining class used to backup simplepage courses
 *
 * @package simplepage
 */

/**
 * Class used to backup simplepage courses
 *
 * @package simplepage
 */
class backup_format_page_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */

    protected function define_course_plugin_structure() {

        $plugin = $this->get_plugin_element(null, '/course/format', 'page');
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);
        $pages = new backup_nested_element('pages');
        $page  = new backup_nested_element('page', array('id'), array(
            'courseid',
            'nameone',
            'nametwo',
            'display',
            'prefleftwidth',
            'prefcenterwidth',
            'prefrightwidth',
            'parent',
            'sortorder',
            'template',
            'showbuttons',
            'locks',
        ));

	$items = new backup_nested_element('items');
	$item = new backup_nested_element('item', array('id'), array(
            'pageid',
            'cmid',
            'blockinstance',
            'position',
            'sortorder',
            'visible',
	));

        // Now the format specific tree
        $pluginwrapper->add_child($pages);
        $pages->add_child($page);
	$page->add_child($items);
	$items->add_child($item);

        // Set source to populate the data
        $page->set_source_table('format_page', array('courseid' => backup::VAR_COURSEID));
        $item->set_source_table('format_page_items', array('pageid' => backup::VAR_PARENTID));


	$item->annotate_ids('block_instance', 'blockinstance');
	$item->annotate_ids('course_modules', 'cmid');

        return $plugin;
    }
}
