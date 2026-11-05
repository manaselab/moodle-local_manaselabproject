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
 * Tests for the rule of one lead per project.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_manaselabproject;

use advanced_testcase;
use context_course;
use context_system;
use core_course\customfield\course_handler;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * Tests for {@see leadership} and {@see observer}.
 *
 * The fixture copies the fields of the site: the space type, where project is
 * the third option, and the status of a project, with its five keys.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(leadership::class)]
#[CoversClass(observer::class)]
final class leadership_test extends advanced_testcase {
    /** @var int Stored value of the status active. */
    private const ACTIVE = 1;

    /** @var int Stored value of the status paused. */
    private const PAUSED = 2;

    /** @var int Stored value of the status closed. */
    private const CLOSED = 3;

    /** @var int Stored value of the status graduated. */
    private const GRADUATED = 4;

    /** @var int Identifier of the lead role. */
    private int $leadrole;

    /** @var int Identifier of the member role. */
    private int $memberrole;

    /** @var stdClass A user with the manager role in the system, who receives the alerts. */
    private stdClass $manager;

    /**
     * Creates the fields, the roles and the manager.
     *
     * @return void
     */
    protected function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $fields = $generator->get_plugin_generator('core_customfield');
        $category = $fields->create_category(['name' => 'Manas E-Lab']);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => project::SPACE_TYPE,
            'type' => 'select',
            'configdata' => ['options' => "call\ntraining\nproject", 'locked' => 1],
        ]);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => leadership::PROJECT_STATUS,
            'type' => 'select',
            'configdata' => ['options' => "active\npaused\nclosed\ngraduated\nincorporated", 'locked' => 1],
        ]);

        $this->leadrole = $generator->create_role(['shortname' => 'projectlead', 'archetype' => 'student']);
        $this->memberrole = $generator->create_role(['shortname' => 'projectmember', 'archetype' => 'student']);

        $this->manager = $generator->create_user();
        $managerrole = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        role_assign($managerrole, $this->manager->id, context_system::instance());
    }

    /**
     * Creates a project.
     *
     * @param  int|null $status Stored value of the status, or null for a project without status.
     * @return stdClass         The course.
     */
    private function create_project(?int $status = self::ACTIVE): stdClass {
        $record = ['customfield_spacetype' => 3];
        if ($status !== null) {
            $record['customfield_projectstatus'] = $status;
        }

        return $this->getDataGenerator()->create_course($record);
    }

    /**
     * Sets the status of a project, as the manager does from the settings of the course.
     *
     * @param  stdClass $course The course.
     * @param  int      $status Stored value of the status.
     * @return void
     */
    private function set_status(stdClass $course, int $status): void {
        $data = (object) ['id' => $course->id, 'customfield_projectstatus' => $status];
        course_handler::create()->instance_form_save($data);
    }

    /**
     * Identifiers of the users with the lead role in a course.
     *
     * @param  stdClass $course The course.
     * @return int[]            The identifiers, sorted.
     */
    private function get_leads(stdClass $course): array {
        global $DB;

        $ids = array_map('intval', $DB->get_fieldset_select('role_assignments', 'userid', 'contextid = ? AND roleid = ?',
            [context_course::instance($course->id)->id, $this->leadrole]));
        sort($ids);

        return $ids;
    }

    /**
     * The first lead of a project keeps the role, and nobody is alerted.
     */
    public function test_first_lead_allowed(): void {
        $course = $this->create_project();
        $sink = $this->redirectMessages();

        $lead = $this->getDataGenerator()->create_and_enrol($course, 'projectlead');

        $this->assertSame([(int) $lead->id], $this->get_leads($course));
        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * A second lead in the same project is undone, stays enrolled, and the assigner and the manager are alerted.
     */
    public function test_second_lead_undone(): void {
        global $USER;
        $course = $this->create_project();
        $generator = $this->getDataGenerator();
        $first = $generator->create_and_enrol($course, 'projectlead');
        $sink = $this->redirectMessages();

        $second = $generator->create_and_enrol($course, 'projectlead');

        $this->assertSame([(int) $first->id], $this->get_leads($course));
        $this->assertTrue(is_enrolled(context_course::instance($course->id), $second));

        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);
        $recipients = array_map(fn($message): int => (int) $message->useridto, $messages);
        sort($recipients);
        $this->assertSame([(int) $USER->id, (int) $this->manager->id], $recipients);
        $this->assertSame('local_manaselabproject', $messages[0]->component);
        $this->assertSame(leadership::MESSAGE_PROVIDER, $messages[0]->eventtype);
        $this->assertStringContainsString(fullname($second), $messages[0]->fullmessage);
    }

    /**
     * The lead of a project that has not finished cannot lead another one, with or without status.
     */
    public function test_lead_of_active_project_undone(): void {
        $generator = $this->getDataGenerator();
        foreach ([self::ACTIVE, self::PAUSED, null] as $status) {
            $first = $this->create_project($status);
            $second = $this->create_project();
            $lead = $generator->create_and_enrol($first, 'projectlead');
            $sink = $this->redirectMessages();

            $generator->enrol_user($lead->id, $second->id, 'projectlead');

            $this->assertSame([], $this->get_leads($second));
            $this->assertSame([(int) $lead->id], $this->get_leads($first));
            $messages = $sink->get_messages();
            $this->assertCount(2, $messages);
            $this->assertStringContainsString($first->fullname, $messages[0]->fullmessage);
            $sink->close();
        }
    }

    /**
     * The lead of a project that has finished may lead a new one.
     */
    public function test_lead_of_finished_project_allowed(): void {
        $generator = $this->getDataGenerator();
        foreach ([self::CLOSED, self::GRADUATED] as $status) {
            $first = $this->create_project();
            $second = $this->create_project();
            $lead = $generator->create_and_enrol($first, 'projectlead');
            $this->set_status($first, $status);

            $generator->enrol_user($lead->id, $second->id, 'projectlead');

            $this->assertSame([(int) $lead->id], $this->get_leads($second));
        }
    }

    /**
     * The finished statuses come from the setting.
     */
    public function test_finished_statuses_setting(): void {
        $first = $this->create_project(self::CLOSED);
        $this->assertTrue(leadership::is_finished($first->id));

        set_config('finishedstatuses', 'graduated', 'local_manaselabproject');
        $this->assertFalse(leadership::is_finished($first->id));

        $second = $this->create_project();
        $generator = $this->getDataGenerator();
        $lead = $generator->create_and_enrol($first, 'projectlead');
        $generator->enrol_user($lead->id, $second->id, 'projectlead');
        $this->assertSame([], $this->get_leads($second));
    }

    /**
     * A course that is not a project takes any number of leads, and a lead there does not count.
     */
    public function test_not_a_project(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['customfield_spacetype' => 2]);
        $sink = $this->redirectMessages();

        $first = $generator->create_and_enrol($course, 'projectlead');
        $second = $generator->create_and_enrol($course, 'projectlead');
        $project = $this->create_project();
        $generator->enrol_user($first->id, $project->id, 'projectlead');

        $this->assertSame([(int) $first->id, (int) $second->id], $this->get_leads($course));
        $this->assertSame([(int) $first->id], $this->get_leads($project));
        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * The hand-over works when the role is removed from the current lead before the new one gets it.
     */
    public function test_handover(): void {
        $course = $this->create_project();
        $generator = $this->getDataGenerator();
        $old = $generator->create_and_enrol($course, 'projectlead');
        $new = $generator->create_and_enrol($course, 'projectmember');
        $context = context_course::instance($course->id);
        $sink = $this->redirectMessages();

        role_unassign($this->leadrole, $old->id, $context->id);
        role_assign($this->memberrole, $old->id, $context->id);
        role_assign($this->leadrole, $new->id, $context->id);
        role_unassign($this->memberrole, $new->id, $context->id);

        $this->assertSame([(int) $new->id], $this->get_leads($course));
        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * The hand-over in the wrong order is undone: the new lead is given the role while the old one has it.
     */
    public function test_handover_wrong_order(): void {
        $course = $this->create_project();
        $generator = $this->getDataGenerator();
        $old = $generator->create_and_enrol($course, 'projectlead');
        $new = $generator->create_and_enrol($course, 'projectmember');

        role_assign($this->leadrole, $new->id, context_course::instance($course->id)->id);

        $this->assertSame([(int) $old->id], $this->get_leads($course));
    }

    /**
     * An assignment an enrolment plugin manages is kept, and the alert says so.
     */
    public function test_component_assignment_kept(): void {
        $course = $this->create_project();
        $generator = $this->getDataGenerator();
        $first = $generator->create_and_enrol($course, 'projectlead');
        $second = $generator->create_user();
        $sink = $this->redirectMessages();

        role_assign($this->leadrole, $second->id, context_course::instance($course->id)->id, 'enrol_cohort', 1);

        $expected = [(int) $first->id, (int) $second->id];
        sort($expected);
        $this->assertSame($expected, $this->get_leads($course));
        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString(
            get_string('leadconflict_kept', 'local_manaselabproject'),
            $messages[0]->fullmessage
        );
    }

    /**
     * Nothing happens when the site has no lead role.
     */
    public function test_no_lead_role(): void {
        set_config('leadrole', 'nosuchrole', 'local_manaselabproject');
        $course = $this->create_project();
        $generator = $this->getDataGenerator();
        $generator->create_and_enrol($course, 'projectlead');
        $generator->create_and_enrol($course, 'projectlead');

        $this->assertCount(2, $this->get_leads($course));
    }
}

