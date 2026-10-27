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

namespace local_manaselabproject;

use cache_helper;
use context_course;
use core\event\course_updated;
use core_course\customfield\course_handler;
use core_customfield\data_controller;
use core_customfield\field_controller;
use stdClass;

/**
 * The profile of a project course, as its lead edits it.
 *
 * The profile is made of the custom fields of the course. The core handler
 * decides which of them the current user may edit: a field the site has locked
 * needs moodle/course:changelockedcustomfields, so locking the fields that are
 * not part of the profile leaves only the profile in the form of the lead.
 *
 * The form goes one step per category of fields, in the order the site gives
 * the categories, so a long profile becomes several short screens.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class project {
    /** @var string Frankenstyle name of the plugin. */
    public const COMPONENT = 'local_manaselabproject';

    /** @var string Capability that opens the form. */
    public const CAPABILITY = 'local/manaselabproject:editproject';

    /** @var string Short name of the field that says what kind of space a course is. */
    public const SPACE_TYPE = 'spacetype';

    /** @var string Option of SPACE_TYPE that marks a course as a project. */
    public const TYPE_PROJECT = 'project';

    /**
     * The field of the course handler with the given short name.
     *
     * @param  string                $shortname Short name of the field.
     * @return field_controller|null            The field, or null when the site has not created it.
     */
    public static function get_field(string $shortname): ?field_controller {
        foreach (course_handler::create()->get_fields() as $field) {
            if ($field->get('shortname') === $shortname) {
                return $field;
            }
        }

        return null;
    }

    /**
     * The neutral keys of the options of a select field, by the value each one stores.
     *
     * A select field stores the position of the option, where 0 is the empty
     * option and the first line of the list is 1. The keys are read from the
     * configuration as the site typed them, before any filter formats them.
     *
     * @param  field_controller $field The field.
     * @return string[]                The keys, by stored value.
     */
    public static function get_option_keys(field_controller $field): array {
        $lines = preg_split(
            '/\s*\n\s*/',
            trim((string) $field->get_configdata_property('options')),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $keys = [];
        foreach ($lines as $position => $line) {
            $keys[$position + 1] = $line;
        }

        return $keys;
    }

    /**
     * Whether a course is a project: its space type holds the project option.
     *
     * @param  int  $courseid Identifier of the course.
     * @return bool           True for a project.
     */
    public static function is_project(int $courseid): bool {
        $field = self::get_field(self::SPACE_TYPE);
        if ($field === null || $field->get('type') !== 'select') {
            return false;
        }

        $position = array_search(self::TYPE_PROJECT, self::get_option_keys($field), true);
        if ($position === false) {
            return false;
        }

        $data = course_handler::create()->get_instance_data($courseid, true)[$field->get('id')] ?? null;

        return $data !== null && (int) $data->get_value() === $position;
    }

    /**
     * The steps of the form for the current user: one per category with fields to edit.
     *
     * A field enters when the handler lets the user edit and view it. The space
     * type never enters, because the form only opens on projects.
     *
     * @param  int   $courseid Identifier of the course.
     * @return array           Each step as ['name' => formatted name, 'fields' => data_controller[]].
     */
    public static function get_steps(int $courseid): array {
        $handler = course_handler::create();
        $fields = array_filter(
            $handler->get_editable_fields($courseid),
            fn(field_controller $field): bool => $field->get('shortname') !== self::SPACE_TYPE
                && $handler->can_view($field, $courseid)
        );

        $steps = [];
        foreach ($handler->get_instance_fields_data($fields, $courseid) as $data) {
            $category = $data->get_field()->get_category();
            $categoryid = (int) $category->get('id');
            if (!isset($steps[$categoryid])) {
                $steps[$categoryid] = ['name' => $category->get_formatted_name(), 'fields' => []];
            }
            $steps[$categoryid]['fields'][] = $data;
        }

        return array_values($steps);
    }

    /**
     * Saves the fields one step of the form has sent.
     *
     * The handler only writes the fields present in the data, so a step leaves
     * the fields of the other steps as they were. The rest of the course is not
     * touched: the event and the purge are those update_course() raises, so the
     * front page, the dashboard and the logs see the change.
     *
     * @param  stdClass $data Data of the form, with the identifier of the course in id.
     * @return void
     */
    public static function save(stdClass $data): void {
        global $DB;

        course_handler::create()->instance_form_save($data);

        cache_helper::purge_by_event('changesincourse');

        $course = $DB->get_record('course', ['id' => $data->id], 'id, shortname, fullname', MUST_EXIST);
        $updatedfields = [];
        foreach (array_keys((array) $data) as $name) {
            if (str_starts_with($name, 'customfield_')) {
                $updatedfields[$name] = '(updated)';
            }
        }

        course_updated::create([
            'objectid' => $course->id,
            'context' => context_course::instance($course->id),
            'other' => [
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'updatedfields' => $updatedfields,
            ],
        ])->trigger();
    }
}
