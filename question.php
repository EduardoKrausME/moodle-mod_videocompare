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
 * Add/edit one comparison question.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_videocompare\form\question_form;

$id = required_param('id', PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('videocompare', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videocompare:managecontent', $context);

$question = null;
if ($questionid) {
    $question = $DB->get_record('videocompare_questions', [
        'id' => $questionid,
        'videocompareid' => $activity->id,
    ], '*', MUST_EXIST);
}

$videos = $DB->get_records(
    'videocompare_videos',
    ['videocompareid' => $activity->id],
    'sortorder ASC, id ASC'
);

$PAGE->set_url('/mod/videocompare/question.php', ['id' => $cm->id, 'questionid' => $questionid]);
$PAGE->set_title(get_string($question ? 'editquestion' : 'addquestion', 'videocompare'));
$PAGE->set_heading(format_string($course->fullname));

$form = new question_form(null, ['videos' => $videos]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]));
}

if ($data = $form->get_data()) {
    $now = time();
    $record = (object)[
        'videocompareid' => $activity->id,
        'questiontext' => $data->questiontext,
        'questionformat' => FORMAT_HTML,
        'required' => (int)$data->required,
        'videoaid' => !empty($data->videoaid) ? (int)$data->videoaid : null,
        'timea' => !empty($data->videoaid) ? videocompare_parse_time((string)$data->timea_text) : null,
        'videobid' => !empty($data->videobid) ? (int)$data->videobid : null,
        'timeb' => !empty($data->videobid) ? videocompare_parse_time((string)$data->timeb_text) : null,
        'timemodified' => $now,
    ];

    if ($question) {
        $record->id = $question->id;
        $record->sortorder = $question->sortorder;
        $DB->update_record('videocompare_questions', $record);
    } else {
        $maxsort = (int)$DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) FROM {videocompare_questions} WHERE videocompareid = ?',
            [$activity->id]
        );
        $record->sortorder = $maxsort + 1;
        $record->timecreated = $now;
        $DB->insert_record('videocompare_questions', $record);
    }

    redirect(new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]));
}

$form->set_data([
    'questionid' => $questionid,
    'questiontext' => $question ? $question->questiontext : '',
    'required' => $question ? $question->required : 1,
    'videoaid' => $question ? $question->videoaid : 0,
    'timea_text' => ($question && $question->timea !== null) ? videocompare_format_time($question->timea) : '',
    'videobid' => $question ? $question->videobid : 0,
    'timeb_text' => ($question && $question->timeb !== null) ? videocompare_format_time($question->timeb) : '',
]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($question ? 'editquestion' : 'addquestion', 'videocompare'));
$form->display();
echo $OUTPUT->footer();
