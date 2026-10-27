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
 * Form of one step of the profile of a project.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_manaselabproject\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use context_system;
use core_customfield\data_controller;
use local_manaselabproject\project;
use moodleform;
use MoodleQuickForm_select;

/**
 * The fields of one step of the profile, with a single button.
 *
 * Each field adds its own element through its data controller, as the core
 * handler does in instance_form_definition(), but without the header of the
 * category: the page shows the step as its title, so the form stays short.
 *
 * A select field offers the neutral keys the site typed. When the plugin has
 * the string option_<shortname>_<key>, the option shows that string instead.
 *
 * Custom data:
 * - fields: data_controller[] of the step.
 * - last: whether this is the last step, which changes the label of the button.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_project extends moodleform {
    /**
     * Defines the fields of the step and the button.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        /** @var data_controller $data */
        foreach ($this->_customdata['fields'] as $data) {
            $data->instance_form_definition($mform);
            $this->label_options($data);
            $this->add_description($data);
        }

        $label = empty($this->_customdata['last']) ? 'savecontinue' : 'saveproject';
        $this->add_action_buttons(true, get_string($label, project::COMPONENT));
    }

    /**
     * Lets each field adjust its element once the data is in place.
     *
     * @return void
     */
    public function definition_after_data(): void {
        foreach ($this->_customdata['fields'] as $data) {
            $data->instance_form_definition_after_data($this->_form);
        }
    }

    /**
     * Validates the fields of the step.
     *
     * Only the fields of the step are asked: the handler would also validate the
     * fields of the other steps, which this form has not sent.
     *
     * @param  array $data  Submitted data.
     * @param  array $files Submitted files.
     * @return array        Errors by element name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        foreach ($this->_customdata['fields'] as $controller) {
            $errors += $controller->instance_form_validation($data, $files);
        }

        return $errors;
    }

    /**
     * Shows the translated label of each option of a select field, when the plugin has one.
     *
     * @param  data_controller $data The field with its data.
     * @return void
     */
    private function label_options(data_controller $data): void {
        $field = $data->get_field();
        $element = $this->_form->getElement($data->get_form_element_name());
        if ($field->get('type') !== 'select' || !$element instanceof MoodleQuickForm_select) {
            return;
        }

        $manager = get_string_manager();
        $options = [];
        foreach ($element->_options as $option) {
            $options[$option['attr']['value']] = $option['text'];
        }
        foreach (project::get_option_keys($field) as $value => $key) {
            $identifier = 'option_' . $field->get('shortname') . '_' . $key;
            if (preg_match('/^[a-z0-9_]+$/', $identifier) && $manager->string_exists($identifier, project::COMPONENT)) {
                $options[$value] = get_string($identifier, project::COMPONENT);
            }
        }

        $element->removeOptions();
        $element->loadArray($options);
    }

    /**
     * Adds the description of a field under its element, as the core handler does.
     *
     * @param  data_controller $data The field with its data.
     * @return void
     */
    private function add_description(data_controller $data): void {
        $field = $data->get_field()->to_record();
        if (!strlen((string) $field->description)) {
            return;
        }

        $context = context_system::instance();
        $description = file_rewrite_pluginfile_urls(
            $field->description,
            'pluginfile.php',
            $context->id,
            'core_customfield',
            'description',
            $field->id
        );
        $this->_form->addElement(
            'static',
            'customfield_' . $field->shortname . '_static',
            '',
            format_text($description, $field->descriptionformat, ['context' => $context])
        );
    }
}
