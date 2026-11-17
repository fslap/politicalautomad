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

namespace Automad\Party\OpenWindow;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component OpenWindow: an open glass front in green. A wall with a row of tall
 * windows and a round window, filled with free, formless glass (organic color fields and
 * flowing lead lines, no symbols), light falling on the floor and text in front of it.
 * A lighter, more open alternative to the StainedGlass section.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class OpenWindow extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const PALETTE_LABELS = array(
		'gruen' => 'Grün (mit allen Farben als Akzent)',
		'bunt' => 'Bunt mit grünem Stich',
		'blau' => 'Blau-grün'
	);

	/**
	 * Palettes: wall top, wall bottom, lead, glass light, glass base, the glass colors
	 * (weighted by repetition, green first) and the light on the floor.
	 */
	const PALETTES = array(
		'gruen' => array(
			'wall' => array('#123029', '#0b1d19'),
			'lead' => '#0a1714',
			'glass' => array('#e4f3e8', '#b9dcc6'),
			'colors' => array('#7fbf9a', '#5aa98a', '#9fd3b0', '#3f8f78', '#c4e3b8', '#7fbf7a', '#3aa5a6', '#f1b9a4', '#e8c27a', '#d9879b', '#9cc3e0', '#a99bd6', '#e98a6b'),
			'light' => 'rgba(190, 236, 210, 0.34)'
		),
		'bunt' => array(
			'wall' => array('#152a2b', '#0c1a1b'),
			'lead' => '#0a1414',
			'glass' => array('#f1f5ec', '#cfe6d6'),
			'colors' => array('#7fbf9a', '#3aa5a6', '#f1b9a4', '#e8c27a', '#d9879b', '#9cc3e0', '#a99bd6', '#e98a6b', '#9fd3b0', '#f2d48a'),
			'light' => 'rgba(236, 224, 200, 0.32)'
		),
		'blau' => array(
			'wall' => array('#132433', '#0b1724'),
			'lead' => '#091320',
			'glass' => array('#e8f1f7', '#bcd6e8'),
			'colors' => array('#9cc3e0', '#6fa6cf', '#3aa5a6', '#a99bd6', '#9fd3b0', '#f1b9a4', '#e8c27a'),
			'light' => 'rgba(200, 225, 245, 0.32)'
		)
	);

	/**
	 * Id of the glass gradient of the current SVG.
	 */
	private string $glassId = '';

	/**
	 * State of the seeded random generator.
	 */
	private int $state = 1;

	public function __construct() {
		parent::__construct(__DIR__, 'openwindow');
	}

	public function context(array $config): array {
		$palette = isset(self::PALETTES[$config['palette']]) ? (string) $config['palette'] : 'gruen';
		$position = in_array($config['text_position'], array('center', 'left', 'right', 'bottom'), true) ? $config['text_position'] : 'bottom';
		$windows = in_array((string) $config['windows'], array('3', '5', '7', '9'), true) ? (int) $config['windows'] : 7;
		$height = preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height']) ?: '100vh';
		$seed = trim((string) $config['seed']);
		$sectionId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']);
		$items = array();

		foreach (array_values(array_filter(array_map(fn ($text) => trim((string) $text), (array) $config['items']), fn ($text) => $text !== '')) as $text) {
			// "Forderung: Erklärung" shows the demand in bold.
			$parts = preg_split('/\s*:\s+/u', $text, 2) ?: array($text);

			$items[] = count($parts) === 2 && mb_strlen($parts[0]) <= 80
				? array('lead' => $parts[0], 'text' => $parts[1])
				: array('lead' => '', 'text' => $text);
		}

		$text = trim((string) $config['text']);
		$this->state = (crc32(($seed === '' || $seed === 'auto') ? ($sectionId ?: 'glasfront') : $seed) & 0x7fffffff) ?: 1;

		return array(
			'section_id' => $sectionId,
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'items' => $items,
			'has_items' => !empty($items),
			'has_content' => $config['title'] !== '' || $text !== '' || !empty($items),
			'svg' => $this->svg(self::PALETTES[$palette], $windows, (bool) $config['rose'], uniqid('gf')),
			'position' => $position,
			'glow' => (bool) $config['glow'],
			'style' => "--gf-height: $height;",
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Offene Glasfront (grün)',
			'icon' => 'window',
			'description' => 'Eine ganze Fensterfront mit grünem Stich: freie, formlose Glasflächen in allen Farben, fließende Bleilinien, Licht auf dem Boden und Text mit Forderungen davor.',
			'fields' => array(
				self::sectionIdField('glasfront'),
				self::titleField('Offen für alle'),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => 'Hell, offen und für alle.'),
				array('name' => 'items', 'type' => 'strings', 'label' => 'Forderungen (eine pro Zeile, „Forderung: Erklärung“ setzt den Anfang fett)', 'default' => array()),
				array('name' => 'palette', 'type' => 'select', 'label' => 'Farben', 'default' => 'gruen', 'options' => self::PALETTE_LABELS),
				array('name' => 'windows', 'type' => 'select', 'label' => 'Fenster in der Front', 'default' => '7', 'options' => array('3' => '3', '5' => '5', '7' => '7', '9' => '9')),
				array('name' => 'rose', 'type' => 'toggle', 'label' => 'Rundes Fenster oben', 'default' => true),
				array('name' => 'seed', 'type' => 'text', 'label' => 'Variante (Startwert, „auto“ = Section-ID)', 'default' => 'auto', 'help' => 'Ein anderes Wort ergibt eine andere, aber feste Komposition.'),
				array('name' => 'text_position', 'type' => 'select', 'label' => 'Text', 'default' => 'bottom', 'options' => array('bottom' => 'Unten vor der Front', 'center' => 'Mitte', 'left' => 'Links', 'right' => 'Rechts')),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe', 'default' => '100vh'),
				array('name' => 'glow', 'type' => 'toggle', 'label' => 'Licht auf dem Boden', 'default' => true),
				self::classesField('')
			)
		);
	}

	/**
	 * A window shape: a pointed or round arch.
	 * @param float $x1
	 * @param float $x2
	 * @param float $top
	 * @param float $bottom
	 * @param string $type
	 */
	private function arch(float $x1, float $x2, float $top, float $bottom, string $type): string {
		$half = ($x2 - $x1) / 2;
		$mid = $x1 + $half;

		if ($type === 'round') {
			$spring = $top + $half;

			return 'M' . self::n($x1) . ' ' . self::n($bottom) . 'V' . self::n($spring) . 'A' . self::n($half) . ' ' . self::n($half) . ' 0 0 1 ' . self::n($x2) . ' ' . self::n($spring) . 'V' . self::n($bottom) . 'Z';
		}

		$radius = $half * 1.6;
		$spring = $top + sqrt($radius * $radius - ($radius - $half) * ($radius - $half));

		return 'M' . self::n($x1) . ' ' . self::n($bottom) . 'V' . self::n($spring)
			. 'A' . self::n($radius) . ' ' . self::n($radius) . ' 0 0 1 ' . self::n($mid) . ' ' . self::n($top)
			. 'A' . self::n($radius) . ' ' . self::n($radius) . ' 0 0 1 ' . self::n($x2) . ' ' . self::n($spring)
			. 'V' . self::n($bottom) . 'Z';
	}

	/**
	 * A closed, smooth organic shape (a formless color field).
	 * @param float $cx
	 * @param float $cy
	 * @param float $rx
	 * @param float $ry
	 */
	private function blob(float $cx, float $cy, float $rx, float $ry): string {
		$points = array();
		$count = 6 + (int) floor($this->random() * 3);
		$offset = $this->random() * M_PI * 2;

		for ($i = 0; $i < $count; $i++) {
			$angle = $offset + $i / $count * M_PI * 2;
			$factor = 0.6 + $this->random() * 0.55;
			$points[] = array($cx + cos($angle) * $rx * $factor, $cy + sin($angle) * $ry * $factor);
		}

		return self::smooth($points, true);
	}

	/**
	 * A glass color: mostly the greens at the start of the list, the other colors as accents.
	 *
	 * @param array $colors
	 * @return string
	 */
	private function color(array $colors): string {
		$greens = min(6, count($colors));

		if ($this->random() < 0.6 || $greens === count($colors)) {
			return $colors[(int) floor($this->random() * $greens)];
		}

		return $colors[$greens + (int) floor($this->random() * (count($colors) - $greens))];
	}

	/**
	 * A flowing lead line across a window.
	 * @param float $x
	 * @param float $y
	 * @param float $width
	 * @param float $height
	 */
	private function flow(float $x, float $y, float $width, float $height): string {
		$points = array();
		$steps = 4;
		$start = $y + $this->random() * $height;
		$slope = ($this->random() - 0.5) * $height * 1.6;

		for ($i = 0; $i <= $steps; $i++) {
			$points[] = array($x - 20 + $i / $steps * ($width + 40), $start + $slope * ($i / $steps - 0.5) + ($this->random() - 0.5) * $height * 0.5);
		}

		return self::smooth($points, false);
	}

	private static function n(float $value): string {
		return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
	}

	/**
	 * The glass of one window: light base glass, formless color fields, flowing lead lines
	 * and the frame.
	 * @param string $shape
	 * @param float $x
	 * @param float $y
	 * @param float $w
	 * @param float $h
	 * @param array $p
	 * @param string $clip
	 * @param string $defs
	 */
	private function pane(string $shape, float $x, float $y, float $w, float $h, array $p, string $clip, string &$defs): string {
		$defs .= '<clipPath id="' . $clip . '"><path d="' . $shape . '"/></clipPath>';
		$svg = '<path d="' . $shape . '" fill="url(#' . $this->glassId . ')"/>';
		$fields = '';
		$amount = 5 + (int) round($h / 110);

		for ($i = 0; $i < $amount; $i++) {
			$color = $this->color($p['colors']);
			$fields .= '<path d="' . $this->blob($x + $this->random() * $w, $y + $this->random() * $h, $w * (0.25 + $this->random() * 0.35), $h * (0.06 + $this->random() * 0.1)) . '" fill="' . $color . '" opacity="' . self::n(0.55 + $this->random() * 0.35) . '"/>';
		}

		$lines = '';

		for ($i = 0; $i < 3 + (int) round($h / 220); $i++) {
			$lines .= '<path d="' . $this->flow($x, $y + $i * $h / (3 + round($h / 220)), $w, $h / (3 + round($h / 220))) . '" fill="none" stroke="' . $p['lead'] . '" stroke-width="3" opacity="0.75"/>';
		}

		$svg .= '<g clip-path="url(#' . $clip . ')">' . $fields . $lines
			. '<line x1="' . self::n($x + $w / 2) . '" y1="' . self::n($y) . '" x2="' . self::n($x + $w / 2) . '" y2="' . self::n($y + $h) . '" stroke="' . $p['lead'] . '" stroke-width="2" opacity="0.35"/></g>';

		return $svg . '<path d="' . $shape . '" fill="none" stroke="' . $p['lead'] . '" stroke-width="9"/>';
	}

	/**
	 * Seeded random number between 0 and 1 (stable compositions for every seed).
	 */
	private function random(): float {
		$this->state = ($this->state * 1103515245 + 12345) & 0x7fffffff;

		return $this->state / 0x7fffffff;
	}

	/**
	 * A smooth path through points (Catmull-Rom as cubic Bézier curves).
	 *
	 * @param array $points
	 * @param bool $closed
	 * @return string
	 */
	private static function smooth(array $points, bool $closed): string {
		$count = count($points);
		$path = 'M' . self::n($points[0][0]) . ' ' . self::n($points[0][1]);
		$last = $closed ? $count : $count - 1;

		for ($i = 0; $i < $last; $i++) {
			$p0 = $points[($i - 1 + $count) % $count];
			$p1 = $points[$i];
			$p2 = $points[($i + 1) % $count];
			$p3 = $points[($i + 2) % $count];

			if (!$closed) {
				$p0 = $points[max(0, $i - 1)];
				$p3 = $points[min($count - 1, $i + 2)];
			}

			$path .= 'C' . self::n($p1[0] + ($p2[0] - $p0[0]) / 6) . ' ' . self::n($p1[1] + ($p2[1] - $p0[1]) / 6)
				. ' ' . self::n($p2[0] - ($p3[0] - $p1[0]) / 6) . ' ' . self::n($p2[1] - ($p3[1] - $p1[1]) / 6)
				. ' ' . self::n($p2[0]) . ' ' . self::n($p2[1]);
		}

		return $path . ($closed ? 'Z' : '');
	}

	/**
	 * Build the glass front.
	 *
	 * @param array $p
	 * @param int $count
	 * @param bool $rose
	 * @param string $id
	 * @return string
	 */
	private function svg(array $p, int $count, bool $rose, string $id): string {
		$width = 1600;
		$height = 1000;
		$floor = 820;
		$gap = 34;
		$side = 90;
		$windowWidth = ($width - 2 * $side - ($count - 1) * $gap) / $count;
		$defs = '<linearGradient id="' . $id . 'w" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $p['wall'][0] . '"/><stop offset="1" stop-color="' . $p['wall'][1] . '"/></linearGradient>'
			. '<linearGradient id="' . $id . 'g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $p['glass'][0] . '"/><stop offset="1" stop-color="' . $p['glass'][1] . '"/></linearGradient>'
			. '<filter id="' . $id . 'b" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="14"/></filter>'
			. '<radialGradient id="' . $id . 'l" cx="0.5" cy="0.5" r="0.5"><stop offset="0" stop-color="' . $p['light'] . '"/><stop offset="1" stop-color="rgba(0,0,0,0)"/></radialGradient>';
		$this->glassId = $id . 'g';
		$glass = '';
		$light = '';
		$middle = ($count - 1) / 2;

		for ($i = 0; $i < $count; $i++) {
			// The middle windows are higher, the outer ones lower.
			$distance = abs($i - $middle) / max(1, $middle);
			$x = $side + $i * ($windowWidth + $gap);
			$top = ($rose ? 330 : 170) + $distance * 140 + ($i === (int) $middle && !$rose ? -40 : 0);

			if ($rose && abs($i - $middle) < 1) {
				$top = 380;
			}

			$shape = $this->arch($x, $x + $windowWidth, $top, $floor - 40, $i % 3 === 1 ? 'round' : 'pointed');
			$glass .= $this->pane($shape, $x, $top, $windowWidth, $floor - 40 - $top, $p, $id . 'c' . $i, $defs);
			// Colored light falling through the glass onto the floor.
			$light .= '<ellipse cx="' . self::n($x + $windowWidth / 2) . '" cy="' . ($floor + 70) . '" rx="' . self::n($windowWidth * 0.95) . '" ry="70" fill="url(#' . $id . 'l)"/>';

			for ($k = 0; $k < 2; $k++) {
				$light .= '<ellipse cx="' . self::n($x + $windowWidth * (0.25 + $this->random() * 0.5)) . '" cy="' . self::n($floor + 50 + $this->random() * 80) . '" rx="' . self::n($windowWidth * (0.25 + $this->random() * 0.3)) . '" ry="' . self::n(18 + $this->random() * 20) . '" fill="' . $this->color($p['colors']) . '" opacity="0.16" filter="url(#' . $id . 'b)"/>';
			}
		}

		if ($rose) {
			$cx = $width / 2;
			$cy = 200;
			$r = 120;
			$circle = 'M' . self::n($cx - $r) . ' ' . $cy . 'A' . $r . ' ' . $r . ' 0 1 0 ' . self::n($cx + $r) . ' ' . $cy . 'A' . $r . ' ' . $r . ' 0 1 0 ' . self::n($cx - $r) . ' ' . $cy . 'Z';
			$glass .= $this->pane($circle, $cx - $r, $cy - $r, 2 * $r, 2 * $r, $p, $id . 'r', $defs);
		}

		return '<svg class="glass-front__svg" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMax slice" role="img" aria-label="Glasfront">'
			. '<defs>' . $defs . '</defs>'
			. '<rect width="' . $width . '" height="' . $height . '" fill="url(#' . $id . 'w)"/>'
			. '<rect y="' . $floor . '" width="' . $width . '" height="' . ($height - $floor) . '" fill="' . $p['wall'][1] . '"/>'
			. '<line x1="0" y1="' . $floor . '" x2="' . $width . '" y2="' . $floor . '" stroke="' . $p['lead'] . '" stroke-width="4" opacity="0.6"/>'
			. '<g class="glass-front__light">' . $light . '</g>'
			. $glass
			. '</svg>';
	}
}
