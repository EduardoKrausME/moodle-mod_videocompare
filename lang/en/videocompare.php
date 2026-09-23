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
 * English language strings for Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addquestion'] = 'Add comparison question';
$string['addvideo'] = 'Add video';
$string['allanswered'] = 'All required questions answered';
$string['answer'] = 'Answer';
$string['answerquestions'] = 'Comparison questions';
$string['answerssaved'] = 'Answers saved.';
$string['backtoreport'] = 'Back to report';
$string['capturedtime'] = 'Captured time';
$string['capturetime'] = 'Capture current time';
$string['compareinstructions'] = 'Choose two videos, capture the current moment in each, and write the comparison.';
$string['comparemoments'] = 'Compare moments';
$string['comparison'] = 'Comparison';
$string['completionpercent'] = 'Required overall video progress (%)';
$string['completionquestions'] = 'Require all mandatory comparison questions';
$string['completionquestions_help'] = 'When enabled, all questions marked as required must have a non-empty answer.';
$string['completionrules'] = '';
$string['configureactivity'] = 'Configure activity';
$string['deletequestion'] = 'Delete comparison question';
$string['deletevideo'] = 'Delete video';
$string['description'] = 'Description';
$string['displaysettings'] = 'Display settings';
$string['editquestion'] = 'Edit comparison question';
$string['editvideo'] = 'Edit video';
$string['error:captureboth'] = 'Capture the current moment in both selected videos before saving.';
$string['error:comparisonrequired'] = 'Write the comparison before saving.';
$string['error:samemomentvideo'] = 'Choose two different videos for the references.';
$string['error:samevideos'] = 'Choose two different videos.';
$string['errorcompletionpercent'] = 'The completion percentage must be between 1 and 100.';
$string['errorinvalidvimeo'] = 'The URL does not contain a valid Vimeo video id.';
$string['errorinvalidyoutube'] = 'The URL does not contain a valid YouTube video id.';
$string['errormaxfiles'] = 'Only one file can be uploaded.';
$string['errortimecode'] = 'Use seconds, MM:SS, or HH:MM:SS.';
$string['erroruploadrequired'] = 'Upload a video file.';
$string['errorurlrequired'] = 'Enter the video URL.';
$string['eventcoursemoduleviewed'] = 'Video Compare activity viewed';
$string['jump'] = 'Jump to this moment';
$string['lastposition'] = 'Last position';
$string['layout'] = 'Video layout';
$string['layoutauto'] = 'Automatic: side by side when space allows';
$string['layoutsidebyside'] = 'Prefer side by side';
$string['layoutswitch'] = 'Show one video at a time with quick switching';
$string['managecontent'] = 'Manage videos and questions';
$string['modulename'] = 'Video Compare';
$string['modulenameplural'] = 'Video Compare activities';
$string['movedown'] = 'Move down';
$string['moveup'] = 'Move up';
$string['nocomparisons'] = 'No timestamp comparisons yet.';
$string['nocontent'] = 'No content available.';
$string['notallanswered'] = 'Required questions pending';
$string['notconfigured'] = 'This activity needs at least two videos before students can compare them.';
$string['optional'] = 'Optional';
$string['overallprogress'] = 'Overall progress';
$string['pluginadministration'] = 'Video Compare administration';
$string['pluginname'] = 'Video Compare';
$string['privacy:metadata:timecreated'] = 'The time when the record was created.';
$string['privacy:metadata:timemodified'] = 'The time when the record was last modified.';
$string['privacy:metadata:videocompare_answers'] = 'Stores student answers to comparison questions.';
$string['privacy:metadata:videocompare_answers:answer'] = 'The submitted answer.';
$string['privacy:metadata:videocompare_answers:answerformat'] = 'The text format used for the submitted answer.';
$string['privacy:metadata:videocompare_answers:questionid'] = 'The question answered.';
$string['privacy:metadata:videocompare_answers:userid'] = 'The user who submitted the answer.';
$string['privacy:metadata:videocompare_answers:videocompareid'] = 'The Video Compare activity associated with the answer.';
$string['privacy:metadata:videocompare_notes'] = 'Stores student timestamp-to-timestamp comparisons.';
$string['privacy:metadata:videocompare_notes:note'] = 'The comparison text.';
$string['privacy:metadata:videocompare_notes:timea'] = 'Timestamp in video A.';
$string['privacy:metadata:videocompare_notes:timeb'] = 'Timestamp in video B.';
$string['privacy:metadata:videocompare_notes:userid'] = 'The user who created the comparison.';
$string['privacy:metadata:videocompare_notes:videoaid'] = 'The first video referenced by the comparison.';
$string['privacy:metadata:videocompare_notes:videobid'] = 'The second video referenced by the comparison.';
$string['privacy:metadata:videocompare_notes:videocompareid'] = 'The Video Compare activity associated with the timestamp comparison.';
$string['privacy:metadata:videocompare_progress'] = 'Stores each user\'s viewing progress for each video.';
$string['privacy:metadata:videocompare_progress:completed'] = 'Whether the video reached the internal watched threshold.';
$string['privacy:metadata:videocompare_progress:duration'] = 'The known duration of the video.';
$string['privacy:metadata:videocompare_progress:lastposition'] = 'The last playback position.';
$string['privacy:metadata:videocompare_progress:percent'] = 'The watched percentage.';
$string['privacy:metadata:videocompare_progress:segments'] = 'The watched time intervals.';
$string['privacy:metadata:videocompare_progress:userid'] = 'The user whose viewing progress is stored.';
$string['privacy:metadata:videocompare_progress:videocompareid'] = 'The Video Compare activity associated with the progress record.';
$string['privacy:metadata:videocompare_progress:videoid'] = 'The video being tracked.';
$string['privacy:metadata:videocompare_progress:watchedseconds'] = 'The number of unique seconds watched.';
$string['progress'] = 'Progress';
$string['questions'] = 'Comparison questions';
$string['questiontext'] = 'Question';
$string['referencetimea'] = 'Reference time A';
$string['referencetimeb'] = 'Reference time B';
$string['referencevideoa'] = 'Reference video A';
$string['referencevideob'] = 'Reference video B';
$string['reports'] = 'Reports';
$string['required'] = 'Required';
$string['requiredanswered'] = 'Required answered';
$string['requiredquestion'] = 'Required question';
$string['resume'] = 'Resume';
$string['saveanswers'] = 'Save answers';
$string['savecomparison'] = 'Save comparison';
$string['savedcomparisons'] = 'Your timestamp comparisons';
$string['sourcetype'] = 'Video source';
$string['sourceupload'] = 'Uploaded video';
$string['sourceuploadoption'] = 'Upload';
$string['sourceurl'] = 'Video URL';
$string['sourceurl_help'] = 'Use a direct video URL, YouTube URL, or Vimeo URL according to the selected source.';
$string['sourceurloption'] = 'Direct video URL';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Student';
$string['studentreport'] = 'Student report';
$string['switchvideo'] = 'Switch video';
$string['timecodehelp'] = 'Use seconds, MM:SS, or HH:MM:SS.';
$string['video'] = 'Video';
$string['videoa'] = 'Video A';
$string['videob'] = 'Video B';
$string['videocompare:addinstance'] = 'Add a new Video Compare activity';
$string['videocompare:managecontent'] = 'Manage videos and comparison questions';
$string['videocompare:submit'] = 'Submit comparisons and answers';
$string['videocompare:view'] = 'View Video Compare';
$string['videocompare:viewreports'] = 'View Video Compare reports';
$string['videocomparename'] = 'Video Compare name';
$string['videofile'] = 'Video file';
$string['videoname'] = 'Video name';
$string['videos'] = 'Videos';
$string['viewdetails'] = 'View details';
$string['watched'] = 'Watched';
