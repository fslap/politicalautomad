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

namespace Automad\Party\HouseRow;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Frei gestaltbare Häuser (Wohnblock, Satteldach, Laden, Hochhaus, Kuppel, Kirche, Fernsehturm, Fabrik, Bahnhof) oder eine zufällige Skyline, optional hereinrollend.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class HouseRow extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	/**
	 * Colors of the houses (CSS variables of the theme).
	 */
	const COLORS = array(
		'' => 'Textfarbe',
		'blue' => 'Blau',
		'orange' => 'Terrakotta',
		'purple' => 'Aubergine',
		'green' => 'Grün',
		'gold' => 'Ocker',
		'red' => 'Rot',
		'grey' => 'Grau'
	);

	/**
	 * House types.
	 */
	const TYPES = array(
		'flat' => 'Wohnblock (Flachdach)',
		'gable' => 'Haus mit Satteldach',
		'hut' => 'Kleines Haus',
		'shop' => 'Laden mit Markise',
		'tower' => 'Hochhaus mit Spitze',
		'dome' => 'Kuppelbau',
		'church' => 'Kirche mit Turm',
		'tvtower' => 'Fernsehturm',
		'factory' => 'Fabrik mit Schornstein',
		'station' => 'Bahnhof'
	);

	public function __construct() {
		parent::__construct(__DIR__, 'houserow');
	}

	public function context(array $config): array {
		$style = in_array($config['style'], array('line', 'filled', 'mixed'), true) ? $config['style'] : 'line';
		$houses = $config['mode'] === 'random'
			? self::randomHouses((string) $config['seed'], max(1, min(40, (int) $config['count'] ?: 12)))
			: array_values(array_filter(is_array($config['houses']) ? $config['houses'] : array(), 'is_array'));

		$roll = in_array($config['roll_in'], array('', 'hover', 'view', 'always'), true) ? $config['roll_in'] : '';
		$align = in_array($config['align'], array('left', 'center', 'right', 'spread'), true) ? $config['align'] : 'left';
		$height = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '220px';

		return array_merge(array(
			'section_id' => $config['section_id'],
			'svg' => self::svg($houses, $style, (int) $config['gap'], (bool) $config['ground'], $align),
			'style' => "--houses-height: $height;",
			'classes' => trim("house-row house-row--$style house-row--align-$align" . ($roll !== '' ? " house-row--roll house-row--roll-$roll" : '') . ' ' . $this->getClasses($config)),
			'roll_view' => $roll === 'view'
		), self::hoverContext($config));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'section_id', 'type' => 'text', 'label' => 'Section-ID (Anker)', 'default' => ''),
			array('name' => 'mode', 'type' => 'select', 'label' => 'Häuser', 'default' => 'manual', 'options' => array('manual' => 'Selbst gestalten (Liste)', 'random' => 'Zufällige Skyline')),
			array(
				'name' => 'houses',
				'type' => 'list',
				'label' => 'Häuser (von links nach rechts)',
				'addLabel' => 'Haus hinzufügen',
				'itemTitle' => 'type',
				'default' => array(
					array('type' => 'flat', 'width' => 5, 'floors' => 5, 'windows' => 3, 'door' => true, 'color' => 'orange', 'details' => true),
					array('type' => 'gable', 'width' => 4, 'floors' => 3, 'windows' => 2, 'door' => true, 'color' => 'green', 'details' => true),
					array('type' => 'flat', 'width' => 6, 'floors' => 7, 'windows' => 4, 'door' => true, 'color' => 'blue', 'details' => true)
				),
				'fields' => array(
					array('name' => 'type', 'type' => 'select', 'label' => 'Typ', 'default' => 'flat', 'options' => self::TYPES),
					array('name' => 'width', 'type' => 'number', 'label' => 'Breite (1 – 20)', 'default' => 4),
					array('name' => 'floors', 'type' => 'number', 'label' => 'Stockwerke (Höhe)', 'default' => 3),
					array('name' => 'windows', 'type' => 'number', 'label' => 'Fenster pro Stockwerk', 'default' => 2),
					array('name' => 'door', 'type' => 'toggle', 'label' => 'Tür', 'default' => true),
					array('name' => 'details', 'type' => 'toggle', 'label' => 'Details (Antennen, Schornstein, Kreuz, Rauch …)', 'default' => true),
					array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => self::COLORS),
					array('name' => 'label', 'type' => 'text', 'label' => 'Beschriftung (Tooltip)'),
					array('name' => 'link', 'type' => 'url', 'label' => 'Link')
				)
			),
			array('name' => 'seed', 'type' => 'text', 'label' => 'Zufall: Startwert (gleicher Wert = gleiche Skyline)', 'default' => 'fsl'),
			array('name' => 'count', 'type' => 'number', 'label' => 'Zufall: Anzahl Häuser', 'default' => 12),
			array('name' => 'style', 'type' => 'select', 'label' => 'Stil', 'default' => 'line', 'options' => array('line' => 'Strichzeichnung (wie die Skizze)', 'filled' => 'Flächig', 'mixed' => 'Gemischt')),
			array('name' => 'gap', 'type' => 'number', 'label' => 'Abstand zwischen den Häusern (0 – 10)', 'default' => 1),
			array('name' => 'height', 'type' => 'text', 'label' => 'Höhe', 'default' => '220px'),
			array('name' => 'align', 'type' => 'select', 'label' => 'Ausrichtung', 'default' => 'left', 'options' => array('left' => 'Links', 'center' => 'Mitte', 'right' => 'Rechts', 'spread' => 'Ganze Breite (gestreckt)')),
			array('name' => 'ground', 'type' => 'toggle', 'label' => 'Bodenlinie', 'default' => true),
			array('name' => 'roll_in', 'type' => 'select', 'label' => 'Hereinrollen', 'default' => '', 'options' => array('' => 'Nein', 'hover' => 'Beim Darüberfahren (Hover)', 'view' => 'Beim Hineinscrollen', 'always' => 'Einmal beim Laden')),
			self::hoverField('tilt'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	/**
	 * A random but stable skyline.
	 *
	 * @param string $seed
	 * @param int $count
	 * @return array
	 */
	public static function randomHouses(string $seed, int $count): array {
		$seed = trim($seed) === '' ? 'fsl' : $seed;
		$rng = new \Random\Randomizer(new \Random\Engine\Mt19937(is_numeric($seed) ? (int) $seed : crc32($seed)));
		$types = array('flat', 'flat', 'flat', 'gable', 'gable', 'hut', 'shop', 'tower', 'dome', 'church', 'tvtower', 'factory');
		$colors = array_keys(self::COLORS);
		$houses = array();

		for ($i = 0; $i < $count; $i++) {
			$type = $types[$rng->getInt(0, count($types) - 1)];

			$houses[] = array(
				'type' => $type,
				'width' => match ($type) {
					'tower', 'tvtower' => $rng->getInt(2, 3),
					'church', 'factory', 'station' => $rng->getInt(5, 7),
					'hut' => 3,
					default => $rng->getInt(3, 6)
				},
				'floors' => match ($type) {
					'tower' => $rng->getInt(8, 12),
					'tvtower' => 12,
					'hut' => 1,
					'church', 'dome' => $rng->getInt(3, 4),
					default => $rng->getInt(2, 6)
				},
				'windows' => $rng->getInt(2, 4),
				'door' => $rng->getInt(0, 100) < 60,
				'color' => $rng->getInt(0, 100) < 30 ? $colors[$rng->getInt(1, count($colors) - 1)] : '',
				'details' => $rng->getInt(0, 100) < 50
			);
		}

		return $houses;
	}

	/**
	 * Draw the houses as one SVG. One unit is 20 px of the drawing, a floor is 1.4 units high.
	 *
	 * @param array $houses
	 * @param string $style
	 * @param int $gap
	 * @param bool $ground
	 * @param string $align
	 * @return string
	 */
	public static function svg(array $houses, string $style, int $gap, bool $ground, string $align): string {
		$u = 20;
		$floor = 28;
		$gapPx = max(0, min(10, $gap)) * $u / 2;
		$x = 0;
		$top = 0;
		$parts = array();

		foreach (array_values($houses) as $index => $house) {
			$type = isset(self::TYPES[$house['type'] ?? '']) ? $house['type'] : 'flat';
			$w = max(1, min(20, (int) ($house['width'] ?? 4))) * $u;
			$floors = max(1, min(30, (int) ($house['floors'] ?? 3)));
			$h = $floors * $floor + 10;
			$windows = max(0, min(8, (int) ($house['windows'] ?? 2)));
			$color = isset(self::COLORS[$house['color'] ?? '']) ? (string) $house['color'] : '';
			$details = !isset($house['details']) || (bool) $house['details'];
			$door = !isset($house['door']) || (bool) $house['door'];
			$label = trim((string) ($house['label'] ?? ''));
			$link = trim((string) ($house['link'] ?? ''));

			[$body, $extra] = self::house($type, $w, $h, $floors, $windows, $door, $details);
			$top = max($top, $h + $extra);

			// <title> with an attribute: Automad only adds the page title when no plain <title> tag exists.
			$title = $label !== '' ? '<title class="house__label">' . htmlspecialchars($label) . '</title>' : '';
			$group = '<g class="house party-hover-item' . ($color !== '' ? " house--$color" : '') . '" style="--i: ' . $index . ';" transform="translate(' . $x . ' 0)">' . $title . $body . '</g>';

			if ($link !== '') {
				$group = '<a href="' . htmlspecialchars($link) . '">' . $group . '</a>';
			}

			$parts[] = array('width' => $w, 'svg' => $group);
			$x += $w + $gapPx;
		}

		if (!$parts) {
			return '';
		}

		$width = max(1, $x - $gapPx);
		$height = $top + 6;
		$content = '';

		foreach ($parts as $part) {
			$content .= $part['svg'];
		}

		// Houses are drawn upwards from y = 0, the view box moves the ground to the bottom.
		$groundLine = $ground ? '<path class="house-row__ground" d="M-40 0 H' . ($width + 40) . '"/>' : '';
		$aspect = match ($align) {
			'center' => 'xMidYMax meet',
			'right' => 'xMaxYMax meet',
			'spread' => 'none',
			default => 'xMinYMax meet'
		};

		return '<svg class="house-row__svg house-row__svg--' . $style . '" viewBox="-4 ' . (-$height) . ' ' . ($width + 8) . ' ' . ($height + 4) . '" preserveAspectRatio="' . $aspect . '" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Häuser">'
			. $content . $groundLine . '</svg>';
	}

	protected function definition(): array {
		return array(
			'title' => 'Häuserzeile / Skyline',
			'icon' => 'buildings',
			'description' => 'Frei gestaltbare Häuser (Wohnblock, Satteldach, Laden, Hochhaus, Kuppel, Kirche, Fernsehturm, Fabrik, Bahnhof) oder eine zufällige Skyline, optional hereinrollend.',
			'fields' => self::elementFields()
		);
	}

	/**
	 * Draw one house. Returns the SVG and the height above the body (roof, spire, …).
	 *
	 * @param string $type
	 * @param int $w
	 * @param int $h
	 * @param int $floors
	 * @param int $windows
	 * @param bool $door
	 * @param bool $details
	 * @return array{0: string, 1: int}
	 */
	private static function house(string $type, int $w, int $h, int $floors, int $windows, bool $door, bool $details): array {
		$body = fn (int $bw, int $bh, int $bx = 0) => '<rect class="house__body" x="' . $bx . '" y="' . (-$bh) . '" width="' . $bw . '" height="' . $bh . '"/>';
		$out = '';
		$extra = 0;

		switch ($type) {
			case 'gable':
			case 'hut':
				$roof = (int) round($w * 0.45);
				$out .= $body($w, $h);
				$out .= '<path class="house__roof" d="M-6 ' . (-$h) . ' L' . ($w / 2) . ' ' . (-$h - $roof) . ' L' . ($w + 6) . ' ' . (-$h) . ' Z"/>';

				if ($details) {
					$cx = (int) round($w * 0.7);
					$out .= '<rect class="house__body" x="' . $cx . '" y="' . (-$h - $roof * 0.8) . '" width="10" height="' . round($roof * 0.5) . '"/>';
				}

				$out .= self::windows(0, $w, $h, $floors, max(1, $windows), $door);
				$extra = $roof;

				break;

			case 'shop':
				$out .= $body($w, $h);
				$out .= self::windows(0, $w, $h - $h / max(1, $floors), max(0, $floors - 1), $windows, false, (int) round($h / max(1, $floors)));
				// Shop window and an awning with stripes.
				$sh = min(30, (int) round($h / max(1, $floors)) + 4);
				$out .= '<rect class="house__window house__window--shop" x="6" y="' . (-$sh + 2) . '" width="' . ($w - 30) . '" height="' . ($sh - 8) . '"/>';
				$out .= '<rect class="house__door" x="' . ($w - 18) . '" y="-22" width="12" height="22"/>';
				$stripes = max(2, (int) floor($w / 12));
				$aw = '';

				for ($i = 0; $i < $stripes; $i++) {
					$aw .= '<path class="house__awning' . ($i % 2 ? ' house__awning--alt' : '') . '" d="M' . round($i * $w / $stripes) . ' ' . (-$sh - 8) . ' h' . round($w / $stripes) . ' l-2 10 h-' . (round($w / $stripes) - 4) . ' Z"/>';
				}

				$out .= $aw;

				break;

			case 'tower':
				$spire = 40;
				$out .= $body($w, $h);
				$out .= '<path class="house__roof" d="M0 ' . (-$h) . ' L' . ($w / 2) . ' ' . (-$h - $spire) . ' L' . $w . ' ' . (-$h) . ' Z"/>';
				$out .= '<path class="house__line" d="M' . ($w / 2) . ' ' . (-$h - $spire) . ' v-18"/>';
				$out .= self::windows(0, $w, $h, $floors, max(1, min(3, $windows)), $door);
				$extra = $spire + 18;

				break;

			case 'dome':
				$r = (int) round($w * 0.38);
				$out .= $body($w, $h);
				$out .= '<path class="house__roof" d="M' . ($w / 2 - $r) . ' ' . (-$h) . ' A' . $r . ' ' . $r . ' 0 0 1 ' . ($w / 2 + $r) . ' ' . (-$h) . ' Z"/>';

				if ($details) {
					$out .= '<path class="house__line" d="M' . ($w / 2) . ' ' . (-$h - $r) . ' v-22 M' . ($w / 2 - 7) . ' ' . (-$h - $r - 15) . ' h14"/>';
				}

				// Columns at the entrance.
				for ($c = 1; $c <= 3; $c++) {
					$out .= '<path class="house__line" d="M' . round($w * $c / 4) . ' 0 V' . round(-$h * 0.45) . '"/>';
				}

				$out .= '<path class="house__line" d="M4 ' . round(-$h * 0.45) . ' H' . ($w - 4) . '"/>';
				$extra = $r + ($details ? 22 : 0);

				break;

			case 'church':
				$tw = max(20, (int) round($w * 0.32));
				$th = $h + 50;
				$spire = 46;
				$out .= $body($w - $tw, $h, $tw);
				$out .= '<path class="house__roof" d="M' . ($tw - 4) . ' ' . (-$h) . ' L' . ($tw + ($w - $tw) / 2) . ' ' . (-$h - 30) . ' L' . ($w + 4) . ' ' . (-$h) . ' Z"/>';
				$out .= $body($tw, $th);
				$out .= '<path class="house__roof" d="M-3 ' . (-$th) . ' L' . ($tw / 2) . ' ' . (-$th - $spire) . ' L' . ($tw + 3) . ' ' . (-$th) . ' Z"/>';
				$out .= '<path class="house__line" d="M' . ($tw / 2) . ' ' . (-$th - $spire) . ' v-16 M' . ($tw / 2 - 6) . ' ' . (-$th - $spire - 10) . ' h12"/>';
				$out .= '<circle class="house__window" cx="' . ($tw / 2) . '" cy="' . (-$th + 20) . '" r="6"/>';
				// Tall church windows with round arches.
				$cw = $w - $tw;
				$n = max(1, min(4, (int) floor($cw / 22)));

				for ($i = 0; $i < $n; $i++) {
					$wx = $tw + round(($i + 0.5) * $cw / $n) - 5;
					$out .= '<path class="house__window" d="M' . $wx . ' ' . round(-$h * 0.25) . ' V' . round(-$h * 0.7) . ' a5 5 0 0 1 10 0 V' . round(-$h * 0.25) . ' Z"/>';
				}

				if ($door) {
					$out .= '<path class="house__door" d="M' . ($tw / 2 - 7) . ' 0 V-16 a7 7 0 0 1 14 0 V0 Z"/>';
				}

				$extra = 50 + $spire + 16;

				break;

			case 'tvtower':
				$cx = $w / 2;
				$out .= '<path class="house__body" d="M' . ($cx - 6) . ' 0 L' . ($cx - 3) . ' ' . (-$h) . ' H' . ($cx + 3) . ' L' . ($cx + 6) . ' 0 Z"/>';
				$out .= '<circle class="house__roof" cx="' . $cx . '" cy="' . round(-$h * 0.78) . '" r="' . max(9, (int) round($w * 0.38)) . '"/>';
				$out .= '<path class="house__line" d="M' . ($cx - max(9, $w * 0.38)) . ' ' . round(-$h * 0.78) . ' H' . ($cx + max(9, $w * 0.38)) . '"/>';
				$out .= '<path class="house__line" d="M' . $cx . ' ' . (-$h) . ' v-30"/>';
				$extra = 30;

				break;

			case 'factory':
				$teeth = max(2, (int) floor($w / 26));
				$tw = $w / $teeth;
				$out .= $body($w, $h);
				$saw = 'M0 ' . (-$h);

				for ($i = 0; $i < $teeth; $i++) {
					$saw .= ' L' . round($i * $tw) . ' ' . (-$h - 16) . ' L' . round(($i + 1) * $tw) . ' ' . (-$h);
				}

				$out .= '<path class="house__roof" d="' . $saw . ' Z"/>';
				$chx = (int) round($w * 0.78);
				$out .= '<rect class="house__body" x="' . $chx . '" y="' . (-$h - 60) . '" width="12" height="60"/>';

				if ($details) {
					$out .= '<g class="house__smoke"><circle cx="' . ($chx + 8) . '" cy="' . (-$h - 72) . '" r="7"/><circle cx="' . ($chx + 20) . '" cy="' . (-$h - 86) . '" r="9"/><circle cx="' . ($chx + 36) . '" cy="' . (-$h - 98) . '" r="11"/></g>';
				}

				$out .= self::windows(0, $w, $h, 1, max(2, $windows + 1), $door);
				$extra = $details ? 110 : 60;

				break;

			case 'station':
				$out .= $body($w, $h);
				$out .= '<path class="house__roof" d="M-8 ' . (-$h) . ' Q' . ($w / 2) . ' ' . (-$h - 34) . ' ' . ($w + 8) . ' ' . (-$h) . ' Z"/>';
				$out .= '<circle class="house__window" cx="' . ($w / 2) . '" cy="' . (-$h + 16) . '" r="8"/>';
				$out .= '<path class="house__line" d="M' . ($w / 2) . ' ' . (-$h + 16) . ' v-5 M' . ($w / 2) . ' ' . (-$h + 16) . ' h4"/>';
				$n = max(2, min(6, (int) floor($w / 24)));

				for ($i = 0; $i < $n; $i++) {
					$ax = round(($i + 0.5) * $w / $n) - 8;
					$out .= '<path class="house__window" d="M' . $ax . ' 0 V' . round(-$h * 0.45) . ' a8 8 0 0 1 16 0 V0"/>';
				}

				$extra = 34;

				break;

			default:
				// Block with flat roof, parapet and antennas.
				$out .= $body($w, $h);
				$out .= '<path class="house__line" d="M-3 ' . (-$h) . ' H' . ($w + 3) . '"/>';

				if ($details) {
					$out .= '<path class="house__line" d="M' . round($w * 0.25) . ' ' . (-$h) . ' v-18 m-6 0 l6 6 l6 -6 M' . round($w * 0.7) . ' ' . (-$h) . ' v-10 h6"/>';
					$extra = 16;
				}

				$out .= self::windows(0, $w, $h, $floors, $windows, $door);
		}

		return array($out, $extra);
	}

	/**
	 * A grid of windows and a door.
	 *
	 * @param int $x
	 * @param int $w
	 * @param float $h
	 * @param int $floors
	 * @param int $windows
	 * @param bool $door
	 * @param int $bottom free space at the bottom
	 * @return string
	 */
	private static function windows(int $x, int $w, float $h, int $floors, int $windows, bool $door, int $bottom = 0): string {
		if ($floors < 1 || $windows < 1) {
			return $door ? '<rect class="house__door" x="' . ($x + $w / 2 - 7) . '" y="-22" width="14" height="22"/>' : '';
		}

		$out = '';
		$fh = ($h - 10 - $bottom) / $floors;
		$ww = min(16, ($w - 8) / $windows - 6);
		$wh = min(16, $fh - 10);

		if ($ww < 3 || $wh < 3) {
			return '';
		}

		for ($f = 0; $f < $floors; $f++) {
			// The ground floor has the door in the middle.
			for ($i = 0; $i < $windows; $i++) {
				$cx = $x + 4 + ($i + 0.5) * ($w - 8) / $windows;
				$cy = -$bottom - ($f + 0.5) * $fh - 4;

				if ($f === 0 && $door && abs($cx - ($x + $w / 2)) < $ww) {
					continue;
				}

				$out .= '<rect class="house__window" x="' . round($cx - $ww / 2, 1) . '" y="' . round($cy - $wh / 2, 1) . '" width="' . round($ww, 1) . '" height="' . round($wh, 1) . '"/>';
			}
		}

		if ($door) {
			$out .= '<rect class="house__door" x="' . round($x + $w / 2 - 7, 1) . '" y="' . (-$bottom - 24) . '" width="14" height="24"/>';
		}

		return $out;
	}
}
