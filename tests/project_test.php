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
 * Tests for the profile of a project.
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
use core_customfield\data_controller;
use local_manaselabproject\form\edit_project;
use navigation_node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use stdClass;

/**
 * Tests for {@see project}, the form and the navigation callback.
 *
 * The fixture copies the fields of the site: a category with the space type,
 * a field of calls the site locks and the three fields of the profile, and a
 * second category with one more field of the profile.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(project::class)]
#[CoversClass(edit_project::class)]
#[CoversFunction('local_manaselabproject_extend_navigation_course')]
final class project_test extends advanced_testcase {
    /** @var stdClass The project course. */
    private stdClass $course;

    /** @var stdClass A user with the project lead role. */
    private stdClass $lead;

    /** @var stdClass A user with the project member role. */
    private stdClass $member;

    /**
     * Creates the fields, a project and its two users.
     *
     * @return void
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->dirroot . '/local/manaselabproject/lib.php');
        $this->resetAfterTest();
        // The site creates its courses as an administrator, who may also fill the locked fields.
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $fields = $generator->get_plugin_generator('core_customfield');

        $category = $fields->create_category(['name' => 'Manas E-Lab']);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => project::SPACE_TYPE,
            'type' => 'select',
            'sortorder' => 1,
            'configdata' => ['options' => "call\ntraining\nproject"],
        ]);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => 'callstatus',
            'type' => 'select',
            'sortorder' => 2,
            'configdata' => ['options' => "draft\nopen", 'locked' => 1],
        ]);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => 'projectstage',
            'type' => 'select',
            'sortorder' => 3,
            'configdata' => ['options' => "stage1_idea\nstage2_validation\nstage3_mvp"],
        ]);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => 'projectneed',
            'type' => 'text',
            'sortorder' => 4,
            'configdata' => ['displaysize' => 50, 'maxlength' => 1333],
        ]);
        $fields->create_field([
            'categoryid' => $category->get('id'),
            'shortname' => 'internalnote',
            'type' => 'text',
            'sortorder' => 5,
            'configdata' => ['displaysize' => 50, 'maxlength' => 1333,
                'visibility' => course_handler::VISIBLETOTEACHERS],
        ]);

        $story = $fields->create_category(['name' => 'Story']);
        $fields->create_field([
            'categoryid' => $story->get('id'),
            'shortname' => 'projectproblem',
            'type' => 'text',
            'configdata' => ['displaysize' => 50, 'maxlength' => 1333],
        ]);

        $this->course = $generator->create_course([
            'customfield_spacetype' => 3,
            'customfield_callstatus' => 1,
            'customfield_projectstage' => 1,
        ]);

        $leadrole = $generator->create_role(['shortname' => 'projectlead', 'archetype' => 'student']);
        assign_capability(project::CAPABILITY, CAP_ALLOW, $leadrole, context_system::instance());
        $memberrole = $generator->create_role(['shortname' => 'projectmember', 'archetype' => 'student']);

        $this->lead = $generator->create_and_enrol($this->course, 'projectlead');
        $this->member = $generator->create_and_enrol($this->course, 'projectmember');
    }

    /**
     * The short names of the fields of each step, in order.
     *
     * @param  array $steps Steps as project::get_steps() returns them.
     * @return array        The short names, one list per step.
     */
    private function get_shortnames(array $steps): array {
        return array_map(fn(array $step): array => array_map(
            fn(data_controller $data): string => $data->get_field()->get('shortname'),
            $step['fields']
        ), $steps);
    }

    /**
     * The value one field holds for the course, read from the database.
     *
     * @param  string $shortname Short name of the field.
     * @return mixed             The stored value.
     */
    private function get_value(string $shortname): mixed {
        foreach (course_handler::create()->get_instance_data($this->course->id, true) as $data) {
            if ($data->get_field()->get('shortname') === $shortname) {
                return $data->get_value();
            }
        }

        return null;
    }

    /**
     * A course is a project when its space type holds the project option, by position.
     */
    public function test_is_project(): void {
        $training = $this->getDataGenerator()->create_course(['customfield_spacetype' => 2]);
        $untyped = $this->getDataGenerator()->create_course();

        $this->assertTrue(project::is_project($this->course->id));
        $this->assertFalse(project::is_project($training->id));
        $this->assertFalse(project::is_project($untyped->id));
    }

    /**
     * The lead gets one step per category, without the space type, the locked field or the hidden one.
     */
    public function test_get_steps_for_lead(): void {
        $this->setUser($this->lead);

        $steps = project::get_steps($this->course->id);

        $this->assertSame([['projectstage', 'projectneed'], ['projectproblem']], $this->get_shortnames($steps));
        $this->assertSame(['Manas E-Lab', 'Story'], array_column($steps, 'name'));
    }

    /**
     * A manager may change locked fields, so the locked field enters the form; the space type never does.
     */
    public function test_get_steps_for_manager(): void {
        $this->setAdminUser();

        $steps = project::get_steps($this->course->id);

        $this->assertSame(
            [['callstatus', 'projectstage', 'projectneed', 'internalnote'], ['projectproblem']],
            $this->get_shortnames($steps)
        );
    }

    /**
     * The lead edits an unlocked field, and the rest of the course and the locked field stay as they were.
     */
    public function test_lead_edits_unlocked_field(): void {
        $this->setUser($this->lead);
        $this->assertTrue(has_capability(project::CAPABILITY, context_course::instance($this->course->id)));
        $this->assertFalse(has_capability('moodle/course:update', context_course::instance($this->course->id)));

        $sink = $this->redirectEvents();
        project::save((object) [
            'id' => $this->course->id,
            'customfield_projectstage' => 3,
            'customfield_projectneed' => 'Looking for a mentor',
            'customfield_callstatus' => 2,
            'fullname' => 'Renamed',
        ]);
        $events = array_values(array_filter(
            $sink->get_events(),
            fn($event): bool => $event instanceof \core\event\course_updated
        ));
        $sink->close();

        $this->assertEquals(3, $this->get_value('projectstage'));
        $this->assertSame('Looking for a mentor', $this->get_value('projectneed'));
        $this->assertEquals(1, $this->get_value('callstatus'));
        $this->assertSame($this->course->fullname, get_course($this->course->id)->fullname);

        $this->assertCount(1, $events);
        $this->assertEquals($this->course->id, $events[0]->objectid);
    }

    /**
     * A member has no capability, so the menu of the course offers no link.
     */
    public function test_member_cannot_edit(): void {
        $context = context_course::instance($this->course->id);

        $this->setUser($this->member);
        $this->assertFalse(has_capability(project::CAPABILITY, $context));
        $node = navigation_node::create('Course');
        local_manaselabproject_extend_navigation_course($node, $this->course, $context);
        $this->assertFalse($node->find('local_manaselabproject_edit', navigation_node::TYPE_SETTING));

        $this->setUser($this->lead);
        $node = navigation_node::create('Course');
        local_manaselabproject_extend_navigation_course($node, $this->course, $context);
        $this->assertNotEmpty($node->find('local_manaselabproject_edit', navigation_node::TYPE_SETTING));
    }

    /**
     * The lead of a course that is not a project gets no link either.
     */
    public function test_no_link_outside_projects(): void {
        $training = $this->getDataGenerator()->create_course(['customfield_spacetype' => 2]);
        $this->getDataGenerator()->enrol_user($this->lead->id, $training->id, 'projectlead');
        $context = context_course::instance($training->id);

        $this->setUser($this->lead);
        $node = navigation_node::create('Course');
        local_manaselabproject_extend_navigation_course($node, $training, $context);

        $this->assertFalse($node->find('local_manaselabproject_edit', navigation_node::TYPE_SETTING));
    }

    /**
     * The form shows the fields of the step only, and a locked field is not one of them.
     */
    public function test_form_shows_step_fields(): void {
        $this->setUser($this->lead);
        $steps = project::get_steps($this->course->id);

        $form = new edit_project(null, ['fields' => $steps[0]['fields'], 'last' => false]);
        $mform = (function () {
            return $this->_form;
        })->call($form);

        $this->assertTrue($mform->elementExists('customfield_projectstage'));
        $this->assertTrue($mform->elementExists('customfield_projectneed'));
        $this->assertFalse($mform->elementExists('customfield_callstatus'));
        $this->assertFalse($mform->elementExists('customfield_spacetype'));
        $this->assertFalse($mform->elementExists('customfield_projectproblem'));
        $this->assertFalse($mform->elementExists('fullname'));
    }

    /**
     * The options of a select field show the strings of the plugin instead of their keys.
     */
    public function test_form_labels_options(): void {
        $this->setUser($this->lead);
        $steps = project::get_steps($this->course->id);

        $form = new edit_project(null, ['fields' => $steps[0]['fields'], 'last' => true]);
        $mform = (function () {
            return $this->_form;
        })->call($form);

        $labels = array_column($mform->getElement('customfield_projectstage')->_options, 'text');
        $this->assertSame([
            '',
            get_string('option_projectstage_stage1_idea', project::COMPONENT),
            get_string('option_projectstage_stage2_validation', project::COMPONENT),
            get_string('option_projectstage_stage3_mvp', project::COMPONENT),
        ], $labels);
    }
}
