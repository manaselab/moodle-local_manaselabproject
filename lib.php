<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Navigation callbacks for local_manaselabproject.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_manaselabproject\project;

/**
 * Adds «Edit my project» to the menu of a project course, for whoever may edit it.
 *
 * @param  navigation_node $navigation The node of the course administration.
 * @param  stdClass        $course     The course.
 * @param  context         $context    The context of the course.
 * @return void
 */
function local_manaselabproject_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context $context
): void {
    if ($course->id == SITEID || !has_capability(project::CAPABILITY, $context) || !project::is_project($course->id)) {
        return;
    }

    $navigation->add(
        get_string('editproject', project::COMPONENT),
        new moodle_url('/local/manaselabproject/edit.php', ['courseid' => $course->id]),
        navigation_node::TYPE_SETTING,
        null,
        'local_manaselabproject_edit',
        new pix_icon('i/edit', '')
    );
}
