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
 * AJAX progress endpoint.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use mod_videocompare\progress_manager;

/**
 * Updates per-video progress.
 */
class update_progress extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'videoid' => new external_value(PARAM_INT, 'Video id'),
            'position' => new external_value(PARAM_FLOAT, 'Current playback position'),
            'duration' => new external_value(PARAM_FLOAT, 'Video duration'),
            'segmentsjson' => new external_value(PARAM_RAW, 'JSON array of watched intervals'),
        ]);
    }

    /**
     * Executes update.
     *
     * @param int $cmid Course module id.
     * @param int $videoid Video id.
     * @param float $position Position.
     * @param float $duration Duration.
     * @param string $segmentsjson Segments JSON.
     * @return array
     */
    public static function execute(
        int    $cmid,
        int    $videoid,
        float  $position,
        float  $duration,
        string $segmentsjson
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'videoid' => $videoid,
            'position' => $position,
            'duration' => $duration,
            'segmentsjson' => $segmentsjson,
        ]);

        $cm = get_coursemodule_from_id('videocompare', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videocompare:view', $context);

        $activity = $DB->get_record('videocompare', ['id' => $cm->instance], '*', MUST_EXIST);
        $segments = json_decode($params['segmentsjson'], true);
        if (!is_array($segments)) {
            $segments = [];
        }

        $record = progress_manager::update(
            (int)$activity->id,
            (int)$params['videoid'],
            (int)$USER->id,
            (float)$params['position'],
            (float)$params['duration'],
            $segments
        );

        $overall = progress_manager::overall_percent((int)$activity->id, (int)$USER->id);

        $completion = new \completion_info(get_course($cm->course));
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
        }

        return [
            'percent' => (float)$record->percent,
            'overall' => $overall,
            'lastposition' => (float)$record->lastposition,
        ];
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'percent' => new external_value(PARAM_FLOAT, 'Video watched percentage'),
            'overall' => new external_value(PARAM_FLOAT, 'Overall activity percentage'),
            'lastposition' => new external_value(PARAM_FLOAT, 'Last position'),
        ]);
    }
}
