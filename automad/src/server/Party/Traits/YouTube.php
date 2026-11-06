<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 * (c) Florian Leon Steenbuck - https://kil.ls https://fslap.de
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Party\Traits;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Helpers for YouTube videos. Videos are embedded with youtube-nocookie.com.
 *
 * @author Florian Leon Steenbuck
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
trait YouTube {
	/**
	 * Format seconds as "m:ss" or "h:mm:ss".
	 *
	 * @param int $seconds
	 * @return string
	 */
	protected static function formatTimecode(int $seconds): string {
		$h = intdiv($seconds, 3600);
		$m = intdiv($seconds % 3600, 60);
		$s = $seconds % 60;

		return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
	}

	/**
	 * Convert a timecode like "1:02:03", "02:03", "123", "1h2m3s" or "2m3s" into seconds.
	 *
	 * @param string $timecode
	 * @return int
	 */
	protected static function parseTimecode(string $timecode): int {
		$timecode = trim($timecode);

		if ($timecode === '') {
			return 0;
		}

		if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s?)?$/i', $timecode, $match) && preg_match('/[hms]/i', $timecode)) {
			return (int) ($match[1] ?? 0) * 3600 + (int) ($match[2] ?? 0) * 60 + (int) ($match[3] ?? 0);
		}

		$parts = array_reverse(array_map('intval', explode(':', $timecode)));

		return ($parts[0] ?? 0) + ($parts[1] ?? 0) * 60 + ($parts[2] ?? 0) * 3600;
	}

	/**
	 * Build an embed URL.
	 *
	 * @param string $url
	 * @param array $params additional player parameters
	 * @return string
	 */
	protected static function youtubeEmbedUrl(string $url, array $params = array()): string {
		$id = self::youtubeId($url);

		if ($id === '') {
			return '';
		}

		$start = self::youtubeStart($url);

		if ($start > 0 && !isset($params['start'])) {
			$params['start'] = $start;
		}

		$query = http_build_query(array_merge(array('rel' => 0, 'modestbranding' => 1, 'playsinline' => 1), $params));

		return "https://www.youtube-nocookie.com/embed/$id?$query";
	}
	/**
	 * Extract the video id of a YouTube URL or return the value when it already is an id.
	 * Supported: watch?v=, youtu.be/, embed/, shorts/, live/ and music.youtube.com URLs.
	 *
	 * @param string $url
	 * @return string
	 */
	protected static function youtubeId(string $url): string {
		$url = trim($url);

		if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
			return $url;
		}

		if (preg_match('#(?:youtu\.be/|/embed/|/shorts/|/live/|/v/)([a-zA-Z0-9_-]{11})#', $url, $match)) {
			return $match[1];
		}

		if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $match)) {
			return $match[1];
		}

		return '';
	}

	/**
	 * Return the start time in seconds of a YouTube URL (t= or start= parameter).
	 *
	 * @param string $url
	 * @return int
	 */
	protected static function youtubeStart(string $url): int {
		if (preg_match('#[?&](?:t|start)=([0-9hms]+)#', $url, $match)) {
			return self::parseTimecode($match[1]);
		}

		return 0;
	}
}
