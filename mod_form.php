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
 * Activity form for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Main activity settings form.
 */
class mod_videocompare_mod_form extends moodleform_mod {
    /**
     * Defines the form.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('videocomparename', 'videocompare'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('html', '<h3>' . get_string('displaysettings', 'videocompare') . '</h3>');
        $mform->addElement('select', 'layout', get_string('layout', 'videocompare'), [
            'auto' => get_string('layoutauto', 'videocompare'),
            'sidebyside' => get_string('layoutsidebyside', 'videocompare'),
            'switch' => get_string('layoutswitch', 'videocompare'),
        ]);
        $mform->setDefault('layout', 'auto');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Adds custom completion rules.
     *
     * @return array
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;

        $mform->addElement(
            'text',
            'completionpercent',
            get_string('completionpercent', 'videocompare'),
            ['size' => 5]
        );
        $mform->setType('completionpercent', PARAM_INT);
        $mform->setDefault('completionpercent', 80);
        $mform->addRule('completionpercent', null, 'numeric', null, 'client');

        $mform->addElement(
            'advcheckbox',
            'completionquestions',
            get_string('completionquestions', 'videocompare'),
            get_string('completionquestions_help', 'videocompare')
        );
        $mform->setDefault('completionquestions', 1);

        return ['completionpercent', 'completionquestions'];
    }

    /**
     * Returns whether at least one custom completion rule is enabled.
     *
     * @param stdClass $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionpercent']) || !empty($data['completionquestions']);
    }

    /**
     * Validates form input.
     *
     * @param array $data Form data.
     * @param array $files Form files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (isset($data['completionpercent'])
            && ((int)$data['completionpercent'] < 1 || (int)$data['completionpercent'] > 100)) {
            $errors['completionpercent'] = get_string('errorcompletionpercent', 'videocompare');
        }
        return $errors;
    }
}
