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
 * Backup structure for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the complete Video Compare backup tree.
 */
class backup_videocompare_activity_structure_step extends backup_activity_structure_step {
    /**
     * Builds the activity backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $activity = new backup_nested_element('videocompare', ['id'], [
            'name', 'intro', 'introformat', 'layout', 'completionpercent', 'completionquestions',
            'timecreated', 'timemodified',
        ]);

        $videos = new backup_nested_element('videos');
        $video = new backup_nested_element('video', ['id'], [
            'name', 'description', 'descriptionformat', 'sourcetype', 'sourceurl', 'sortorder',
            'timecreated', 'timemodified',
        ]);

        $questions = new backup_nested_element('questions');
        $question = new backup_nested_element('question', ['id'], [
            'questiontext', 'questionformat', 'required', 'videoaid', 'timea', 'videobid', 'timeb',
            'sortorder', 'timecreated', 'timemodified',
        ]);

        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'videoid', 'userid', 'duration', 'lastposition', 'watchedseconds', 'percent', 'segments',
            'completed', 'timecreated', 'timemodified',
        ]);

        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', ['id'], [
            'questionid', 'userid', 'answer', 'answerformat', 'timecreated', 'timemodified',
        ]);

        $notes = new backup_nested_element('notes');
        $note = new backup_nested_element('note', ['id'], [
            'userid', 'videoaid', 'timea', 'videobid', 'timeb', 'note', 'timecreated', 'timemodified',
        ]);

        $activity->add_child($videos);
        $videos->add_child($video);
        $activity->add_child($questions);
        $questions->add_child($question);
        $activity->add_child($progresses);
        $progresses->add_child($progress);
        $activity->add_child($answers);
        $answers->add_child($answer);
        $activity->add_child($notes);
        $notes->add_child($note);

        $activity->set_source_table('videocompare', ['id' => backup::VAR_ACTIVITYID]);
        $video->set_source_table('videocompare_videos', ['videocompareid' => backup::VAR_PARENTID]);
        $question->set_source_table('videocompare_questions', ['videocompareid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $progress->set_source_table('videocompare_progress', ['videocompareid' => backup::VAR_PARENTID]);
            $answer->set_source_table('videocompare_answers', ['videocompareid' => backup::VAR_PARENTID]);
            $note->set_source_table('videocompare_notes', ['videocompareid' => backup::VAR_PARENTID]);
        }

        $progress->annotate_ids('user', 'userid');
        $answer->annotate_ids('user', 'userid');
        $note->annotate_ids('user', 'userid');
        $activity->annotate_files('mod_videocompare', 'intro', null);
        $video->annotate_files('mod_videocompare', 'video', 'id');

        return $this->prepare_activity_structure($activity);
    }
}
