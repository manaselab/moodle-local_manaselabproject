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
 * English language strings for local_manaselabproject.
 *
 * The option_<shortname>_<key> strings label the options of the select fields
 * of the profile. A select field without them shows the keys the site typed.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtoproject'] = 'Back to the project';
$string['editproject'] = 'Edit my project';
$string['finishedstatuses'] = 'Statuses that finish a project';
$string['finishedstatuses_desc'] = 'Keys of the options of the course field projectstatus, separated by commas. A lead may lead a new project once the previous one holds one of them. A project without status has not finished.';
$string['leadconflict_course'] = '{$a->user} was given the lead role in {$a->course}, which already has a lead.';
$string['leadconflict_howto'] = 'A project has one lead, and a lead leads one project until it has finished. To hand over a project, first remove the lead role from the current lead and then give it to the new one. Participants: {$a->url}';
$string['leadconflict_kept'] = 'The assignment comes from an enrolment method that keeps it in sync, so it has not been removed. Fix it in the enrolment methods of the project.';
$string['leadconflict_subject'] = 'Lead role not allowed in {$a->course}';
$string['leadconflict_undone'] = 'The lead role has been removed: {$a->user} stays enrolled in the project without it.';
$string['leadconflict_user'] = '{$a->user} was given the lead role in {$a->course}, but already leads a project that has not finished: {$a->others}.';
$string['leadership'] = 'One lead per project';
$string['leadership_desc'] = 'A project has one lead at a time, and a lead cannot lead another project until the first one has finished. When the lead role is assigned against this rule, the assignment is undone and an alert is sent to whoever made it and to the users who receive the alerts.';
$string['leadrole'] = 'Lead role';
$string['leadrole_desc'] = 'Role of the lead of a project.';
$string['manaselabproject:editproject'] = 'Edit the profile of a project';
$string['manaselabproject:receiveleadalerts'] = 'Receive the alerts of the rule of one lead per project';
$string['messageprovider:leadconflict'] = 'Lead role assignments not allowed by the rule of one lead per project';
$string['nofields'] = 'There is nothing in the profile of this project that you can edit.';
$string['notaproject'] = 'This course is not a project.';
$string['option_projectsector_agrifood'] = 'Agri-food';
$string['option_projectsector_creative'] = 'Creative industries';
$string['option_projectsector_education'] = 'Education and training';
$string['option_projectsector_energy'] = 'Energy and environment';
$string['option_projectsector_fintech'] = 'Finance and fintech';
$string['option_projectsector_health'] = 'Health and wellbeing';
$string['option_projectsector_ict'] = 'ICT, software and AI';
$string['option_projectsector_industry'] = 'Industry and manufacturing';
$string['option_projectsector_logistics'] = 'Transport and logistics';
$string['option_projectsector_other'] = 'Other';
$string['option_projectsector_retail'] = 'Retail and commerce';
$string['option_projectsector_socialimpact'] = 'Social impact';
$string['option_projectsector_tourism'] = 'Tourism and heritage';
$string['option_projectstage_stage1_idea'] = 'Stage 1 · Idea';
$string['option_projectstage_stage2_validation'] = 'Stage 2 · Validation';
$string['option_projectstage_stage3_mvp'] = 'Stage 3 · Prototype / MVP';
$string['option_projectstage_stage4_traction'] = 'Stage 4 · Traction / pilot';
$string['option_projectstage_stage5_consolidation'] = 'Stage 5 · Consolidation';
$string['pluginname'] = 'Manas E-Lab project';
$string['privacy:metadata'] = 'The Manas E-Lab project plugin stores no personal data: the profile of a project is kept in the custom fields of the course.';
$string['projectsaved'] = 'Your project has been updated.';
$string['savecontinue'] = 'Save and continue';
$string['saveproject'] = 'Save';
$string['stepof'] = 'Step {$a->step} of {$a->total} · {$a->name}';
