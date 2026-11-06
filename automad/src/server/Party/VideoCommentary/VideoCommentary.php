<?php
/*
 * International Political Party Zone for all Partys also Locally
 *
 *                      #;:#;:#;:
 *                       #;:#;:            #;:#;:#;
 *            %&                         #;: #;:;:#;:#;:#;:#
 *    %$%    %$%           #;:    #;:     #;: #;:;:#;:#;:
 *    ____________                ;:      #;:  #;: #;:#;:#;:#;
 *   /§§§§§§§§§§§|                                    ;:#;:#;:
 *   |###########/  / &/      #;:#;:#;:#;:#;:        #;:#;: #;: #;
 *   /%%%%%%%%%%/   |$$$/        #;:#;:#;:#;:#;#;:#;:;:#;:#;:
 *  /######### /   |$$$/          #;:#;:#;:#;:#;:#;:#;:#;:#;:#;:
 * /%%%%%%%%%%/   \CCC/           #;:#;:#;:#;:#;:#;:#;:#;:
 * \#########|   \\|      _\    :#;:  :#;:#;:  #;:#;:#;:#;:
 *  \________/   /[[]]\  \_/   :#\:#\:#\:#\:#;:
 *   \====__/    \xxxx/              #\:#\:#;:#;:
 *   |""""""\    |yyy/                #;: #;:#;:#;: |#;:\
 *   |~~~~~~/ #   |c|                 ;:#;:\;:#;:   |#;:;:
 *   +~~~~~/  |   \t/                  #\:#;\       |##;/
 *    +~~~/   |#
 *     +~/\       \*+ ~
 *                      #
 *
 *
 *
 * https://north.sbdp.ro https://mid.sbdp.ro/ https://pacif.sbdp.ro/ https://low.sbdp.ro/
 * (c) Florian Leon Steenbuck
 *
 * Copyright (c) 2026 by Florian Leon Steenbuck
 * https://kil.ls https://fslap.de
 *
 * See LICENSE_PARTY_PURPOSE.md for license information.
 */

namespace Automad\Party\VideoCommentary;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\YouTube;

defined('AUTOMAD') or die('Direct access not permitted!');

require_once __DIR__ . '/../Markdown/lib/Parsedown.php';

