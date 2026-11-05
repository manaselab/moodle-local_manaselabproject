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

use context;
use context_course;
use context_system;
use core\event\role_assigned;
use core\message\message;
use core_course\customfield\course_handler;
use core_user;
use moodle_url;
use stdClass;

/**
 * The rule of one lead: a project has one lead, and a lead leads one project that has not finished.
 *
 * The rule is checked when the lead role is assigned in a project, after the
 * assignment is saved: a role assignment that breaks it is undone, so the user
 * stays enrolled without the lead role. The user who made the assignment and the
 * users who receive the alerts get a message that says why.
 *
 * An assignment that an enrolment plugin manages (cohort sync, course meta link)
 * is not undone, because the next sync would make it again: the message says so
 * and the control report of the site lists it.
 *
 * A project has finished when its status holds one of the finished keys. A
 * project without status has not finished.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leadership {
    /** @var string Short name of the lead role when the site has not set one. */
    public const DEFAULT_LEAD_ROLE = 'projectlead';

    /** @var string Keys of the status that finish a project when the site has not set them. */
    public const DEFAULT_FINISHED_STATUSES = 'closed,graduated';

    /** @var string Short name of the field that holds the status of a project. */
    public const PROJECT_STATUS = 'projectstatus';

    /** @var string The project has another lead. */
    public const CONFLICT_COURSE = 'course';

    /** @var string The user leads another project that has not finished. */
    public const CONFLICT_USER = 'user';

    /** @var string Capability of the users who receive the alerts. */
    public const ALERT_CAPABILITY = 'local/manaselabproject:receiveleadalerts';

    /** @var string Name of the message provider of the alerts. */
    public const MESSAGE_PROVIDER = 'leadconflict';

    /**
     * Identifier of the lead role, from the setting leadrole.
     *
     * @return int|null The identifier, or null when the site has no such role.
     */
    public static function get_lead_roleid(): ?int {
        global $DB;

        $shortname = trim((string) get_config(project::COMPONENT, 'leadrole')) ?: self::DEFAULT_LEAD_ROLE;
        $roleid = $DB->get_field('role', 'id', ['shortname' => $shortname]);

        return $roleid === false ? null : (int) $roleid;
    }

    /**
     * Keys of the status that finish a project, from the setting finishedstatuses.
     *
     * @return string[] The keys.
     */
    public static function get_finished_statuses(): array {
        $setting = get_config(project::COMPONENT, 'finishedstatuses');
        if ($setting === false) {
            $setting = self::DEFAULT_FINISHED_STATUSES;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $setting)), 'strlen'));
    }

    /**
     * The value stored by an option of a select field, or null when the field has no such option.
     *
     * @param  string   $shortname Short name of the field.
     * @param  string[] $keys      Keys of the options.
     * @return array               [field id or null, stored values of the keys the field has].
     */
    private static function get_positions(string $shortname, array $keys): array {
        $field = project::get_field($shortname);
        if ($field === null || $field->get('type') !== 'select') {
            return [null, []];
        }

        $positions = array_keys(array_intersect(project::get_option_keys($field), $keys));

        return [(int) $field->get('id'), $positions];
    }

    /**
     * Whether a project has finished: its status holds one of the finished keys.
     *
     * @param  int  $courseid Identifier of the course.
     * @return bool           True when it has finished.
     */
    public static function is_finished(int $courseid): bool {
        [$fieldid, $positions] = self::get_positions(self::PROJECT_STATUS, self::get_finished_statuses());
        if ($fieldid === null || !$positions) {
            return false;
        }

        $data = course_handler::create()->get_instance_data($courseid, true)[$fieldid] ?? null;

        return $data !== null && in_array((int) $data->get_value(), $positions, true);
    }

    /**
     * The other projects the user leads that have not finished.
     *
     * @param  int   $userid   Identifier of the user.
     * @param  int   $roleid   Identifier of the lead role.
     * @param  int   $courseid Identifier of the course to leave out.
     * @return array           The courses, by identifier, with id, shortname and fullname.
     */
    public static function get_active_projects(int $userid, int $roleid, int $courseid): array {
        global $DB;

        [$typeid, $project] = self::get_positions(project::SPACE_TYPE, [project::TYPE_PROJECT]);
        if ($typeid === null || !$project) {
            return [];
        }

        $params = [
            'userid' => $userid,
            'roleid' => $roleid,
            'courseid' => $courseid,
            'contextlevel' => CONTEXT_COURSE,
            'typeid' => $typeid,
            'project' => reset($project),
        ];

        $finished = '';
        [$statusid, $positions] = self::get_positions(self::PROJECT_STATUS, self::get_finished_statuses());
        if ($statusid !== null && $positions) {
            [$insql, $inparams] = $DB->get_in_or_equal($positions, SQL_PARAMS_NAMED, 'status');
            $finished = "AND NOT EXISTS (
                            SELECT 1
                              FROM {customfield_data} st
                             WHERE st.instanceid = c.id
                               AND st.fieldid = :statusid
                               AND st.intvalue $insql
                         )";
            $params += ['statusid' => $statusid] + $inparams;
        }

        $sql = "SELECT DISTINCT c.id, c.shortname, c.fullname
                  FROM {role_assignments} ra
                  JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.contextlevel = :contextlevel
                  JOIN {course} c ON c.id = ctx.instanceid
                  JOIN {customfield_data} tp ON tp.instanceid = c.id AND tp.fieldid = :typeid AND tp.intvalue = :project
                 WHERE ra.userid = :userid
                   AND ra.roleid = :roleid
                   AND c.id <> :courseid
                       $finished
              ORDER BY c.id";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * The other users who hold the lead role in a course.
     *
     * @param  int   $courseid Identifier of the course.
     * @param  int   $roleid   Identifier of the lead role.
     * @param  int   $userid   Identifier of the user to leave out.
     * @return int[]           Identifiers of the users.
     */
    public static function get_other_leads(int $courseid, int $roleid, int $userid): array {
        global $DB;

        $sql = "SELECT DISTINCT userid
                  FROM {role_assignments}
                 WHERE contextid = :contextid AND roleid = :roleid AND userid <> :userid";

        return array_map('intval', $DB->get_fieldset_sql($sql, [
            'contextid' => context_course::instance($courseid)->id,
            'roleid' => $roleid,
            'userid' => $userid,
        ]));
    }

    /**
     * Whether giving the lead role in a project to a user breaks the rule, and why.
     *
     * The project is checked first: when it already has a lead, that is the reason.
     *
     * @param  int           $courseid Identifier of the course.
     * @param  int           $userid   Identifier of the user.
     * @return stdClass|null           Null when the rule holds; else reason (a CONFLICT_ constant)
     *                                 and courses (the other projects of the user, for CONFLICT_USER).
     */
    public static function get_conflict(int $courseid, int $userid): ?stdClass {
        $roleid = self::get_lead_roleid();
        if ($roleid === null || !project::is_project($courseid)) {
            return null;
        }

        if (self::get_other_leads($courseid, $roleid, $userid)) {
            return (object) ['reason' => self::CONFLICT_COURSE, 'courses' => []];
        }

        $courses = self::get_active_projects($userid, $roleid, $courseid);
        if ($courses) {
            return (object) ['reason' => self::CONFLICT_USER, 'courses' => $courses];
        }

        return null;
    }

    /**
     * Applies the rule to a role assignment that has just been saved.
     *
     * @param  role_assigned $event The event of the assignment.
     * @return bool                 True when the assignment has been undone.
     */
    public static function check_assignment(role_assigned $event): bool {
        $roleid = self::get_lead_roleid();
        $context = context::instance_by_id($event->contextid, IGNORE_MISSING);
        if ($roleid === null || (int) $event->objectid !== $roleid
                || !$context || $context->contextlevel != CONTEXT_COURSE) {
            return false;
        }

        $courseid = (int) $context->instanceid;
        $userid = (int) $event->relateduserid;
        $conflict = self::get_conflict($courseid, $userid);
        if ($conflict === null) {
            return false;
        }

        $component = (string) ($event->other['component'] ?? '');
        $undone = $component === '';
        if ($undone) {
            role_unassign($roleid, $userid, $context->id);
        }

        self::notify($courseid, $userid, (int) $event->userid, $conflict, $undone);

        return $undone;
    }

    /**
     * Sends the alert to the user who made the assignment and to the users with the alert capability.
     *
     * @param  int      $courseid   Identifier of the project.
     * @param  int      $userid     Identifier of the user who received the lead role.
     * @param  int      $assignerid Identifier of the user who made the assignment, 0 when none.
     * @param  stdClass $conflict   The conflict, as get_conflict() returns it.
     * @param  bool     $undone     Whether the assignment has been undone.
     * @return void
     */
    private static function notify(int $courseid, int $userid, int $assignerid, stdClass $conflict, bool $undone): void {
        global $DB;

        $recipients = get_users_by_capability(context_system::instance(), self::ALERT_CAPABILITY, 'u.id');
        $recipientids = array_map('intval', array_keys($recipients));
        if ($assignerid > 0 && !isguestuser($assignerid)) {
            $recipientids[] = $assignerid;
        }
        $recipientids = array_unique($recipientids);
        if (!$recipientids) {
            return;
        }

        $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname', MUST_EXIST);
        $lead = core_user::get_user($userid, '*', MUST_EXIST);
        $url = new moodle_url('/user/index.php', ['id' => $courseid]);
        $component = project::COMPONENT;

        foreach ($recipientids as $recipientid) {
            $recipient = core_user::get_user($recipientid);
            if (!$recipient || $recipient->deleted || $recipient->suspended) {
                continue;
            }

            // Each recipient reads the alert in the language of the account.
            $previouslang = force_current_language($recipient->lang);
            $a = (object) [
                'user' => fullname($lead),
                'course' => format_string($course->fullname, true, ['context' => context_course::instance($courseid)]),
                'others' => implode(', ', array_map(
                    fn(stdClass $other): string => format_string($other->fullname, true,
                        ['context' => context_course::instance($other->id)]),
                    $conflict->courses
                )),
                'url' => $url->out(false),
            ];
            $reason = get_string('leadconflict_' . $conflict->reason, $component, $a);
            $outcome = get_string($undone ? 'leadconflict_undone' : 'leadconflict_kept', $component, $a);
            $text = $reason . "

" . $outcome . "

" . get_string('leadconflict_howto', $component, $a);

            $message = new message();
            $message->component = $component;
            $message->name = self::MESSAGE_PROVIDER;
            $message->userfrom = core_user::get_noreply_user();
            $message->userto = $recipient;
            $message->subject = get_string('leadconflict_subject', $component, $a);
            $message->fullmessage = $text;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = text_to_html(s($text), false, false, true);
            $message->smallmessage = $reason;
            $message->notification = 1;
            $message->contexturl = $url->out(false);
            $message->contexturlname = get_string('participants');
            $message->courseid = $courseid;
            force_current_language($previouslang);

            message_send($message);
        }
    }
}
