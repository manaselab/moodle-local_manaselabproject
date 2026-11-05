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
 * Settings of local_manaselabproject.
 *
 * @package    local_manaselabproject
 * @category   admin
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_manaselabproject', new lang_string('pluginname', 'local_manaselabproject'));

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading(
            'local_manaselabproject/leadershipheading',
            new lang_string('leadership', 'local_manaselabproject'),
            new lang_string('leadership_desc', 'local_manaselabproject')
        ));

        // By short name, so the setting is the same on every site whatever the identifier of the role.
        $roles = [];
        foreach (role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT) as $role) {
            $roles[$role->shortname] = $role->localname;
        }
        $settings->add(new admin_setting_configselect(
            'local_manaselabproject/leadrole',
            new lang_string('leadrole', 'local_manaselabproject'),
            new lang_string('leadrole_desc', 'local_manaselabproject'),
            \local_manaselabproject\leadership::DEFAULT_LEAD_ROLE,
            $roles
        ));

        $settings->add(new admin_setting_configtext(
            'local_manaselabproject/finishedstatuses',
            new lang_string('finishedstatuses', 'local_manaselabproject'),
            new lang_string('finishedstatuses_desc', 'local_manaselabproject'),
            \local_manaselabproject\leadership::DEFAULT_FINISHED_STATUSES,
            PARAM_TEXT
        ));
    }

    $ADMIN->add('localplugins', $settings);
}
