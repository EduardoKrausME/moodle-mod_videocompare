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
 * Reports for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_videocompare\progress_manager;

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('videocompare', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videocompare:viewreports', $context);

$PAGE->set_url('/mod/videocompare/report.php', ['id' => $cm->id, 'userid' => $userid ?: null]);
$PAGE->set_title(get_string('reports', 'videocompare'));
$PAGE->set_heading(format_string($course->fullname));

$videos = array_values($DB->get_records(
    'videocompare_videos',
    ['videocompareid' => $activity->id],
    'sortorder ASC, id ASC'
));
$questions = array_values($DB->get_records(
    'videocompare_questions',
    ['videocompareid' => $activity->id],
    'sortorder ASC, id ASC'
));

if ($userid) {
    $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], '*', MUST_EXIST);
    if (!is_enrolled($context, $user, 'mod/videocompare:view', true)) {
        throw new required_capability_exception($context, 'mod/videocompare:view', 'nopermissions', '');
    }

    $progress = progress_manager::get_indexed_for_user((int)$activity->id, $userid);
    $videodata = [];
    foreach ($videos as $video) {
        $record = $progress[$video->id] ?? null;
        $videodata[] = [
            'name' => format_string($video->name),
            'percent' => $record ? round((float)$record->percent, 1) : 0,
            'watched' => $record ? videocompare_format_time($record->watchedseconds) : '00:00',
            'lastposition' => $record ? videocompare_format_time($record->lastposition) : '00:00',
        ];
    }

    $answers = $DB->get_records('videocompare_answers', [
        'videocompareid' => $activity->id,
        'userid' => $userid,
    ]);
    $answerindex = [];
    foreach ($answers as $answer) {
        $answerindex[(int)$answer->questionid] = $answer;
    }
    $answerdata = [];
    foreach ($questions as $question) {
        $answer = $answerindex[$question->id] ?? null;
        $answerdata[] = [
            'questionhtml' => format_text($question->questiontext, $question->questionformat, ['context' => $context]),
            'required' => !empty($question->required),
            'hasanswer' => $answer && trim((string)$answer->answer) !== '',
            'answer' => $answer ? $answer->answer : '',
        ];
    }

    $videoindex = [];
    foreach ($videos as $video) {
        $videoindex[(int)$video->id] = $video;
    }
    $notedata = [];
    $notes = $DB->get_records('videocompare_notes', [
        'videocompareid' => $activity->id,
        'userid' => $userid,
    ], 'timecreated ASC');
    foreach ($notes as $note) {
        if (!isset($videoindex[(int)$note->videoaid], $videoindex[(int)$note->videobid])) {
            continue;
        }
        $notedata[] = [
            'videoaname' => format_string($videoindex[(int)$note->videoaid]->name),
            'timea' => videocompare_format_time($note->timea),
            'videobname' => format_string($videoindex[(int)$note->videobid]->name),
            'timeb' => videocompare_format_time($note->timeb),
            'note' => $note->note,
        ];
    }

    $status = progress_manager::required_question_status((int)$activity->id, $userid);
    $data = [
        'detail' => true,
        'name' => fullname($user),
        'overall' => round(progress_manager::overall_percent((int)$activity->id, $userid), 1),
        'videos' => $videodata,
        'answers' => $answerdata,
        'hasanswers' => !empty($answerdata),
        'notes' => $notedata,
        'hasnotes' => !empty($notedata),
        'requiredanswered' => $status['answered'],
        'requiredtotal' => $status['required'],
        'backurl' => (new moodle_url('/mod/videocompare/report.php', ['id' => $cm->id]))->out(false),
    ];
} else {
    $users = get_enrolled_users(
        $context,
        'mod/videocompare:submit',
        0,
        'u.id,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email',
        "u.lastname,u.firstname"
    );
    $rows = [];
    foreach ($users as $user) {
        $progress = progress_manager::get_indexed_for_user((int)$activity->id, (int)$user->id);
        $pervideo = [];
        foreach ($videos as $video) {
            $pervideo[] = [
                'name' => format_string($video->name),
                'percent' => isset($progress[$video->id]) ? round((float)$progress[$video->id]->percent, 1) : 0,
            ];
        }
        $status = progress_manager::required_question_status((int)$activity->id, (int)$user->id);
        $rows[] = [
            'name' => fullname($user),
            'overall' => round(progress_manager::overall_percent((int)$activity->id, (int)$user->id), 1),
            'pervideo' => $pervideo,
            'requiredanswered' => $status['answered'],
            'requiredtotal' => $status['required'],
            'detailsurl' => (new moodle_url('/mod/videocompare/report.php', [
                'id' => $cm->id,
                'userid' => $user->id,
            ]))->out(false),
        ];
    }

    $data = [
        'detail' => false,
        'name' => format_string($activity->name),
        'rows' => $rows,
        'hasrows' => !empty($rows),
        'videos' => array_map(static function ($video): array {
            return ['name' => format_string($video->name)];
        }, $videos),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videocompare/report', $data);
echo $OUTPUT->footer();
