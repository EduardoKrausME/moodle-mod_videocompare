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
 * Add/edit one video.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use mod_videocompare\form\video_form;

$id = required_param('id', PARAM_INT);
$videoid = optional_param('videoid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('videocompare', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videocompare:managecontent', $context);

$video = null;
if ($videoid) {
    $video = $DB->get_record('videocompare_videos', [
        'id' => $videoid,
        'videocompareid' => $activity->id,
    ], '*', MUST_EXIST);
}

$PAGE->set_url('/mod/videocompare/video.php', ['id' => $cm->id, 'videoid' => $videoid]);
$PAGE->set_title(get_string($video ? 'editvideo' : 'addvideo', 'videocompare'));
$PAGE->set_heading(format_string($course->fullname));

$form = new video_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]));
}

if ($data = $form->get_data()) {
    $now = time();
    $record = (object)[
        'videocompareid' => $activity->id,
        'name' => $data->name,
        'description' => $data->description,
        'descriptionformat' => FORMAT_HTML,
        'sourcetype' => $data->sourcetype,
        'sourceurl' => $data->sourcetype === 'upload' ? '' : $data->sourceurl,
        'timemodified' => $now,
    ];

    if ($video) {
        $record->id = $video->id;
        $record->sortorder = $video->sortorder;
        $DB->update_record('videocompare_videos', $record);
        $newid = $video->id;
    } else {
        $maxsort = (int)$DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) FROM {videocompare_videos} WHERE videocompareid = ?',
            [$activity->id]
        );
        $record->sortorder = $maxsort + 1;
        $record->timecreated = $now;
        $newid = $DB->insert_record('videocompare_videos', $record);
    }

    if ($data->sourcetype === 'upload') {
        file_save_draft_area_files(
            $data->videofile,
            $context->id,
            'mod_videocompare',
            'video',
            $newid,
            ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video']]
        );
    } else {
        get_file_storage()->delete_area_files($context->id, 'mod_videocompare', 'video', $newid);
    }

    redirect(new moodle_url('/mod/videocompare/manage.php', ['id' => $cm->id]));
}

$defaults = [
    'videoid' => $videoid,
    'name' => $video ? $video->name : '',
    'description' => $video ? $video->description : '',
    'sourcetype' => $video ? $video->sourcetype : 'url',
    'sourceurl' => $video ? $video->sourceurl : '',
];

$draftid = file_get_submitted_draft_itemid('videofile');
file_prepare_draft_area(
    $draftid,
    $context->id,
    'mod_videocompare',
    'video',
    $videoid,
    ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['video']]
);
$defaults['videofile'] = $draftid;
$form->set_data($defaults);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($video ? 'editvideo' : 'addvideo', 'videocompare'));
$form->display();
echo $OUTPUT->footer();
