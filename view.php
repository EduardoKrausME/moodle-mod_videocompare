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
 * Main Video Compare activity page.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_videocompare\event\course_module_viewed;
use mod_videocompare\progress_manager;
use mod_videocompare\video_helper;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('videocompare', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videocompare:view', $context);

$PAGE->set_url('/mod/videocompare/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->add_body_class('mod-videocompare-page');

$cansubmit = has_capability('mod/videocompare:submit', $context);
$canmanage = has_capability('mod/videocompare:managecontent', $context);
$canreports = has_capability('mod/videocompare:viewreports', $context);

$questions = $DB->get_records(
    'videocompare_questions',
    ['videocompareid' => $activity->id],
    'sortorder ASC, id ASC'
);

if ($cansubmit && optional_param('saveanswers', 0, PARAM_BOOL)) {
    require_sesskey();
    foreach ($questions as $question) {
        $answertext = trim(optional_param('answer_' . $question->id, '', PARAM_RAW));
        $existing = $DB->get_record('videocompare_answers', [
            'questionid' => $question->id,
            'userid' => $USER->id,
        ]);
        if ($answertext === '') {
            if ($existing) {
                $DB->delete_records('videocompare_answers', ['id' => $existing->id]);
            }
            continue;
        }

        $now = time();
        if ($existing) {
            $existing->answer = $answertext;
            $existing->answerformat = FORMAT_PLAIN;
            $existing->timemodified = $now;
            $DB->update_record('videocompare_answers', $existing);
        } else {
            $DB->insert_record('videocompare_answers', (object)[
                'videocompareid' => $activity->id,
                'questionid' => $question->id,
                'userid' => $USER->id,
                'answer' => $answertext,
                'answerformat' => FORMAT_PLAIN,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }
    }

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
    }

    redirect(
        new moodle_url('/mod/videocompare/view.php', ['id' => $cm->id, 'saved' => 1]),
        get_string('answerssaved', 'videocompare'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$videosrecords = $DB->get_records(
    'videocompare_videos',
    ['videocompareid' => $activity->id],
    'sortorder ASC, id ASC'
);

$progressindex = progress_manager::get_indexed_for_user((int)$activity->id, (int)$USER->id);
$videos = [];
$videooptions = [];
$first = true;
foreach ($videosrecords as $video) {
    $data = video_helper::build($video, $context, $progressindex[$video->id] ?? null);
    $data['first'] = $first;
    $first = false;
    $videos[] = $data;
    $videooptions[] = [
        'id' => (int)$video->id,
        'name' => format_string($video->name),
    ];
}

$answers = $DB->get_records('videocompare_answers', [
    'videocompareid' => $activity->id,
    'userid' => $USER->id,
]);
$answersbyquestion = [];
foreach ($answers as $answer) {
    $answersbyquestion[(int)$answer->questionid] = $answer;
}

$videoarray = [];
foreach ($videosrecords as $video) {
    $videoarray[(int)$video->id] = $video;
}

$questiondata = [];
foreach ($questions as $question) {
    $answer = $answersbyquestion[$question->id] ?? null;
    $item = [
        'id' => (int)$question->id,
        'questionhtml' => format_text($question->questiontext, $question->questionformat, ['context' => $context]),
        'required' => !empty($question->required),
        'fieldname' => 'answer_' . $question->id,
        'answer' => $answer ? $answer->answer : '',
        'hasreferencea' => !empty($question->videoaid) && isset($videoarray[(int)$question->videoaid]),
        'hasreferenceb' => !empty($question->videobid) && isset($videoarray[(int)$question->videobid]),
    ];
    if ($item['hasreferencea']) {
        $item['referenceaname'] = format_string($videoarray[(int)$question->videoaid]->name);
        $item['referenceatime'] = videocompare_format_time((int)$question->timea);
        $item['referenceavideoid'] = (int)$question->videoaid;
        $item['referenceaseconds'] = (int)$question->timea;
    }
    if ($item['hasreferenceb']) {
        $item['referencebname'] = format_string($videoarray[(int)$question->videobid]->name);
        $item['referencebtime'] = videocompare_format_time((int)$question->timeb);
        $item['referencebvideoid'] = (int)$question->videobid;
        $item['referencebseconds'] = (int)$question->timeb;
    }
    $questiondata[] = $item;
}

$notesrecords = $DB->get_records('videocompare_notes', [
    'videocompareid' => $activity->id,
    'userid' => $USER->id,
], 'timecreated DESC');

$notes = [];
foreach ($notesrecords as $note) {
    if (!isset($videoarray[(int)$note->videoaid], $videoarray[(int)$note->videobid])) {
        continue;
    }
    $notes[] = [
        'videoaid' => (int)$note->videoaid,
        'videoaname' => format_string($videoarray[(int)$note->videoaid]->name),
        'timea' => (float)$note->timea,
        'timeaformatted' => videocompare_format_time($note->timea),
        'videobid' => (int)$note->videobid,
        'videobname' => format_string($videoarray[(int)$note->videobid]->name),
        'timeb' => (float)$note->timeb,
        'timebformatted' => videocompare_format_time($note->timeb),
        'note' => $note->note,
    ];
}

$overall = progress_manager::overall_percent((int)$activity->id, (int)$USER->id);
$status = progress_manager::required_question_status((int)$activity->id, (int)$USER->id);

$templatedata = [
    'cmid' => (int)$cm->id,
    'name' => format_string($activity->name),
    'intro' => format_module_intro('videocompare', $activity, $cm->id),
    'configured' => count($videos) >= 2,
    'needsconfiguration' => count($videos) < 2,
    'canmanage' => $canmanage,
    'canreports' => $canreports,
    'cansubmit' => $cansubmit,
    'manageurl' => (new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]))->out(false),
    'reporturl' => (new moodle_url('/mod/videocompare/report.php', ['id' => $cm->id]))->out(false),
    'layoutclass' => 'videocompare-layout-' . $activity->layout,
    'videos' => $videos,
    'videooptions' => $videooptions,
    'questions' => $questiondata,
    'hasquestions' => !empty($questiondata),
    'notes' => $notes,
    'hasnotes' => !empty($notes),
    'overall' => round($overall, 1),
    'requiredtotal' => $status['required'],
    'requiredanswered' => $status['answered'],
    'questionscomplete' => $status['required'] === 0 || $status['answered'] >= $status['required'],
    'sesskey' => sesskey(),
];

$PAGE->requires->js_call_amd('mod_videocompare/compare', 'init', [[
    'cmid' => (int)$cm->id,
    'layout' => $activity->layout,
    'cansubmit' => $cansubmit,
]]);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$event = course_module_viewed::create([
    'objectid' => $activity->id,
    'context' => $context,
]);
$event->add_record_snapshot('videocompare', $activity);
$event->trigger();

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videocompare/view', $templatedata);
echo $OUTPUT->footer();
