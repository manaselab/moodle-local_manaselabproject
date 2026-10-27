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
 * Edit the profile of a project, one short step at a time.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_manaselabproject\form\edit_project;
use local_manaselabproject\project;

$courseid = required_param('courseid', PARAM_INT);
$step = optional_param('step', 0, PARAM_INT);

$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
require_capability(project::CAPABILITY, $context);

if ($course->id == SITEID || !project::is_project($course->id)) {
    throw new moodle_exception('notaproject', project::COMPONENT);
}

$courseurl = course_get_url($course);
$steps = project::get_steps($course->id);
$step = max(0, min($step, count($steps) - 1));

$PAGE->set_url(new moodle_url('/local/manaselabproject/edit.php', ['courseid' => $course->id, 'step' => $step]));
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('editproject', project::COMPONENT) . moodle_page::TITLE_SEPARATOR .
    format_string($course->fullname, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => $context]));
$PAGE->navbar->add(get_string('editproject', project::COMPONENT));

if ($steps === []) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('editproject', project::COMPONENT));
    echo $OUTPUT->notification(get_string('nofields', project::COMPONENT), 'info', false);
    echo $OUTPUT->single_button($courseurl, get_string('backtoproject', project::COMPONENT), 'get');
    echo $OUTPUT->footer();
    die();
}

$last = $step === count($steps) - 1;
$form = new edit_project($PAGE->url, ['fields' => $steps[$step]['fields'], 'last' => $last]);

if ($form->is_cancelled()) {
    redirect($courseurl);
}

if ($data = $form->get_data()) {
    $data->id = $course->id;
    project::save($data);

    if ($last) {
        redirect(
            $courseurl,
            get_string('projectsaved', project::COMPONENT),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    redirect(new moodle_url($PAGE->url, ['step' => $step + 1]));
}

$instance = (object) ['id' => $course->id];
core_course\customfield\course_handler::create()->instance_form_before_set_data($instance);
$form->set_data($instance);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editproject', project::COMPONENT));
if (count($steps) > 1) {
    echo html_writer::tag('p', get_string('stepof', project::COMPONENT, (object) [
        'step' => $step + 1,
        'total' => count($steps),
        'name' => $steps[$step]['name'],
    ]), ['class' => 'text-muted']);
}
$form->display();
echo $OUTPUT->footer();
