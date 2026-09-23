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
 * Custom completion for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare\completion;

use core_completion\activity_custom_completion;
use mod_videocompare\progress_manager;

/**
 * Custom completion implementation.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Returns the state of a named custom rule.
     *
     * @param string $rule Rule.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;
        $instance = $DB->get_record('videocompare', ['id' => $this->cm->instance], '*', MUST_EXIST);

        if ($rule === 'completionpercent') {
            return progress_manager::overall_percent((int)$instance->id, (int)$this->userid)
            >= (float)$instance->completionpercent ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }

        if ($rule === 'completionquestions') {
            if (empty($instance->completionquestions)) {
                return COMPLETION_COMPLETE;
            }
            $status = progress_manager::required_question_status((int)$instance->id, (int)$this->userid);
            return ($status['required'] === 0 || $status['answered'] >= $status['required'])
                ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }

        return COMPLETION_INCOMPLETE;
    }

    /**
     * Describes active custom completion rules.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionpercent', 'completionquestions'];
    }

    /**
     * Returns localized completion descriptions.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;
        $instance = $DB->get_record('videocompare', ['id' => $this->cm->instance], '*', MUST_EXIST);

        return [
            'completionpercent' => get_string('completionpercent', 'videocompare') . ': ' . (int)$instance->completionpercent . '%',
            'completionquestions' => get_string('completionquestions', 'videocompare'),
        ];
    }

    /**
     * Returns sort order for completion rules.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return ['completionpercent', 'completionquestions'];
    }
}
