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
 * Video editor form.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare\form;

use mod_videocompare\video_helper;
use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/formslib.php");

/**
 * Form used by teachers to add or edit one video.
 */
class video_form extends moodleform {
    /**
     * Defines fields.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('videoname', 'videocompare'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'videocompare'), [
            'rows' => 4,
            'cols' => 70,
        ]);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('select', 'sourcetype', get_string('sourcetype', 'videocompare'), [
            'url' => get_string('sourceurloption', 'videocompare'),
            'youtube' => get_string('sourceyoutube', 'videocompare'),
            'vimeo' => get_string('sourcevimeo', 'videocompare'),
            'upload' => get_string('sourceuploadoption', 'videocompare'),
        ]);
        $mform->setDefault('sourcetype', 'url');

        $mform->addElement('text', 'sourceurl', get_string('sourceurl', 'videocompare'), ['size' => 80]);
        $mform->setType('sourceurl', PARAM_URL);
        $mform->addHelpButton('sourceurl', 'sourceurl', 'videocompare');
        $mform->hideIf('sourceurl', 'sourcetype', 'eq', 'upload');

        $options = [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['video'],
            'return_types' => FILE_INTERNAL,
        ];
        $mform->addElement('filemanager', 'videofile', get_string('videofile', 'videocompare'), null, $options);
        $mform->hideIf('videofile', 'sourcetype', 'neq', 'upload');

        $mform->addElement('hidden', 'videoid');
        $mform->setType('videoid', PARAM_INT);

        $this->add_action_buttons();
    }

    /**
     * Validates source-specific input.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $type = (string)($data['sourcetype'] ?? '');

        if ($type !== 'upload' && trim((string)($data['sourceurl'] ?? '')) === '') {
            $errors['sourceurl'] = get_string('errorurlrequired', 'videocompare');
        } else if ($type === 'youtube' && !video_helper::youtube_id((string)$data['sourceurl'])) {
            $errors['sourceurl'] = get_string('errorinvalidyoutube', 'videocompare');
        } else if ($type === 'vimeo' && !video_helper::vimeo_id((string)$data['sourceurl'])) {
            $errors['sourceurl'] = get_string('errorinvalidvimeo', 'videocompare');
        }

        if ($type === 'upload') {
            $draftid = (int)($data['videofile'] ?? 0);
            $usercontext = \context_user::instance($GLOBALS['USER']->id);
            $filesindraft = get_file_storage()->get_area_files(
                $usercontext->id,
                'user',
                'draft',
                $draftid,
                'id',
                false
            );
            if (!$filesindraft && empty($data['videoid'])) {
                $errors['videofile'] = get_string('erroruploadrequired', 'videocompare');
            }
        }

        return $errors;
    }
}
