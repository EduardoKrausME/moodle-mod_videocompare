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
 * Restore structure for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores Video Compare activities and their child records.
 */
class restore_videocompare_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the XML paths restored by this step.
     *
     * @return restore_path_element[]
     */
    protected function define_structure(): array {
        $paths = [
            new restore_path_element('videocompare', '/activity/videocompare'),
            new restore_path_element('videocompare_video', '/activity/videocompare/videos/video'),
            new restore_path_element('videocompare_question', '/activity/videocompare/questions/question'),
        ];

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videocompare_progress', '/activity/videocompare/progresses/progress');
            $paths[] = new restore_path_element('videocompare_answer', '/activity/videocompare/answers/answer');
            $paths[] = new restore_path_element('videocompare_note', '/activity/videocompare/notes/note');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the activity record.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newid = $DB->insert_record('videocompare', $data);
        $this->apply_activity_instance($newid);
        $this->set_mapping('videocompare', $oldid, $newid);
    }

    /**
     * Restores a video.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare_video($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->videocompareid = $this->get_new_parentid('videocompare');
        $newid = $DB->insert_record('videocompare_videos', $data);
        $this->set_mapping('videocompare_video', $oldid, $newid, true);
    }

    /**
     * Restores a teacher comparison question.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare_question($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->videocompareid = $this->get_new_parentid('videocompare');
        if (!empty($data->videoaid)) {
            $data->videoaid = $this->get_mappingid('videocompare_video', $data->videoaid, null);
        }
        if (!empty($data->videobid)) {
            $data->videobid = $this->get_mappingid('videocompare_video', $data->videobid, null);
        }
        $newid = $DB->insert_record('videocompare_questions', $data);
        $this->set_mapping('videocompare_question', $oldid, $newid);
    }

    /**
     * Restores per-video viewing progress.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare_progress($data): void {
        global $DB;

        $data = (object)$data;
        $data->videocompareid = $this->get_new_parentid('videocompare');
        $data->videoid = $this->get_mappingid('videocompare_video', $data->videoid, 0);
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        if ($data->videoid && $data->userid) {
            $DB->insert_record('videocompare_progress', $data);
        }
    }

    /**
     * Restores a student answer.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare_answer($data): void {
        global $DB;

        $data = (object)$data;
        $data->videocompareid = $this->get_new_parentid('videocompare');
        $data->questionid = $this->get_mappingid('videocompare_question', $data->questionid, 0);
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        if ($data->questionid && $data->userid) {
            $DB->insert_record('videocompare_answers', $data);
        }
    }

    /**
     * Restores a student's timestamp comparison.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_videocompare_note($data): void {
        global $DB;

        $data = (object)$data;
        $data->videocompareid = $this->get_new_parentid('videocompare');
        $data->videoaid = $this->get_mappingid('videocompare_video', $data->videoaid, 0);
        $data->videobid = $this->get_mappingid('videocompare_video', $data->videobid, 0);
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        if ($data->videoaid && $data->videobid && $data->userid) {
            $DB->insert_record('videocompare_notes', $data);
        }
    }

    /**
     * Restores uploaded media files after record mappings exist.
     *
     * @return void
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_videocompare', 'intro', null);
        $this->add_related_files('mod_videocompare', 'video', 'videocompare_video');
    }
}
