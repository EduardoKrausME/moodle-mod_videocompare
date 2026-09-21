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
 * Restore task for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videocompare/backup/moodle2/restore_videocompare_stepslib.php');

/**
 * Video Compare restore task.
 */
class restore_videocompare_activity_task extends restore_activity_task {
    /**
     * No activity-specific restore settings are required.
     *
     * @return void
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines activity-specific restore steps.
     *
     * @return void
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_videocompare_activity_structure_step('videocompare_structure', 'videocompare.xml'));
    }

    /**
     * Defines content decoding rules.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule('VIDEOCOMPAREINDEX', '/mod/videocompare/index.php?id=$1', 'course'),
            new restore_decode_rule('VIDEOCOMPAREVIEWBYID', '/mod/videocompare/view.php?id=$1', 'course_module'),
        ];
    }

    /**
     * Defines content decoding rules that use the course id.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents(): array {
        return [
            new restore_decode_content('videocompare', ['intro'], 'videocompare'),
            new restore_decode_content('videocompare_videos', ['description'], 'videocompare_video'),
            new restore_decode_content('videocompare_questions', ['questiontext'], 'videocompare_question'),
        ];
    }
}
