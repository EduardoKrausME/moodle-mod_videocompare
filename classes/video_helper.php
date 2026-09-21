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
 * Video source and presentation helper.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videocompare;

use context_module;
use moodle_url;
use stdClass;

/**
 * Builds player-safe information for configured videos.
 */
class video_helper {
    /**
     * Builds a template context for one video.
     *
     * @param stdClass $video Video record.
     * @param context_module $context Module context.
     * @param stdClass|null $progress User progress.
     * @return array
     */
    public static function build(stdClass $video, context_module $context, ?stdClass $progress = null): array {
        $sourceurl = '';
        $providerid = '';

        if ($video->sourcetype === 'upload') {
            $sourceurl = self::uploaded_url($video, $context);
        } else if ($video->sourcetype === 'youtube') {
            $providerid = self::youtube_id((string)$video->sourceurl) ?? '';
        } else if ($video->sourcetype === 'vimeo') {
            $providerid = self::vimeo_id((string)$video->sourceurl) ?? '';
        } else {
            $sourceurl = (string)$video->sourceurl;
        }

        $segments = [];
        if ($progress && !empty($progress->segments)) {
            $decoded = json_decode($progress->segments, true);
            if (is_array($decoded)) {
                foreach ($decoded as $segment) {
                    if (!isset($segment[0], $segment[1]) || (float)$progress->duration <= 0) {
                        continue;
                    }
                    $start = max(0.0, min(100.0, ((float)$segment[0] / (float)$progress->duration) * 100.0));
                    $end = max($start, min(100.0, ((float)$segment[1] / (float)$progress->duration) * 100.0));
                    $segments[] = [
                        'left' => round($start, 3),
                        'width' => round($end - $start, 3),
                    ];
                }
            }
        }

        return [
            'id' => (int)$video->id,
            'name' => format_string($video->name),
            'description' => format_text($video->description, $video->descriptionformat, ['context' => $context]),
            'sourcetype' => $video->sourcetype,
            'sourceurl' => $sourceurl,
            'providerid' => $providerid,
            'ishtml5' => in_array($video->sourcetype, ['upload', 'url'], true),
            'isyoutube' => $video->sourcetype === 'youtube',
            'isvimeo' => $video->sourcetype === 'vimeo',
            'percent' => $progress ? round((float)$progress->percent, 1) : 0,
            'lastposition' => $progress ? (float)$progress->lastposition : 0,
            'lastpositionformatted' => videocompare_format_time($progress ? $progress->lastposition : 0),
            'segments' => $segments,
        ];
    }

    /**
     * Returns URL for an uploaded video.
     *
     * @param stdClass $video Video record.
     * @param context_module $context Module context.
     * @return string
     */
    public static function uploaded_url(stdClass $video, context_module $context): string {
        $files = get_file_storage()->get_area_files(
            $context->id,
            'mod_videocompare',
            'video',
            $video->id,
            'filename',
            false
        );
        if (!$files) {
            return '';
        }
        $file = reset($files);
        return moodle_url::make_pluginfile_url(
            $context->id,
            'mod_videocompare',
            'video',
            $video->id,
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    /**
     * Extracts a YouTube id.
     *
     * @param string $url URL or bare id.
     * @return string|null
     */
    public static function youtube_id(string $url): ?string {
        $url = trim($url);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }
        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~youtube\.com/watch\?.*v=([A-Za-z0-9_-]{11})~',
            '~youtube\.com/embed/([A-Za-z0-9_-]{11})~',
            '~youtube\.com/shorts/([A-Za-z0-9_-]{11})~',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Extracts a Vimeo numeric id.
     *
     * @param string $url URL or bare id.
     * @return string|null
     */
    public static function vimeo_id(string $url): ?string {
        $url = trim($url);
        if (preg_match('/^\d+$/', $url)) {
            return $url;
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
