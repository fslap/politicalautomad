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

	public function __construct() {
		parent::__construct(__DIR__, 'scrollreference');
	}

	public function context(array $config): array {
		$items = $config['items'];

		if (empty($items) && $config['source_path'] !== '') {
			$items = $this->loadJsonItems((string) $config['source_path'], (string) $config['source_key']);
		}

		if ($config['match'] !== '') {
			$match = (string) $config['match'];
			$items = array_values(array_filter(
				$items,
				function ($item, $idx) use ($match) {
					if (is_numeric($match) && (int) $match === (int) $idx) {
						return true;
					}

					if (isset($item['id']) && (string) $item['id'] === $match) {
						return true;
					}

					return isset($item['subject']) && stripos((string) $item['subject'], $match) !== false;
				},
				ARRAY_FILTER_USE_BOTH
			));
		}

		return array(
			'section_id' => $config['section_id'],
			'headline' => $config['headline'],
			'items' => array_values(array_filter($items, 'is_array')),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
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
				array('name' => 'source_path', 'type' => 'text', 'label' => 'Alternativ: JSON-Datei', 'placeholder' => '/shared/topics.json', 'help' => 'Pfad relativ zum Automad-Verzeichnis. Wird nur verwendet, wenn keine Einträge gepflegt sind.'),
				array('name' => 'source_key', 'type' => 'text', 'label' => 'JSON-Schlüssel (optional, z.B. de)'),
				array('name' => 'match', 'type' => 'text', 'label' => 'Filter (Index, ID oder Thema)'),
				self::classesField('')
			)
		);
	}

	/**
	 * Load items from a JSON file inside of the Automad base directory.
	 *
	 * @param string $path
	 * @param string $key
	 * @return array
	 */
	private function loadJsonItems(string $path, string $key): array {
		$base = realpath(AM_BASE_DIR);
		$file = realpath(AM_BASE_DIR . '/' . ltrim($path, '/'));

		if (!$base || !$file || strpos($file, $base . DIRECTORY_SEPARATOR) !== 0 || !preg_match('/\.json$/i', $file)) {
			return array();
		}

		$decoded = json_decode((string) file_get_contents($file), true);

		if (!is_array($decoded)) {
			return array();
		}

		if ($key !== '' && isset($decoded[$key]) && is_array($decoded[$key])) {
			$decoded = $decoded[$key];
		}

		if (isset($decoded['items']) && is_array($decoded['items'])) {
			$decoded = $decoded['items'];
		}

		return array_map(function ($item) {
			if (!is_array($item)) {
				return array('subject' => (string) $item);
			}

			return array(
				'id' => $item['id'] ?? '',
				'subject' => $item['subject'] ?? ($item['title'] ?? ''),
				'text' => $item['text'] ?? ($item['description'] ?? '')
			);
		}, array_values($decoded));
	}
}
