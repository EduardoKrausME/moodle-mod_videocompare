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
 * Privacy provider for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Exports and deletes personal data stored by Video Compare.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videocompare_progress', [
            'videocompareid' => 'privacy:metadata:videocompare_progress:videocompareid',
            'userid' => 'privacy:metadata:videocompare_progress:userid',
            'videoid' => 'privacy:metadata:videocompare_progress:videoid',
            'duration' => 'privacy:metadata:videocompare_progress:duration',
            'lastposition' => 'privacy:metadata:videocompare_progress:lastposition',
            'watchedseconds' => 'privacy:metadata:videocompare_progress:watchedseconds',
            'percent' => 'privacy:metadata:videocompare_progress:percent',
            'segments' => 'privacy:metadata:videocompare_progress:segments',
            'completed' => 'privacy:metadata:videocompare_progress:completed',
            'timecreated' => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:videocompare_progress');

        $collection->add_database_table('videocompare_answers', [
            'videocompareid' => 'privacy:metadata:videocompare_answers:videocompareid',
            'userid' => 'privacy:metadata:videocompare_answers:userid',
            'questionid' => 'privacy:metadata:videocompare_answers:questionid',
            'answer' => 'privacy:metadata:videocompare_answers:answer',
            'answerformat' => 'privacy:metadata:videocompare_answers:answerformat',
            'timecreated' => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:videocompare_answers');

        $collection->add_database_table('videocompare_notes', [
            'videocompareid' => 'privacy:metadata:videocompare_notes:videocompareid',
            'userid' => 'privacy:metadata:videocompare_notes:userid',
            'videoaid' => 'privacy:metadata:videocompare_notes:videoaid',
            'timea' => 'privacy:metadata:videocompare_notes:timea',
            'videobid' => 'privacy:metadata:videocompare_notes:videobid',
            'timeb' => 'privacy:metadata:videocompare_notes:timeb',
            'note' => 'privacy:metadata:videocompare_notes:note',
            'timecreated' => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:videocompare_notes');

        return $collection;
    }

    /**
     * Finds module contexts containing data for the user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        foreach (['videocompare_progress', 'videocompare_answers', 'videocompare_notes'] as $table) {
            $sql = "SELECT ctx.id
                      FROM {{$table}} d
                      JOIN {videocompare} vc ON vc.id = d.videocompareid
                      JOIN {course_modules} cm ON cm.instance = vc.id
                      JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                      JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextlevel
                     WHERE d.userid = :userid";
            $contextlist->add_from_sql($sql, [
                'modname' => 'videocompare',
                'contextlevel' => CONTEXT_MODULE,
                'userid' => $userid,
            ]);
        }
        return $contextlist;
    }

    /**
     * Exports a user's data in approved module contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('videocompare', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            $activity = $DB->get_record('videocompare', ['id' => $cm->instance]);
            if (!$activity) {
                continue;
            }

            $progress = array_values($DB->get_records('videocompare_progress', [
                'videocompareid' => $activity->id,
                'userid' => $userid,
            ]));
            $answers = array_values($DB->get_records('videocompare_answers', [
                'videocompareid' => $activity->id,
                'userid' => $userid,
            ]));
            $notes = array_values($DB->get_records('videocompare_notes', [
                'videocompareid' => $activity->id,
                'userid' => $userid,
            ]));

            foreach ($progress as $record) {
                $record->timecreated = transform::datetime($record->timecreated);
                $record->timemodified = transform::datetime($record->timemodified);
            }
            foreach ($answers as $record) {
                $record->timecreated = transform::datetime($record->timecreated);
                $record->timemodified = transform::datetime($record->timemodified);
            }
            foreach ($notes as $record) {
                $record->timecreated = transform::datetime($record->timecreated);
                $record->timemodified = transform::datetime($record->timemodified);
            }

            writer::with_context($context)->export_data([], (object)[
                'progress' => $progress,
                'answers' => $answers,
                'timestampcomparisons' => $notes,
            ]);
        }
    }

    /**
     * Deletes all personal data in a module context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('videocompare', $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }
        foreach (['videocompare_progress', 'videocompare_answers', 'videocompare_notes'] as $table) {
            $DB->delete_records($table, ['videocompareid' => $cm->instance]);
        }
    }

    /**
     * Deletes one user's data from the approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('videocompare', $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }
            foreach (['videocompare_progress', 'videocompare_answers', 'videocompare_notes'] as $table) {
                $DB->delete_records($table, ['videocompareid' => $cm->instance, 'userid' => $userid]);
            }
        }
    }
}
