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

namespace Automad\Party\ScrollReference;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Automad;
use Automad\Core\Resolve;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component ScrollReference (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ScrollReference extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * The path of the rendered page, used to resolve files picked with the file field.
	 */
	private string $pagePath = '';

	public function __construct() {
		parent::__construct(__DIR__, 'scrollreference');
	}

	/**
	 * Get a JSON value by a key, also a dotted path like "de.topics".
	 *
	 * @param array $data
	 * @param string $key
	 * @return array|null
	 */
	public static function byKey(array $data, string $key): ?array {
		foreach (explode('.', $key) as $part) {
			if (!isset($data[$part]) || !is_array($data[$part])) {
				return $data;
			}

			$data = $data[$part];
		}

		return $data;
	}

	/**
	 * Collect the entries of a JSON node as a flat list.
	 *
	 * @param array $node
	 * @param string $category
	 * @return array
	 */
	public static function collectItems(array $node, string $category = ''): array {
		if (!array_is_list($node)) {
			$children = self::children($node);

			if ($children !== null) {
				return self::collectItems($children, self::label($node) ?: $category);
			}

			// A map of groups, e.g. {"Freiheit": [...], "Eigentum": [...]}.
			$all = array();

			foreach ($node as $key => $value) {
				if (is_array($value)) {
					$all = array_merge($all, self::collectItems($value, is_string($key) ? $key : $category));
				}
			}

			return $all ?: array($node);
		}

		$items = array();

		foreach ($node as $value) {
			if (is_array($value) && !array_is_list($value) && self::children($value) !== null && self::text($value) === '') {
				$items = array_merge($items, self::collectItems($value, $category));

				continue;
			}

			if (is_array($value) && !array_is_list($value)) {
				$value['category'] = $value['category'] ?? $category;
			}

			$items[] = $value;
		}

		return $items;
	}

	public function context(array $config): array {
		$items = is_array($config['items']) ? $config['items'] : array();
		$match = trim((string) $config['match']);

		$source = trim((string) ($config['source_file'] ?? ''));

		if ($source !== '' && !preg_match('#^https?://#i', $source)) {
			$source = Resolve::filePath($this->pagePath, $source);
		}

		$source = $source ?: trim((string) $config['source_path']);

		if (empty($items) && $source !== '') {
			$data = self::loadJson($source);

			if ($data !== null && trim((string) $config['source_key']) !== '') {
				$data = self::byKey($data, trim((string) $config['source_key']));
			}

			if ($data !== null) {
				// A group (e.g. a topic with its demands) that matches the filter wins.
				$group = $match !== '' ? self::findGroup($data, $match) : null;

				if ($group !== null) {
					$items = self::collectItems($group);
					$match = '';
				} else {
					$items = self::collectItems($data);
				}
			}
		}

		$items = array_map(fn ($item) => is_array($item) ? self::normalizeItem($item) : array('subject' => (string) $item), $items);

		if ($match !== '') {
			$items = array_values(array_filter(
				$items,
				function ($item, $idx) use ($match) {
					if (is_numeric($match) && (int) $match === (int) $idx) {
						return true;
					}

					if ((string) ($item['id'] ?? '') === $match) {
						return true;
					}

					foreach (array('subject', 'category') as $key) {
						if (isset($item[$key]) && stripos((string) $item[$key], $match) !== false) {
							return true;
						}
					}

					return false;
				},
				ARRAY_FILTER_USE_BOTH
			));
		}

		return array(
			'section_id' => $config['section_id'],
			'headline' => $config['headline'],
			'items' => array_values(array_filter($items, fn ($item) => ($item['subject'] ?? '') !== '' || ($item['text'] ?? '') !== '')),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	/**
	 * Find a group whose label or key matches, e.g. {"title": "Freiheit", "items": [...]}
	 * or {"Freiheit": [...]}. The search is recursive and case-insensitive.
	 *
	 * @param array $data
	 * @param string $match
	 * @return array|null
	 */
	public static function findGroup(array $data, string $match): ?array {
		$needle = mb_strtolower(trim($match));

		foreach ($data as $key => $value) {
			if (is_string($key) && mb_strtolower(trim($key)) === $needle && is_array($value)) {
				return $value;
			}
		}

		if (!array_is_list($data)) {
			$label = self::label($data);

			if ($label !== '' && mb_strtolower($label) === $needle && self::children($data) !== null) {
				return self::children($data);
			}
		}

		foreach ($data as $value) {
			if (is_array($value) && ($found = self::findGroup($value, $match)) !== null) {
				return $found;
			}
		}

		return null;
	}

	/**
	 * Load and decode a JSON source. Supported are paths relative to the Automad directory,
	 * absolute paths on the server (e.g. a neighbouring site of the same hosting account) and URLs.
	 *
	 * @param string $source
	 * @return array|null
	 */
	public static function loadJson(string $source): ?array {
		if (preg_match('#^https?://#i', $source)) {
			$json = self::fetchCached($source);
		} else {
			$json = null;
			$candidates = array(AM_BASE_DIR . '/' . ltrim($source, '/'));

			if (str_starts_with($source, '/')) {
				$candidates[] = $source;
			}

			foreach ($candidates as $file) {
				if (preg_match('/\.json$/i', $file) && @is_file($file) && @is_readable($file)) {
					$json = (string) @file_get_contents($file);

					break;
				}
			}
		}

		if (!$json) {
			return null;
		}

		$decoded = json_decode($json, true);

		return is_array($decoded) ? $decoded : null;
	}

	/**
	 * Normalize an entry with common key names.
	 *
	 * @param array $item
	 * @return array
	 */
	public static function normalizeItem(array $item): array {
		return array(
			'id' => (string) self::first($item, array('id', 'anchor', 'slug')),
			'subject' => self::label($item),
			'text' => self::text($item),
			'category' => (string) self::first($item, array('category', 'topic', 'thema', 'group'))
		);
	}

	/**
	 * Remember the page path before rendering.
	 *
	 * @param array $block
	 * @param Automad $Automad
	 * @return string
	 */
	public function render(array $block, Automad $Automad): string {
		$this->pagePath = (string) ($Automad->Context->get()->path ?? '');

		return parent::render($block, $Automad);
	}

	protected function definition(): array {
		return array(
			'title' => 'Scroll-Referenz',
			'icon' => 'distribute-horizontal',
			'description' => 'Horizontal scrollende Themenliste mit Sprungankern – manuell oder aus einer JSON-Datei.',
			'fields' => array(
				self::sectionIdField('topics'),
				array('name' => 'headline', 'type' => 'text', 'label' => 'Überschrift', 'default' => 'Themen'),
				array(
					'name' => 'items',
					'type' => 'list',
					'label' => 'Einträge',
					'itemTitle' => 'subject',
					'fields' => array(
						array('name' => 'id', 'type' => 'text', 'label' => 'Anker-ID'),
						array('name' => 'subject', 'type' => 'text', 'label' => 'Thema'),
						array('name' => 'text', 'type' => 'textarea', 'label' => 'Text')
					)
				),
				array('name' => 'source_file', 'type' => 'file', 'label' => 'JSON-Datei auswählen', 'help' => 'Hochgeladene JSON-Datei der Seite oder aus shared. Hat Vorrang vor dem Pfad darunter.'),
				array('name' => 'source_path', 'type' => 'text', 'label' => 'Alternativ: Pfad oder URL der JSON-Datei', 'placeholder' => '/shared/topics.json', 'help' => 'Pfad relativ zum Automad-Verzeichnis, absoluter Pfad auf dem Server (z.B. /home/webpages/…/data.json) oder URL (https://…/data.json, 1 Stunde gecacht). Wird nur verwendet, wenn keine Einträge gepflegt sind.'),
				array('name' => 'source_key', 'type' => 'text', 'label' => 'JSON-Schlüssel (optional, z.B. de oder de.topics)'),
				array('name' => 'match', 'type' => 'text', 'label' => 'Filter (Thema/Gruppe, Index, ID oder Titel)', 'help' => 'Ein Gruppentitel wie „Freiheit und Selbstbestimmung“ zeigt alle Einträge dieser Gruppe.'),
				self::classesField('')
			)
		);
	}

	/**
	 * The list of child entries of a group.
	 *
	 * @param array $node
	 * @return array|null
	 */
	private static function children(array $node): ?array {
		foreach (array('items', 'topic', 'entries', 'children', 'points', 'demands', 'forderungen', 'punkte', 'list', 'topics', 'themen', 'data') as $key) {
			if (isset($node[$key]) && is_array($node[$key]) && $node[$key] && array_is_list($node[$key])) {
				return $node[$key];
			}
		}

		return null;
	}

	/**
	 * Get a cached remote file (1 hour), stored outside the web root.
	 *
	 * @param string $url
	 * @return string|null
	 */
	private static function fetchCached(string $url): ?string {
		$file = \Automad\System\FileSystem::getTmpDir() . '/scroll-reference-' . sha1($url) . '.json';

		if (is_readable($file) && filemtime($file) > time() - 3600) {
			return (string) file_get_contents($file);
		}

		$json = \Automad\System\Fetch::request($url);

		if ($json !== '' && is_array(json_decode($json, true))) {
			@file_put_contents($file, $json);

			return $json;
		}

		return is_readable($file) ? (string) file_get_contents($file) : null;
	}

	/**
	 * The first non-empty scalar of the given keys.
	 *
	 * @param array $item
	 * @param array $keys
	 * @return string
	 */
	private static function first(array $item, array $keys): string {
		foreach ($keys as $key) {
			if (isset($item[$key]) && is_scalar($item[$key]) && trim((string) $item[$key]) !== '') {
				return trim((string) $item[$key]);
			}
		}

		return '';
	}

	/**
	 * The title of an entry.
	 *
	 * @param array $item
	 * @return string
	 */
	private static function label(array $item): string {
		return self::first($item, array('subject', 'title', 'headline', 'heading', 'name', 'label', 'titel', 'ueberschrift'));
	}

	/**
	 * The text of an entry (lists of strings are joined).
	 *
	 * @param array $item
	 * @return string
	 */
	private static function text(array $item): string {
		foreach (array('text', 'desc', 'description', 'content', 'body', 'summary', 'beschreibung', 'inhalt') as $key) {
			if (isset($item[$key]) && is_scalar($item[$key]) && trim((string) $item[$key]) !== '') {
				return trim((string) $item[$key]);
			}

			if (isset($item[$key]) && is_array($item[$key])) {
				$parts = array_filter($item[$key], 'is_scalar');

				if ($parts) {
					return trim(join("\n", $parts));
				}
			}
		}

		return '';
	}
}
