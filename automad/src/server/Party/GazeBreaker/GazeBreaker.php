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

namespace Automad\Party\GazeBreaker;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * A breaker band that is split into equal columns. Small images (e.g. faces) are placed
 * at selectable positions across the columns and look at the visitor (follow the pointer).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class GazeBreaker extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const VERTICAL = array('top' => 'Oben', 'middle' => 'Mitte', 'bottom' => 'Unten');
	const HORIZONTAL = array('left' => 'Links', 'center' => 'Mitte', 'right' => 'Rechts');
	const SIZES = array('s' => 'Klein (35 %)', 'm' => 'Mittel (55 %)', 'l' => 'Groß (75 %)', 'xl' => 'Spaltenbreit (95 %)');

	public function __construct() {
		parent::__construct(__DIR__, 'gazebreaker');
	}

	public function context(array $config): array {
		$columns = max(1, min(12, (int) $config['columns'] ?: 6));
		$items = array();

		foreach (is_array($config['items']) ? $config['items'] : array() as $index => $item) {
			if (!is_array($item) || empty($item['image'])) {
				continue;
			}

			$column = (int) ($item['column'] ?? 0);
			$column = $column >= 1 && $column <= $columns ? $column : ($index % $columns) + 1;
			$vertical = isset(self::VERTICAL[$item['vertical'] ?? '']) ? $item['vertical'] : 'middle';
			$horizontal = isset(self::HORIZONTAL[$item['horizontal'] ?? '']) ? $item['horizontal'] : 'center';
			$size = isset(self::SIZES[$item['size'] ?? '']) ? $item['size'] : 'm';

			$items[] = array(
				'image' => (string) $item['image'],
				'alt' => (string) ($item['alt'] ?? ''),
				'link' => (string) ($item['link'] ?? ''),
				'style' => "grid-column: $column;",
				'classes' => "gaze-breaker__item--$vertical gaze-breaker__item--$horizontal gaze-breaker__item--$size",
				// Every item looks a bit differently (depth of the movement).
				'depth' => 0.6 + (($index * 37) % 9) / 10
			);
		}

		$height = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '220px';

		return array_merge(array(
			'section_id' => $config['section_id'],
			'items' => $items,
			'columns' => array_map(fn ($column) => array('style' => "grid-column: $column;"), range(1, $columns)),
			'style' => "--gaze-columns: $columns; --gaze-height: $height;",
			'dividers' => (bool) $config['dividers'],
			'classes' => $this->getClasses($config)
		), self::hoverContext($config));
	}

	protected function definition(): array {
		return array(
			'title' => 'Blick-Breaker',
			'icon' => 'eye',
			'description' => 'Band aus gleich breiten Spalten mit kleinen Bildern, die den Besucher ansehen.',
			'fields' => array(
				self::sectionIdField(''),
				array('name' => 'columns', 'type' => 'number', 'label' => 'Anzahl Spalten (gleichmäßig aufgeteilt)', 'default' => 6),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe des Bands', 'default' => '220px'),
				array('name' => 'dividers', 'type' => 'toggle', 'label' => 'Trennlinien zwischen den Spalten', 'default' => true),
				array(
					'name' => 'items',
					'type' => 'list',
					'label' => 'Bilder',
					'addLabel' => 'Bild hinzufügen',
					'itemTitle' => 'alt',
					'fields' => array(
						array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
						array('name' => 'alt', 'type' => 'text', 'label' => 'Beschreibung (Alt-Text)'),
						array('name' => 'column', 'type' => 'number', 'label' => 'Spalte (1 = links)', 'default' => 1),
						array('name' => 'vertical', 'type' => 'select', 'label' => 'Position vertikal', 'default' => 'middle', 'options' => self::VERTICAL),
						array('name' => 'horizontal', 'type' => 'select', 'label' => 'Position horizontal', 'default' => 'center', 'options' => self::HORIZONTAL),
						array('name' => 'size', 'type' => 'select', 'label' => 'Größe', 'default' => 'm', 'options' => self::SIZES),
						array('name' => 'link', 'type' => 'url', 'label' => 'Link (optional)')
					)
				),
				self::hoverField('look'),
				self::classesField('')
			)
		);
	}
}
