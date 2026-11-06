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
 * Breaker aus Manga-Panels (z.B. Augenpartien) mit unterschiedlicher Länge, schrägen Schnitten und Bildausschnitt pro Panel.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class GazeBreaker extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * Lengths of panels (relative widths).
	 */
	const LENGTHS = array(
		'1' => 'Schmal (1)',
		'2' => 'Normal (2)',
		'3' => 'Lang (3)',
		'4' => 'Sehr lang (4)',
		'6' => 'Breitbild (6)'
	);

	public function __construct() {
		parent::__construct(__DIR__, 'gazebreaker');
	}

	public function context(array $config): array {
		$rows = max(1, min(3, (int) $config['rows'] ?: 1));
		$slant = (string) $config['slant'];
		$all = array_values(array_filter(is_array($config['items']) ? $config['items'] : array(), 'is_array'));
		$items = array_values(array_filter($all, fn ($item) => !empty($item['image'])));

		// Without images empty panels show the layout (e.g. in the editor preview).
		if (!$items) {
			$items = $all ?: array_map(fn ($length) => array('length' => $length), array(2, 1, 3, 1, 2, 4, 1));
		}

		// Distribute the panels over the rows by their length.
		$total = array_sum(array_map(fn ($item) => self::length($item), $items));
		$perRow = $total / $rows;
		$out = array(array());
		$sum = 0;

		foreach ($items as $index => $item) {
			$length = self::length($item);

			if ($sum >= $perRow * count($out) - 0.001 && count($out) < $rows) {
				$out[] = array();
			}

			$sum += $length;
			$angle = $slant === 'alt' ? ($index % 2 ? -6 : 6) : (int) $slant;
			$fx = max(0, min(100, (int) ($item['focus_x'] ?? 50)));
			$fy = max(0, min(100, (int) ($item['focus_y'] ?? 35)));
			$zoom = max(1, min(3, (float) ($item['zoom'] ?? 1)));

			$out[count($out) - 1][] = array(
				'image' => (string) ($item['image'] ?? ''),
				'alt' => (string) ($item['alt'] ?? ''),
				'caption' => trim((string) ($item['caption'] ?? '')),
				'link' => trim((string) ($item['link'] ?? '')),
				'style' => "--length: $length; --skew: {$angle}deg; --fx: $fx%; --fy: $fy%; --zoom: $zoom;"
			);
		}

		$height = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '160px';
		$gutter = in_array($config['gutter'], array('s', 'm', 'l'), true) ? $config['gutter'] : 'm';
		$frame = in_array($config['frame'], array('ink', 'thin', 'none'), true) ? $config['frame'] : 'ink';
		$background = in_array($config['background'], array('paper', 'ink', 'none'), true) ? $config['background'] : 'paper';
		$animation = in_array($config['hover_animation'], array('zoom', 'tilt', 'move', 'float', 'wobble'), true) ? $config['hover_animation'] : '';

		return array(
			'section_id' => $config['section_id'],
			'has_rows' => true,
			'rows' => array_map(fn ($panels) => array('panels' => $panels), $out),
			'style' => "--panel-height: $height;",
			'classes' => trim(
				"panel-strip panel-strip--gutter-$gutter panel-strip--frame-$frame panel-strip--bg-$background"
				. ($slant !== '0' ? ' panel-strip--slanted' : '')
				. ((bool) $config['grayscale'] ? ' panel-strip--grayscale' : '')
				. ($animation !== '' ? " party-hover party-hover--$animation" : '')
				. ' ' . $this->getClasses($config)
			)
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'section_id', 'type' => 'text', 'label' => 'Section-ID (Anker)', 'default' => ''),
			array(
				'name' => 'items',
				'type' => 'list',
				'label' => 'Panels (von links nach rechts)',
				'addLabel' => 'Panel hinzufügen',
				'itemTitle' => 'alt',
				'default' => array(),
				'fields' => array(
					array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
					array('name' => 'alt', 'type' => 'text', 'label' => 'Beschreibung (Alt-Text)'),
					array('name' => 'length', 'type' => 'select', 'label' => 'Länge des Panels', 'default' => '2', 'options' => self::LENGTHS),
					array('name' => 'focus_y', 'type' => 'select', 'label' => 'Bildausschnitt vertikal', 'default' => '35', 'options' => array('0' => 'Oben', '20' => 'Oberes Drittel', '35' => 'Augenhöhe', '50' => 'Mitte', '75' => 'Unteres Drittel', '100' => 'Unten')),
					array('name' => 'focus_x', 'type' => 'select', 'label' => 'Bildausschnitt horizontal', 'default' => '50', 'options' => array('0' => 'Links', '25' => 'Links-Mitte', '50' => 'Mitte', '75' => 'Rechts-Mitte', '100' => 'Rechts')),
					array('name' => 'zoom', 'type' => 'select', 'label' => 'Zoom', 'default' => '1', 'options' => array('1' => '100 %', '1.25' => '125 %', '1.5' => '150 %', '2' => '200 %', '2.5' => '250 %', '3' => '300 %')),
					array('name' => 'caption', 'type' => 'text', 'label' => 'Sprechblase / Text (optional)'),
					array('name' => 'link', 'type' => 'url', 'label' => 'Link (optional)')
				)
			),
			array('name' => 'rows', 'type' => 'select', 'label' => 'Reihen', 'default' => '1', 'options' => array('1' => '1 Reihe', '2' => '2 Reihen', '3' => '3 Reihen')),
			array('name' => 'height', 'type' => 'text', 'label' => 'Höhe einer Reihe', 'default' => '160px'),
			array('name' => 'slant', 'type' => 'select', 'label' => 'Schnitte', 'default' => '6', 'options' => array('0' => 'Gerade', '4' => 'Leicht schräg', '6' => 'Schräg (Manga)', '10' => 'Stark schräg', 'alt' => 'Abwechselnd schräg')),
			array('name' => 'gutter', 'type' => 'select', 'label' => 'Zwischenraum', 'default' => 'm', 'options' => array('s' => 'Schmal', 'm' => 'Mittel', 'l' => 'Breit')),
			array('name' => 'frame', 'type' => 'select', 'label' => 'Rahmen', 'default' => 'ink', 'options' => array('ink' => 'Schwarz (Tusche)', 'thin' => 'Dünn', 'none' => 'Ohne')),
			array('name' => 'background', 'type' => 'select', 'label' => 'Hintergrund', 'default' => 'paper', 'options' => array('paper' => 'Papier (weiß)', 'ink' => 'Schwarz', 'none' => 'Transparent')),
			array('name' => 'grayscale', 'type' => 'toggle', 'label' => 'Schwarz-Weiß (Manga)', 'default' => false),
			array(
				'name' => 'hover_animation',
				'type' => 'select',
				'label' => 'Animation beim Darüberfahren (Hover)',
				'default' => 'zoom',
				'options' => array('' => 'Keine', 'zoom' => 'Heranzoomen', 'tilt' => 'Kippen', 'move' => 'Bewegen', 'float' => 'Schweben', 'wobble' => 'Wackeln')
			),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Panel-Streifen (Manga)',
			'icon' => 'grid-1x2',
			'description' => 'Breaker aus Manga-Panels (z.B. Augenpartien) mit unterschiedlicher Länge, schrägen Schnitten und Bildausschnitt pro Panel.',
			'fields' => self::elementFields()
		);
	}

	/**
	 * The length of a panel.
	 *
	 * @param array $item
	 * @return int
	 */
	private static function length(array $item): int {
		$length = (int) ($item['length'] ?? 2);

		return isset(self::LENGTHS[(string) $length]) ? $length : 2;
	}
}
