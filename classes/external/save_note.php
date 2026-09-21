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
 * AJAX timestamp comparison endpoint.
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

/**
 * Saves a comparison between timestamped moments in two videos.
 */
class save_note extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'videoaid' => new external_value(PARAM_INT, 'Video A id'),
            'timea' => new external_value(PARAM_FLOAT, 'Time in video A'),
            'videobid' => new external_value(PARAM_INT, 'Video B id'),
            'timeb' => new external_value(PARAM_FLOAT, 'Time in video B'),
            'note' => new external_value(PARAM_TEXT, 'Comparison text'),
        ]);
    }

    /**
     * Saves the comparison.
     *
     * @param int $cmid Course module id.
     * @param int $videoaid Video A.
     * @param float $timea Time A.
     * @param int $videobid Video B.
     * @param float $timeb Time B.
     * @param string $note Note.
     * @return array
     */
    public static function execute(
        int    $cmid,
        int    $videoaid,
        float  $timea,
        int    $videobid,
        float  $timeb,
        string $note
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'videoaid' => $videoaid,
            'timea' => $timea,
            'videobid' => $videobid,
            'timeb' => $timeb,
            'note' => $note,
        ]);

        $cm = get_coursemodule_from_id('videocompare', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videocompare:submit', $context);

        if ($params['videoaid'] === $params['videobid']) {
            throw new \invalid_parameter_exception('Choose two different videos.');
        }

        foreach ([$params['videoaid'], $params['videobid']] as $videoid) {
            if (!$DB->record_exists('videocompare_videos', [
                'id' => $videoid,
                'videocompareid' => $cm->instance,
            ])) {
                throw new \invalid_parameter_exception('Invalid video.');
            }
        }

        $note = trim($params['note']);
        if ($note === '') {
            throw new \invalid_parameter_exception('Comparison text is required.');
        }

        $now = time();
        $record = (object)[
            'videocompareid' => $cm->instance,
            'userid' => $USER->id,
            'videoaid' => $params['videoaid'],
            'timea' => max(0.0, $params['timea']),
            'videobid' => $params['videobid'],
            'timeb' => max(0.0, $params['timeb']),
            'note' => $note,
            'timecreated' => $now,
            'timemodified' => $now,
        ];

        $id = $DB->insert_record('videocompare_notes', $record);
        return ['id' => (int)$id];
    }

    /**
     * Return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'New comparison id'),
        ]);
    }
}
