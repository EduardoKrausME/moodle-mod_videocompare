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
 * Viewing progress manager.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare;

use stdClass;

/**
 * Stores merged intervals that were actually played and calculates completion percentages.
 */
class progress_manager {
    /**
     * Updates progress for one user and video.
     *
     * @param int $activityid Activity id.
     * @param int $videoid Video id.
     * @param int $userid User id.
     * @param float $position Current position.
     * @param float $duration Duration.
     * @param array $newsegments New watched intervals.
     * @return stdClass
     */
    public static function update(
        int $activityid,
        int $videoid,
        int $userid,
        float $position,
        float $duration,
        array $newsegments
    ): stdClass {
        global $DB;

        $video = $DB->get_record('videocompare_videos', [
            'id' => $videoid,
            'videocompareid' => $activityid,
        ], '*', MUST_EXIST);
        unset($video);

        $record = $DB->get_record('videocompare_progress', [
            'videoid' => $videoid,
            'userid' => $userid,
        ]);

        $existing = [];
        if ($record && $record->segments) {
            $decoded = json_decode($record->segments, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $duration = max(0.0, min($duration, 86400.0));
        $position = max(0.0, $duration > 0 ? min($position, $duration) : $position);
        $validated = self::validate_segments($newsegments, $duration);
        $segments = self::merge_segments(array_merge($existing, $validated));
        $watched = 0.0;
        foreach ($segments as $segment) {
            $watched += max(0.0, $segment[1] - $segment[0]);
        }

        $percent = $duration > 0 ? min(100.0, ($watched / $duration) * 100.0) : 0.0;
        $now = time();

        if (!$record) {
            $record = (object)[
                'videocompareid' => $activityid,
                'videoid' => $videoid,
                'userid' => $userid,
                'timecreated' => $now,
            ];
        }

        $record->duration = $duration;
        $record->lastposition = $position;
        $record->watchedseconds = round($watched, 2);
        $record->percent = round($percent, 2);
        $record->segments = json_encode($segments, JSON_UNESCAPED_SLASHES);
        $record->completed = $percent >= 99.5 ? 1 : 0;
        $record->timemodified = $now;

        if (empty($record->id)) {
            $record->id = $DB->insert_record('videocompare_progress', $record);
        } else {
            $DB->update_record('videocompare_progress', $record);
        }

        return $record;
    }

    /**
     * Returns progress records indexed by video id.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return stdClass[]
     */
    public static function get_for_user(int $activityid, int $userid): array {
        global $DB;

        return $DB->get_records('videocompare_progress', [
            'videocompareid' => $activityid,
            'userid' => $userid,
        ], '', '*', 0, 0);
    }

    /**
     * Returns progress records keyed by video id.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return array<int, stdClass>
     */
    public static function get_indexed_for_user(int $activityid, int $userid): array {
        $indexed = [];
        foreach (self::get_for_user($activityid, $userid) as $record) {
            $indexed[(int)$record->videoid] = $record;
        }
        return $indexed;
    }

    /**
     * Calculates overall percentage as the average percentage across every configured video.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return float
     */
    public static function overall_percent(int $activityid, int $userid): float {
        global $DB;

        $videos = $DB->get_records('videocompare_videos', ['videocompareid' => $activityid], '', 'id');
        if (!$videos) {
            return 0.0;
        }

        $progress = self::get_indexed_for_user($activityid, $userid);
        $sum = 0.0;
        foreach ($videos as $video) {
            $sum += isset($progress[$video->id]) ? (float)$progress[$video->id]->percent : 0.0;
        }
        return round($sum / count($videos), 2);
    }

    /**
     * Counts required questions and answered required questions.
     *
     * @param int $activityid Activity id.
     * @param int $userid User id.
     * @return array{required:int, answered:int}
     */
    public static function required_question_status(int $activityid, int $userid): array {
        global $DB;

        $questions = $DB->get_records('videocompare_questions', [
            'videocompareid' => $activityid,
            'required' => 1,
        ], '', 'id');

        if (!$questions) {
            return ['required' => 0, 'answered' => 0];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_keys($questions), SQL_PARAMS_NAMED, 'q');
        $params['userid'] = $userid;
        $sql = "SELECT COUNT(1)
                  FROM {videocompare_answers}
                 WHERE userid = :userid
                   AND questionid {$insql}
                   AND " . $DB->sql_length('answer') . " > 0";
        $answered = (int)$DB->count_records_sql($sql, $params);

        return ['required' => count($questions), 'answered' => $answered];
    }

    /**
     * Determines whether configured custom completion requirements are met.
     *
     * @param stdClass $activity Activity record.
     * @param int $userid User id.
     * @return bool
     */
    public static function completion_met(stdClass $activity, int $userid): bool {
        $progressok = self::overall_percent((int)$activity->id, $userid) >= (float)$activity->completionpercent;

        if (empty($activity->completionquestions)) {
            return $progressok;
        }

        $status = self::required_question_status((int)$activity->id, $userid);
        $questionsok = $status['required'] === 0 || $status['answered'] >= $status['required'];
        return $progressok && $questionsok;
    }

    /**
     * Validates small client-produced intervals.
     *
     * @param array $segments Segments.
     * @param float $duration Duration.
     * @return array
     */
    private static function validate_segments(array $segments, float $duration): array {
        $valid = [];
        foreach ($segments as $segment) {
            if (!is_array($segment) || count($segment) < 2) {
                continue;
            }
            $start = (float)$segment[0];
            $end = (float)$segment[1];
            if (!is_finite($start) || !is_finite($end)) {
                continue;
            }
            $start = max(0.0, $start);
            $end = max(0.0, $end);
            if ($duration > 0) {
                $start = min($start, $duration);
                $end = min($end, $duration);
            }
            $length = $end - $start;
            if ($length <= 0.0 || $length > 60.0) {
                continue;
            }
            $valid[] = [round($start, 2), round($end, 2)];
        }
        return $valid;
    }

    /**
     * Merges overlapping and nearly contiguous intervals.
     *
     * @param array $segments Segments.
     * @return array
     */
    private static function merge_segments(array $segments): array {
        $normalised = [];
        foreach ($segments as $segment) {
            if (!is_array($segment) || !isset($segment[0], $segment[1])) {
                continue;
            }
            $start = (float)$segment[0];
            $end = (float)$segment[1];
            if ($end <= $start) {
                continue;
            }
            $normalised[] = [$start, $end];
        }

        usort($normalised, static function (array $a, array $b): int {
            return $a[0] <=> $b[0];
        });

        $merged = [];
        foreach ($normalised as $segment) {
            if (!$merged) {
                $merged[] = $segment;
                continue;
            }
            $index = count($merged) - 1;
            if ($segment[0] <= $merged[$index][1] + 0.5) {
                $merged[$index][1] = max($merged[$index][1], $segment[1]);
            } else {
                $merged[] = $segment;
            }
        }

        return array_map(static function (array $segment): array {
            return [round($segment[0], 2), round($segment[1], 2)];
        }, $merged);
    }
}
