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
 * Behat steps for local_manaselabproject.
 *
 * @package    local_manaselabproject
 * @category   test
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Named pages of the plugin.
 *
 * @package    local_manaselabproject
 * @category   test
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_manaselabproject extends behat_base {
    /**
     * Resolves a page of the plugin that belongs to one course.
     *
     * Recognised page names are:
     * | Page type    | Identifier   | Description                        |
     * | Edit project | Course name  | The form that edits the profile    |
     *
     * @param  string     $type       Type of the page.
     * @param  string     $identifier Full name of the course.
     * @return moodle_url             The URL of the page.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        switch (strtolower($type)) {
            case 'edit project':
                return new moodle_url('/local/manaselabproject/edit.php', [
                    'courseid' => $this->get_course_id($identifier),
                ]);
            default:
                throw new Exception('Unrecognised local_manaselabproject page type "' . $type . '."');
        }
    }
}
