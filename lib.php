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
 * Library functions for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declares supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function videocompare_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Creates a Video Compare activity.
 *
 * @param stdClass $data Activity data.
 * @param mod_videocompare_mod_form|null $mform Form instance.
 * @return int New instance id.
 */
function videocompare_add_instance(stdClass $data, ?mod_videocompare_mod_form $mform = null): int {
    global $DB;

    $now = time();
    $data->timecreated = $now;
    $data->timemodified = $now;
    return (int)$DB->insert_record('videocompare', $data);
}

/**
 * Updates a Video Compare activity.
 *
 * @param stdClass $data Activity data.
 * @param mod_videocompare_mod_form|null $mform Form instance.
 * @return bool
 */
function videocompare_update_instance(stdClass $data, ?mod_videocompare_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    return $DB->update_record('videocompare', $data);
}

/**
 * Deletes a Video Compare activity and its related data.
 *
 * @param int $id Activity id.
 * @return bool
 */
function videocompare_delete_instance($id): bool {
    global $DB;

    if (!$activity = $DB->get_record('videocompare', ['id' => $id])) {
        return false;
    }

    $cms = get_coursemodule_from_instance('videocompare', $id, $activity->course, false, IGNORE_MISSING);
    if ($cms) {
        $context = context_module::instance($cms->id);
        get_file_storage()->delete_area_files($context->id, 'mod_videocompare');
    }

    $DB->delete_records('videocompare_notes', ['videocompareid' => $id]);
    $DB->delete_records('videocompare_answers', ['videocompareid' => $id]);
    $DB->delete_records('videocompare_progress', ['videocompareid' => $id]);
    $DB->delete_records('videocompare_questions', ['videocompareid' => $id]);
    $DB->delete_records('videocompare_videos', ['videocompareid' => $id]);
    $DB->delete_records('videocompare', ['id' => $id]);

    return true;
}

/**
 * Serves protected uploaded videos.
 *
 * @param stdClass $course Course record.
 * @param stdClass $cm Course module record.
 * @param context $context Module context.
 * @param string $filearea File area.
 * @param array $args Path arguments.
 * @param bool $forcedownload Force download.
 * @param array $options Send file options.
 * @return bool
 */
function videocompare_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload, array $options = []): bool {
    global $DB;

    if ($context->contextlevel !== CONTEXT_MODULE || !in_array($filearea, ['intro', 'video'], true)) {
        return false;
    }

    require_login($course, true, $cm);
    require_capability('mod/videocompare:view', $context);

    if ($filearea === 'intro') {
        $itemid = (int)array_shift($args);
        if ($itemid !== 0) {
            return false;
        }
    } else {
        $itemid = (int)array_shift($args);
        if (!$DB->record_exists('videocompare_videos', ['id' => $itemid, 'videocompareid' => $cm->instance])) {
            return false;
        }
    }

    $filename = array_pop($args);
    $filepath = '/' . implode('/', $args) . '/';
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_videocompare', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, false, $options);
    return true;
}

/**
 * Returns a human-readable time code.
 *
 * @param float|int $seconds Time in seconds.
 * @return string
 */
function videocompare_format_time($seconds): string {
    $seconds = max(0, (int)round((float)$seconds));
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remaining = $seconds % 60;
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remaining);
    }
    return sprintf('%02d:%02d', $minutes, $remaining);
}

/**
 * Parses MM:SS or HH:MM:SS into seconds.
 *
 * @param string $value Time code.
 * @return int|null
 */
function videocompare_parse_time(string $value): ?int {
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    if (ctype_digit($value)) {
        return (int)$value;
    }
    $parts = array_map('trim', explode(':', $value));
    if (count($parts) < 2 || count($parts) > 3) {
        return null;
    }
    foreach ($parts as $part) {
        if ($part === '' || !ctype_digit($part)) {
            return null;
        }
    }
    if (count($parts) === 2) {
        [$minutes, $seconds] = array_map('intval', $parts);
        if ($seconds > 59) {
            return null;
        }
        return ($minutes * 60) + $seconds;
    }
    [$hours, $minutes, $seconds] = array_map('intval', $parts);
    if ($minutes > 59 || $seconds > 59) {
        return null;
    }
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}