/**
 * A YouTube video in a split screen with timecoded comments. Clicking a timecode jumps to that
 * position of the video and the current comment is highlighted while the video is playing.
 * Comments can be entered one by one or pasted as text with one timecode per line.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class VideoCommentary extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use YouTube;

	/**
	 * The comment categories.
	 */
	const CATEGORIES = array(
		'' => 'Kommentar',
		'fact' => 'Faktencheck',
		'quote' => 'Zitat',
		'context' => 'Einordnung',
		'critique' => 'Kritik',
		'agree' => 'Zustimmung',
		'question' => 'Frage'
	);

	private ?\Parsedown $parser = null;

	public function __construct() {
		parent::__construct(__DIR__, 'videocommentary');
	}

	public function context(array $config): array {
		if ($this->parser === null) {
			$this->parser = new \Parsedown();
			$this->parser->setSafeMode(true);
		}

		$comments = array_merge(
			array_values(array_filter($config['comments'], 'is_array')),
			self::parseCommentText((string) $config['comments_text'])
		);

		$items = array();

		foreach ($comments as $comment) {
			$time = self::parseTimecode((string) ($comment['time'] ?? ''));
			$category = (string) ($comment['category'] ?? '');

			$items[] = array(
				'seconds' => $time,
				'time' => self::formatTimecode($time),
				'title' => (string) ($comment['title'] ?? ''),
				'html' => $this->parser->text((string) ($comment['text'] ?? '')),
				'category' => isset(self::CATEGORIES[$category]) ? $category : '',
				'category_label' => $category !== '' && isset(self::CATEGORIES[$category]) ? self::CATEGORIES[$category] : ''
			);
		}

		usort($items, fn ($a, $b) => $a['seconds'] <=> $b['seconds']);

		$id = self::youtubeId((string) $config['video']);
		$sectionId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']) ?: 'video-' . substr(md5($id . count($items)), 0, 6);

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'intro' => $config['intro'] !== '' ? $this->parser->text((string) $config['intro']) : '',
			'video_id' => $id,
			'embed_url' => self::youtubeEmbedUrl((string) $config['video'], array('enablejsapi' => 1)),
			'comments' => $items,
			'has_comments' => !empty($items),
			'layout' => $config['video_position'] === 'right' ? 'right' : 'left',
			'config_json' => $this->json(array(
				'videoId' => $id,
				'start' => self::youtubeStart((string) $config['video']),
				'follow' => (bool) $config['follow'],
				'pauseOnLeave' => (bool) $config['pause_on_leave']
			)),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Video mit Timecode-Kommentaren',
			'icon' => 'camera-video',
			'description' => 'YouTube-Video im Split-Screen mit zeitgestempelten Kommentaren. Klick auf eine Zeitmarke springt zur Stelle im Video.',
			'fields' => array(
				self::sectionIdField(),
				self::titleField(),
				array('name' => 'video', 'type' => 'url', 'label' => 'YouTube-Link', 'placeholder' => 'https://www.youtube.com/watch?v=…'),
				array('name' => 'intro', 'type' => 'markdown', 'label' => 'Einleitung (optional)'),
				array(
					'name' => 'comments',
					'type' => 'list',
					'label' => 'Kommentare',
					'addLabel' => 'Kommentar hinzufügen',
					'itemTitle' => 'time',
					'fields' => array(
						array('name' => 'time', 'type' => 'text', 'label' => 'Zeitmarke', 'placeholder' => '1:23 oder 1:02:03'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift (optional)'),
						array('name' => 'category', 'type' => 'select', 'label' => 'Art', 'default' => '', 'options' => self::CATEGORIES),
						array('name' => 'text', 'type' => 'markdown', 'label' => 'Kommentar')
					)
				),
				array(
					'name' => 'comments_text',
					'type' => 'textarea',
					'label' => 'Kommentare als Text einfügen (optional)',
					'help' => 'Eine Zeitmarke pro Zeile, z.B. „00:42 – Kommentar …“ oder „[1:02:03] Kommentar …“. Folgezeilen ohne Zeitmarke gehören zum vorherigen Kommentar. Ideal zum Einfügen einer von Claude erstellten Kommentierung.'
				),
				array('name' => 'video_position', 'type' => 'select', 'label' => 'Position des Videos', 'default' => 'left', 'options' => array('left' => 'Links', 'right' => 'Rechts')),
				array('name' => 'follow', 'type' => 'toggle', 'label' => 'Kommentarliste beim Abspielen mitlaufen lassen', 'default' => true),
				array('name' => 'pause_on_leave', 'type' => 'toggle', 'label' => 'Video pausieren, wenn die Section verlassen wird', 'default' => true),
				self::classesField('')
			)
		);
	}

	/**
	 * Parse pasted comments with one timecode per line, e.g. "00:42 – text", "[1:02:03] text",
	 * "**02:15** text" or "- 3:10: text". Lines without a timecode are appended to the previous comment.
	 *
	 * @param string $text
	 * @return array
	 */
	private static function parseCommentText(string $text): array {
		$comments = array();
		$current = null;

		foreach (preg_split('/\r?\n/', $text) ?: array() as $line) {
			if (preg_match('/^\s*(?:[-*•]\s*)?(?:\*\*|\[|\()?\s*((?:\d{1,2}:)?\d{1,2}:\d{2})\s*(?:\*\*|\]|\))?\s*(?:[-–—:|]\s*)?(.*)$/u', $line, $match)) {
				if ($current !== null) {
					$comments[] = $current;
				}

				$rest = trim($match[2]);
				$title = '';

				// "Title: text" or "**Title** text" become a title.
				if (preg_match('/^\*\*(.+?)\*\*\s*[:\-–—]?\s*(.*)$/u', $rest, $titleMatch)) {
					$title = (string) preg_replace('/[\s:–—-]+$/u', '', $titleMatch[1]);
					$rest = $titleMatch[2];
				}

				$current = array('time' => $match[1], 'title' => $title, 'text' => $rest, 'category' => '');

				continue;
			}

			if ($current !== null && trim($line) !== '') {
				$current['text'] .= "\n" . trim($line);
			}
		}

		if ($current !== null) {
			$comments[] = $current;
		}

		return $comments;
	}
}
