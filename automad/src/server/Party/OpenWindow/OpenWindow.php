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
 * Party component OpenWindow: a simple, open church window (Gothic tracery with rosettes,
 * flower glass in the lancet heads and large clear panes that show the view outside).
 * A lighter alternative to the StainedGlass section.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class OpenWindow extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const PALETTE_LABELS = array(
		'evangelisch' => 'Evangelisch offen (Pfirsich, Creme, Türkis)',
		'blau' => 'Hellblau',
		'gruen' => 'Hellgrün'
	);

	/**
	 * The glass palettes: frame, cream, petal, center, accent, sky top, sky bottom.
	 */
	const PALETTES = array(
		'evangelisch' => array('#1a1d24', '#f6ecdf', '#f1b9a4', '#3aa5a6', '#d9879b', '#d8e8f1', '#f5ece6'),
		'blau' => array('#141b26', '#eef3f7', '#9cc3e0', '#2f6f9f', '#e1b45a', '#d6e6f2', '#eef2f6'),
		'gruen' => array('#151d18', '#f2f1e4', '#b9d7a6', '#3f7a52', '#e5a46b', '#dfeee2', '#f3f1e8')
	);

	public function __construct() {
		parent::__construct(__DIR__, 'openwindow');
	}

	public function context(array $config): array {
		$palette = isset(self::PALETTES[$config['palette']]) ? (string) $config['palette'] : 'evangelisch';
		$layout = in_array($config['layout'], array('left', 'right', 'center'), true) ? $config['layout'] : 'left';
		$lancets = max(2, min(4, (int) $config['lancets']));
		$items = array();

		foreach (array_values(array_filter(array_map(fn ($text) => trim((string) $text), (array) $config['items']), fn ($text) => $text !== '')) as $text) {
			// "Forderung: Erklärung" shows the demand in bold.
			$parts = preg_split('/\s*:\s+/u', $text, 2) ?: array($text);

			$items[] = count($parts) === 2 && mb_strlen($parts[0]) <= 80
				? array('lead' => $parts[0], 'text' => $parts[1])
				: array('lead' => '', 'text' => $text);
		}

		$text = trim((string) $config['text']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'items' => $items,
			'has_items' => !empty($items),
			'has_content' => $config['title'] !== '' || $text !== '' || !empty($items),
			'view_image' => trim((string) $config['view_image']),
			'svg' => self::svg($palette, $lancets, (bool) $config['side_window'], uniqid('ow')),
			'layout' => $layout,
			'side' => (bool) $config['side_window'] ? 'with-side' : 'single',
			'has_image' => trim((string) $config['view_image']) !== '',
			'glow' => (bool) $config['glow'],
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * Build the window as SVG. The wall around the window is drawn as a frame with the window
	 * cut out, so an image behind the SVG shows through the clear panes.
	 *
	 * @param string $palette
	 * @param int $lancets
	 * @param bool $side
	 * @param string $id
	 * @return string
	 */
	public static function svg(string $palette, int $lancets, bool $side, string $id): string {
		[$frame, $cream, $petal, $center, $accent, $skyTop, $skyBottom] = self::PALETTES[$palette] ?? self::PALETTES['evangelisch'];
		$width = $side ? 820 : 600;
		$left = 40;
		$right = 560;
		$spring = 360;
		$bottom = 880;
		$radius = 360;

		$arch = self::archPath($left, $right, $spring, $bottom, $radius);
		$parts = array();

		// Wall with the window opening(s) cut out.
		$cutouts = $arch;
		$sideArch = '';

		if ($side) {
			$sideArch = self::archPath(660, 760, 560, $bottom, 90);
			$cutouts .= ' ' . $sideArch;
		}

		$defs = '<defs>'
			. '<clipPath id="' . $id . 'c"><path d="' . $arch . '"/></clipPath>'
			. '<linearGradient id="' . $id . 's" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $skyTop . '"/><stop offset="0.6" stop-color="' . $skyBottom . '"/><stop offset="1" stop-color="#ead0c8"/></linearGradient>'
			. '<pattern id="' . $id . 'p" width="34" height="34" patternUnits="userSpaceOnUse">'
			. '<rect width="34" height="34" fill="' . $cream . '"/>'
			. '<g fill="' . $petal . '" stroke="' . $frame . '" stroke-width="1.4">'
			. '<ellipse cx="17" cy="9" rx="5" ry="7"/><ellipse cx="17" cy="25" rx="5" ry="7"/><ellipse cx="9" cy="17" rx="7" ry="5"/><ellipse cx="25" cy="17" rx="7" ry="5"/>'
			. '</g><circle cx="17" cy="17" r="3.4" fill="' . $center . '" stroke="' . $frame . '" stroke-width="1.2"/>'
			. '<path d="M0 0L34 34M34 0L0 34" stroke="' . $frame . '" stroke-width="1" opacity="0.35"/>'
			. '</pattern></defs>';

		// Clear panes over the whole opening (image or sky shows through).
		$parts[] = '<path class="open-window__sky" d="' . $cutouts . '" fill="url(#' . $id . 's)"/>';

		// Tracery head: two rosettes and a quatrefoil cluster.
		$head = '';

		foreach (array(array(200, 286), array(400, 286)) as [$cx, $cy]) {
			$head .= self::rosette($cx, $cy, 58, 82, 19, $cream, $petal, $center, $accent, $frame);
		}

		$head .= self::cluster(300, 150, 22, 38, 15, $cream, $petal, $center, $accent, $frame);
		$head .= self::cluster(300, 400, 14, 26, 10, $cream, $petal, $center, $accent, $frame);

		// Lancets with pointed heads, flower glass on top and clear panes below.
		$inner = $right - $left - 24;
		$gap = 14;
		$lancetWidth = ($inner - ($lancets - 1) * $gap) / $lancets;
		$lancetsSvg = '';

		for ($i = 0; $i < $lancets; $i++) {
			$x1 = $left + 12 + $i * ($lancetWidth + $gap);
			$x2 = $x1 + $lancetWidth;
			$top = self::archPath($x1, $x2, 520, 600, $lancetWidth * 0.62);
			$lancetsSvg .= '<path d="' . $top . '" fill="url(#' . $id . 'p)" stroke="' . $frame . '" stroke-width="6"/>';
			$lancetsSvg .= '<rect x="' . self::n($x1) . '" y="600" width="' . self::n($lancetWidth) . '" height="' . ($bottom - 600 - 12) . '" fill="none" stroke="' . $frame . '" stroke-width="7"/>';
			$lancetsSvg .= '<line x1="' . self::n($x1) . '" y1="740" x2="' . self::n($x2) . '" y2="740" stroke="' . $frame . '" stroke-width="6"/>';

			// Stone mullion between the lancets.
			if ($i < $lancets - 1) {
				$lancetsSvg .= '<rect x="' . self::n($x2) . '" y="560" width="' . $gap . '" height="' . ($bottom - 560) . '" fill="' . $frame . '"/>';
			}
		}

		$parts[] = '<g clip-path="url(#' . $id . 'c)">' . $head . $lancetsSvg . '</g>';

		// Side lancet: quatrefoil on top, flower glass head, clear panes.
		if ($side) {
			$parts[] = self::cluster(710, 420, 11, 20, 8, $cream, $petal, $center, $accent, $frame);
			$parts[] = '<path d="' . self::archPath(670, 750, 520, 580, 52) . '" fill="url(#' . $id . 'p)" stroke="' . $frame . '" stroke-width="5"/>';

			foreach (array(680, 760) as $y) {
				$parts[] = '<line x1="660" y1="' . $y . '" x2="760" y2="' . $y . '" stroke="' . $frame . '" stroke-width="6"/>';
			}

			$parts[] = '<path d="' . $sideArch . '" fill="none" stroke="' . $frame . '" stroke-width="10"/>';
		}

		// The wall (section background) with the openings cut out, then the outer frame.
		$wall = 'M0 0H' . $width . 'V900H0Z ' . $cutouts;
		$parts[] = '<path class="open-window__wall" d="' . $wall . '" fill-rule="evenodd"/>';
		$parts[] = '<path d="' . $arch . '" fill="none" stroke="' . $frame . '" stroke-width="16"/>';
		$parts[] = '<line x1="' . $left . '" y1="' . $bottom . '" x2="' . ($side ? 760 : $right) . '" y2="' . $bottom . '" stroke="' . $frame . '" stroke-width="18"/>';

		return '<svg class="open-window__svg" viewBox="0 0 ' . $width . ' 900" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Kirchenfenster" preserveAspectRatio="xMidYMax meet">'
			. $defs . implode('', $parts) . '</svg>';
	}

	protected function definition(): array {
		return array(
			'title' => 'Offenes Kirchenfenster',
			'icon' => 'window',
			'description' => 'Einfaches, offenes Kirchenfenster (Maßwerk mit Rosetten, Blütenglas, klare Scheiben mit Blick nach draußen) neben Text und Forderungen.',
			'fields' => array(
				self::sectionIdField('offenes_fenster'),
				self::titleField('Offen für alle'),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => 'Ein Fenster, durch das man hinaussieht: hell, offen und für alle.'),
				array('name' => 'items', 'type' => 'strings', 'label' => 'Forderungen (eine pro Zeile, „Forderung: Erklärung“ setzt den Anfang fett)', 'default' => array()),
				array('name' => 'view_image', 'type' => 'image', 'label' => 'Blick durchs Fenster (Bild hinter den klaren Scheiben, optional)', 'default' => ''),
				array('name' => 'palette', 'type' => 'select', 'label' => 'Glasfarben', 'default' => 'evangelisch', 'options' => self::PALETTE_LABELS),
				array('name' => 'lancets', 'type' => 'select', 'label' => 'Bahnen (Spitzbögen)', 'default' => '3', 'options' => array('2' => '2', '3' => '3', '4' => '4')),
				array('name' => 'side_window', 'type' => 'toggle', 'label' => 'Schmales Seitenfenster', 'default' => true),
				array('name' => 'layout', 'type' => 'select', 'label' => 'Anordnung', 'default' => 'left', 'options' => array('left' => 'Fenster links, Text rechts', 'right' => 'Fenster rechts, Text links', 'center' => 'Fenster oben, Text darunter')),
				array('name' => 'glow', 'type' => 'toggle', 'label' => 'Lichtschein auf dem Boden', 'default' => true),
				self::classesField('')
			)
		);
	}

	/**
	 * A pointed (Gothic) arch from the springing line down to the bottom.
	 *
	 * @param float $x1
	 * @param float $x2
	 * @param float $spring
	 * @param float $bottom
	 * @param float $radius
	 * @return string
	 */
	private static function archPath(float $x1, float $x2, float $spring, float $bottom, float $radius): string {
		$half = ($x2 - $x1) / 2;
		$radius = max($radius, $half + 1);
		$dx = $radius - $half;
		$apex = $spring - sqrt($radius * $radius - $dx * $dx);
		$mid = $x1 + $half;

		return 'M' . self::n($x1) . ' ' . self::n($bottom) . 'V' . self::n($spring)
			. 'A' . self::n($radius) . ' ' . self::n($radius) . ' 0 0 1 ' . self::n($mid) . ' ' . self::n($apex)
			. 'A' . self::n($radius) . ' ' . self::n($radius) . ' 0 0 1 ' . self::n($x2) . ' ' . self::n($spring)
			. 'V' . self::n($bottom) . 'Z';
	}

	/**
	 * A small cluster: a center circle with four circles around it (quatrefoil).
	 * @param float $cx
	 * @param float $cy
	 * @param float $r
	 * @param float $distance
	 * @param float $small
	 * @param string $cream
	 * @param string $petal
	 * @param string $center
	 * @param string $accent
	 * @param string $frame
	 */
	private static function cluster(float $cx, float $cy, float $r, float $distance, float $small, string $cream, string $petal, string $center, string $accent, string $frame): string {
		$svg = '';

		foreach (array(array(0, -1), array(1, 0), array(0, 1), array(-1, 0)) as [$x, $y]) {
			$svg .= self::medallion($cx + $x * $distance, $cy + $y * $distance, $small, $cream, $petal, $accent, $frame);
		}

		return $svg . self::medallion($cx, $cy, $r, $cream, $petal, $center, $frame);
	}

	/**
	 * A round glass medallion with a ring of petals and a colored center.
	 * @param float $cx
	 * @param float $cy
	 * @param float $r
	 * @param string $cream
	 * @param string $petal
	 * @param string $center
	 * @param string $frame
	 */
	private static function medallion(float $cx, float $cy, float $r, string $cream, string $petal, string $center, string $frame): string {
		$svg = '<circle cx="' . self::n($cx) . '" cy="' . self::n($cy) . '" r="' . self::n($r) . '" fill="' . $cream . '" stroke="' . $frame . '" stroke-width="' . self::n(max(2, $r / 6)) . '"/>';

		for ($i = 0; $i < 6; $i++) {
			$angle = deg2rad($i * 60);
			$svg .= '<circle cx="' . self::n($cx + cos($angle) * $r * 0.55) . '" cy="' . self::n($cy + sin($angle) * $r * 0.55) . '" r="' . self::n($r * 0.26) . '" fill="' . $petal . '" stroke="' . $frame . '" stroke-width="1"/>';
		}

		return $svg . '<circle cx="' . self::n($cx) . '" cy="' . self::n($cy) . '" r="' . self::n($r * 0.24) . '" fill="' . $center . '" stroke="' . $frame . '" stroke-width="1"/>';
	}

	private static function n(float $value): string {
		return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
	}

	/**
	 * A rosette: a large wheel with spokes and six medallions around it.
	 * @param float $cx
	 * @param float $cy
	 * @param float $r
	 * @param float $distance
	 * @param float $small
	 * @param string $cream
	 * @param string $petal
	 * @param string $center
	 * @param string $accent
	 * @param string $frame
	 */
	private static function rosette(float $cx, float $cy, float $r, float $distance, float $small, string $cream, string $petal, string $center, string $accent, string $frame): string {
		$svg = '';

		for ($i = 0; $i < 6; $i++) {
			$angle = deg2rad($i * 60 - 90);
			$svg .= self::medallion($cx + cos($angle) * $distance, $cy + sin($angle) * $distance, $small, $cream, $petal, $i % 2 ? $accent : $center, $frame);
		}

		$svg .= '<circle cx="' . self::n($cx) . '" cy="' . self::n($cy) . '" r="' . self::n($r) . '" fill="' . $cream . '" stroke="' . $frame . '" stroke-width="7"/>';

		for ($i = 0; $i < 8; $i++) {
			$angle = deg2rad($i * 45);
			$svg .= '<line x1="' . self::n($cx) . '" y1="' . self::n($cy) . '" x2="' . self::n($cx + cos($angle) * $r) . '" y2="' . self::n($cy + sin($angle) * $r) . '" stroke="' . $frame . '" stroke-width="2.4"/>';
			$svg .= '<circle cx="' . self::n($cx + cos($angle + deg2rad(22.5)) * $r * 0.66) . '" cy="' . self::n($cy + sin($angle + deg2rad(22.5)) * $r * 0.66) . '" r="' . self::n($r * 0.12) . '" fill="' . ($i % 2 ? $petal : $accent) . '" stroke="' . $frame . '" stroke-width="1.2"/>';
		}

		return $svg . '<circle cx="' . self::n($cx) . '" cy="' . self::n($cy) . '" r="' . self::n($r * 0.2) . '" fill="' . $center . '" stroke="' . $frame . '" stroke-width="2"/>';
	}
}
