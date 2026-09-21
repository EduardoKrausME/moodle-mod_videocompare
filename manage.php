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
 * Teacher management page for videos and questions.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$itemid = optional_param('itemid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('videocompare', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videocompare:managecontent', $context);

$PAGE->set_url('/mod/videocompare/manage.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($activity->name));
$PAGE->set_heading(format_string($course->fullname));

if ($action !== '' && $itemid) {
    require_sesskey();

    if ($action === 'deletevideo') {
        $video = $DB->get_record('videocompare_videos', [
            'id' => $itemid,
            'videocompareid' => $activity->id,
        ], '*', MUST_EXIST);

        get_file_storage()->delete_area_files($context->id, 'mod_videocompare', 'video', $video->id);
        $DB->delete_records('videocompare_notes', ['videoaid' => $video->id]);
        $DB->delete_records('videocompare_notes', ['videobid' => $video->id]);
        $DB->delete_records('videocompare_progress', ['videoid' => $video->id]);

        $questions = $DB->get_records_select(
            'videocompare_questions',
            'videocompareid = :activityid AND (videoaid = :videoa OR videobid = :videob)',
            ['activityid' => $activity->id, 'videoa' => $video->id, 'videob' => $video->id]
        );
        foreach ($questions as $question) {
            if ((int)$question->videoaid === (int)$video->id) {
                $question->videoaid = null;
                $question->timea = null;
            }
            if ((int)$question->videobid === (int)$video->id) {
                $question->videobid = null;
                $question->timeb = null;
            }
            $DB->update_record('videocompare_questions', $question);
        }
        $DB->delete_records('videocompare_videos', ['id' => $video->id]);
    } else if ($action === 'deletequestion') {
        $question = $DB->get_record('videocompare_questions', [
            'id' => $itemid,
            'videocompareid' => $activity->id,
        ], '*', MUST_EXIST);
        $DB->delete_records('videocompare_answers', ['questionid' => $question->id]);
        $DB->delete_records('videocompare_questions', ['id' => $question->id]);
    } else if (in_array($action, ['videoup', 'videodown'], true)) {
        $records = array_values($DB->get_records(
            'videocompare_videos',
            ['videocompareid' => $activity->id],
            'sortorder ASC, id ASC'
        ));
        videocompare_reorder_records($records, $itemid, $action === 'videoup' ? -1 : 1, 'videocompare_videos');
    } else if (in_array($action, ['questionup', 'questiondown'], true)) {
        $records = array_values($DB->get_records(
            'videocompare_questions',
            ['videocompareid' => $activity->id],
            'sortorder ASC, id ASC'
        ));
        videocompare_reorder_records($records, $itemid, $action === 'questionup' ? -1 : 1, 'videocompare_questions');
    }

    redirect(new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]));
}

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

foreach ($videos as $index => $video) {
    $video->source = get_string('source' . ($video->sourcetype === 'url' ? 'urloption' : $video->sourcetype), 'videocompare');
    $video->editurl = (new moodle_url('/mod/videocompare/video.php', [
        'id' => $cm->id,
        'videoid' => $video->id,
    ]))->out(false);
    $video->deleteurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'deletevideo',
        'itemid' => $video->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $video->upurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'videoup',
        'itemid' => $video->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $video->downurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'videodown',
        'itemid' => $video->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $video->canup = $index > 0;
    $video->candown = $index < count($videos) - 1;
}

foreach ($questions as $index => $question) {
    $question->questionhtml = format_text($question->questiontext, $question->questionformat, ['context' => $context]);
    $question->requiredlabel = $question->required
        ? get_string('required', 'videocompare') : get_string('optional', 'videocompare');
    $question->editurl = (new moodle_url('/mod/videocompare/question.php', [
        'id' => $cm->id,
        'questionid' => $question->id,
    ]))->out(false);
    $question->deleteurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'deletequestion',
        'itemid' => $question->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $question->upurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'questionup',
        'itemid' => $question->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $question->downurl = (new moodle_url('/mod/videocompare/manage.php', [
        'id' => $cm->id,
        'action' => 'questiondown',
        'itemid' => $question->id,
        'sesskey' => sesskey(),
    ]))->out(false);
    $question->canup = $index > 0;
    $question->candown = $index < count($questions) - 1;
}

$data = [
    'name' => format_string($activity->name),
    'needsvideos' => count($videos) < 2,
    'videos' => $videos,
    'hasvideos' => !empty($videos),
    'questions' => $questions,
    'hasquestions' => !empty($questions),
    'addvideourl' => (new moodle_url('/mod/videocompare/video.php', ['id' => $cm->id]))->out(false),
    'addquestionurl' => (new moodle_url('/mod/videocompare/question.php', ['id' => $cm->id]))->out(false),
    'viewurl' => (new moodle_url('/mod/videocompare/view.php', ['id' => $cm->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_videocompare/manage', $data);
echo $OUTPUT->footer();

/**
 * Moves a record one position and normalises all sort orders.
 *
 * @param array $records Ordered records.
 * @param int $itemid Record id.
 * @param int $direction -1 or 1.
 * @param string $table Table name.
 * @return void
 */
function videocompare_reorder_records(array $records, int $itemid, int $direction, string $table): void {
    global $DB;

    $current = null;
    foreach ($records as $index => $record) {
        if ((int)$record->id === $itemid) {
            $current = $index;
            break;
        }
    }
    if ($current === null) {
        return;
    }
    $target = $current + $direction;
    if ($target < 0 || $target >= count($records)) {
        return;
    }

    [$records[$current], $records[$target]] = [$records[$target], $records[$current]];
    foreach ($records as $index => $record) {
        $DB->set_field($table, 'sortorder', $index + 1, ['id' => $record->id]);
    }
}
