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
 * Comparison question editor form.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

/**
 * Teacher form for comparison questions.
 */
class question_form extends moodleform {
    /**
     * Defines fields.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;
        $videos = $this->_customdata['videos'] ?? [];
        $options = [0 => get_string('none')];
        foreach ($videos as $video) {
            $options[(int)$video->id] = format_string($video->name);
        }

        $mform->addElement('textarea', 'questiontext', get_string('questiontext', 'videocompare'), [
            'rows' => 5,
            'cols' => 80,
        ]);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');

        $mform->addElement('selectyesno', 'required', get_string('required', 'videocompare'));
        $mform->setDefault('required', 1);

        $mform->addElement('html', '<h3>' . get_string('comparemoments', 'videocompare') . '</h3>');
        $mform->addElement('select', 'videoaid', get_string('referencevideoa', 'videocompare'), $options);
        $mform->addElement('text', 'timea_text', get_string('referencetimea', 'videocompare'), ['size' => 12]);
        $mform->setType('timea_text', PARAM_TEXT);
        $mform->addHelpButton('timea_text', 'timecodehelp', 'videocompare');

        $mform->addElement('select', 'videobid', get_string('referencevideob', 'videocompare'), $options);
        $mform->addElement('text', 'timeb_text', get_string('referencetimeb', 'videocompare'), ['size' => 12]);
        $mform->setType('timeb_text', PARAM_TEXT);
        $mform->addHelpButton('timeb_text', 'timecodehelp', 'videocompare');

        $mform->addElement('hidden', 'questionid');
        $mform->setType('questionid', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Validates timecodes and references.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        foreach (['a', 'b'] as $suffix) {
            $videoid = (int)($data['video' . $suffix . 'id'] ?? 0);
            $timefield = 'time' . $suffix . '_text';
            $value = trim((string)($data[$timefield] ?? ''));
            if ($value !== '' && videocompare_parse_time($value) === null) {
                $errors[$timefield] = get_string('errortimecode', 'videocompare');
            }
            if (!$videoid && $value !== '') {
                $errors['video' . $suffix . 'id'] = get_string('required');
            }
        }

        if (!empty($data['videoaid']) && (int)$data['videoaid'] === (int)($data['videobid'] ?? 0)) {
            $errors['videobid'] = get_string('error:samemomentvideo', 'videocompare');
        }

        return $errors;
    }
}
