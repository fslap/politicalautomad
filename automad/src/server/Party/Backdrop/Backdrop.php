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

namespace Automad\Party\Backdrop;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Backdrop (Kulisse): a stage set behind demands. "half" places the set
 * next to the demands (the demands are the focus), "full" puts the demands in front of a
 * set that fills the whole section. Scenes: an official assembly (blue and gold, e.g. for
 * the UN program) and a police scene at night (blue light, checker band).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Backdrop extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const SCENES = array(
		'official' => 'Offiziell (Versammlung, Blau und Gold)',
		'police' => 'Polizei (Nacht, Blaulicht, Karoband)',
		'party' => 'Partei (Terrakotta und Ocker)'
	);

	/**
	 * State of the seeded random generator.
	 */
	private int $state = 1;

	public function __construct() {
		parent::__construct(__DIR__, 'backdrop');
	}

	public function context(array $config): array {
		$scene = isset(self::SCENES[$config['scene']]) ? (string) $config['scene'] : 'official';
		$mode = $config['mode'] === 'full' ? 'full' : 'half';
		$side = $config['side'] === 'right' ? 'right' : 'left';
		$sectionId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']);
		$demands = array();
		$number = 0;

		foreach ((array) $config['demands'] as $demand) {
			if (!is_array($demand) || trim((string) ($demand['title'] ?? '')) === '') {
				continue;
			}

			$number++;
			$demands[] = array(
				'number' => str_pad((string) $number, 2, '0', STR_PAD_LEFT),
				'title' => trim((string) $demand['title']),
				'text' => trim((string) ($demand['text'] ?? '')),
				'focus' => $number === 1 && (bool) $config['focus_first']
			);
		}

		$text = trim((string) $config['text']);
		$this->state = (crc32($sectionId ?: $scene) & 0x7fffffff) ?: 1;

		return array(
			'section_id' => $sectionId,
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'demands' => $demands,
			'has_demands' => !empty($demands),
			'numbered' => (bool) $config['numbered'],
			'note' => trim((string) $config['note']),
			'mode' => $mode,
			'scene' => $scene,
			'side' => $side,
			'svg' => $this->stage($scene, uniqid('bd')),
			'emblem_svg' => (bool) $config['emblem'] ? self::emblem((string) $config['seal_text'], uniqid('em')) : '',
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * A round seal: a ring with the text running around it and a globe of lines inside.
	 *
	 * @param string $text
	 * @param string $id
	 * @return string
	 */
	public static function emblem(string $text, string $id): string {
		$text = trim($text) === '' ? 'FSLAP ·' : trim($text);
		$repeat = max(1, (int) floor(46 / max(1, mb_strlen($text))));
		$ring = htmlspecialchars(trim(str_repeat($text . ' ', $repeat)));
		$globe = '';

		foreach (array(14, 28, 40) as $rx) {
			$globe .= '<ellipse cx="100" cy="100" rx="' . $rx . '" ry="46" />';
		}

		foreach (array(-30, -15, 0, 15, 30) as $y) {
			$half = sqrt(max(0, 46 * 46 - $y * $y));
			$globe .= '<line x1="' . round(100 - $half, 1) . '" y1="' . (100 + $y) . '" x2="' . round(100 + $half, 1) . '" y2="' . (100 + $y) . '"/>';
		}

		$stars = '';

		for ($i = 0; $i < 12; $i++) {
			$angle = deg2rad($i * 30 - 90);
			$stars .= '<circle cx="' . round(100 + cos($angle) * 58, 1) . '" cy="' . round(100 + sin($angle) * 58, 1) . '" r="2.2"/>';
		}

		return '<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Siegel">'
			. '<defs><path id="' . $id . '" d="M100 100m-80 0a80 80 0 1 1 160 0a80 80 0 1 1 -160 0"/></defs>'
			. '<circle cx="100" cy="100" r="96" class="emblem__disc"/>'
			. '<circle cx="100" cy="100" r="92" class="emblem__line"/><circle cx="100" cy="100" r="67" class="emblem__line"/>'
			. '<text class="emblem__text"><textPath href="#' . $id . '" startOffset="0">' . $ring . '</textPath></text>'
			. '<g class="emblem__stars">' . $stars . '</g>'
			. '<circle cx="100" cy="100" r="46" class="emblem__line"/><g class="emblem__globe">' . $globe . '</g>'
			. '</svg>';
	}

	protected function definition(): array {
		return array(
			'title' => 'Kulisse (halb oder voll)',
			'icon' => 'easel',
			'description' => 'Bühnenbild hinter Forderungen: halb (Kulisse neben den Forderungen im Fokus) oder voll (Forderungen vor der Kulisse). Szenen: offizielle Versammlung, Polizei bei Nacht, Partei.',
			'fields' => array(
				self::sectionIdField('kulisse'),
				array('name' => 'mode', 'type' => 'select', 'label' => 'Kulisse', 'default' => 'half', 'options' => array('half' => 'Halb – Kulisse neben den Forderungen', 'full' => 'Voll – Forderungen vor der Kulisse')),
				array('name' => 'scene', 'type' => 'select', 'label' => 'Szene', 'default' => 'official', 'options' => self::SCENES),
				array('name' => 'side', 'type' => 'select', 'label' => 'Kulisse (halb) auf der Seite', 'default' => 'left', 'options' => array('left' => 'Links', 'right' => 'Rechts')),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => 'Programm'),
				self::titleField('Unser Programm'),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => ''),
				array(
					'name' => 'demands',
					'type' => 'list',
					'label' => 'Forderungen',
					'addLabel' => 'Forderung hinzufügen',
					'itemTitle' => 'title',
					'default' => array(
						array('title' => 'Erste Forderung', 'text' => 'Kurze Erklärung.'),
						array('title' => 'Zweite Forderung', 'text' => 'Kurze Erklärung.')
					),
					'fields' => array(
						array('name' => 'title', 'type' => 'text', 'label' => 'Forderung'),
						array('name' => 'text', 'type' => 'textarea', 'label' => 'Erklärung')
					)
				),
				array('name' => 'focus_first', 'type' => 'toggle', 'label' => 'Erste Forderung groß im Fokus', 'default' => true),
				array('name' => 'numbered', 'type' => 'toggle', 'label' => 'Forderungen nummerieren', 'default' => true),
				array('name' => 'emblem', 'type' => 'toggle', 'label' => 'Siegel anzeigen', 'default' => true),
				array('name' => 'seal_text', 'type' => 'text', 'label' => 'Text im Siegel', 'default' => 'FSLAP · PROGRAMM ·'),
				array('name' => 'note', 'type' => 'text', 'label' => 'Fußnote (optional)', 'default' => ''),
				self::classesField('')
			)
		);
	}

	/**
	 * An official assembly: banners, a spotlight, the tiers of a plenary hall and a rostrum.
	 * @param string $id
	 * @param bool $party
	 */
	private function assemblyScene(string $id, bool $party): string {
		[$top, $bottom, $banner, $gold, $tier] = $party
			? array('#5a2c1d', '#2a150e', '#a95a36', '#d8b25c', '#3b1d13')
			: array('#0f2147', '#081430', '#3f7fd0', '#d8b25c', '#0b1a3a');
		$svg = '<defs><linearGradient id="' . $id . 'a" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $top . '"/><stop offset="1" stop-color="' . $bottom . '"/></linearGradient>'
			. '<radialGradient id="' . $id . 'b" cx="0.5" cy="0.1" r="0.6"><stop offset="0" stop-color="#ffffff" stop-opacity="0.28"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/></radialGradient>'
			. '<linearGradient id="' . $id . 'c" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . $banner . '"/><stop offset="1" stop-color="' . $banner . '" stop-opacity="0.35"/></linearGradient></defs>'
			. '<rect width="1600" height="900" fill="url(#' . $id . 'a)"/>';

		// Tall banners with gold edges.
		for ($i = 0; $i < 9; $i++) {
			$x = 40 + $i * 175;
			$height = $i === 4 ? 560 : 470 - abs($i - 4) * 18;
			$svg .= '<path d="M' . $x . ' 0H' . ($x + 120) . 'V' . $height . 'L' . ($x + 60) . ' ' . ($height + 40) . 'L' . $x . ' ' . $height . 'Z" fill="url(#' . $id . 'c)" opacity="' . ($i === 4 ? '0.95' : '0.55') . '"/>'
				. '<path d="M' . ($x + 8) . ' 0V' . ($height - 4) . 'M' . ($x + 112) . ' 0V' . ($height - 4) . '" stroke="' . $gold . '" stroke-width="2" opacity="0.7"/>';
		}

		$svg .= '<rect width="1600" height="900" fill="url(#' . $id . 'b)"/>';

		// The tiers of the hall as arcs.
		for ($i = 0; $i < 6; $i++) {
			$y = 640 + $i * 48;
			$svg .= '<path d="M-100 ' . ($y + 120) . 'Q800 ' . ($y - 90) . ' 1700 ' . ($y + 120) . 'V900H-100Z" fill="' . $tier . '" opacity="' . self::n(0.55 + $i * 0.08) . '"/>'
				. '<path d="M-100 ' . ($y + 120) . 'Q800 ' . ($y - 90) . ' 1700 ' . ($y + 120) . '" fill="none" stroke="' . $gold . '" stroke-width="1.5" opacity="0.45"/>';
		}

		// Rostrum.
		$svg .= '<path d="M690 900L715 700H885L910 900Z" fill="' . $bottom . '" stroke="' . $gold . '" stroke-width="3"/>'
			. '<rect x="700" y="688" width="200" height="16" fill="' . $gold . '"/>'
			. '<circle cx="800" cy="790" r="34" fill="none" stroke="' . $gold . '" stroke-width="3"/>';

		return $svg;
	}

	private static function n(float $value): string {
		return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
	}

	/**
	 * A police scene at night: city, street, blue light and a checker band.
	 * @param string $id
	 */
	private function policeScene(string $id): string {
		$svg = '<defs><linearGradient id="' . $id . 'a" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#050c1c"/><stop offset="0.65" stop-color="#0c1d3a"/><stop offset="1" stop-color="#101a2b"/></linearGradient>'
			. '<radialGradient id="' . $id . 'b" cx="0.5" cy="0.5" r="0.5"><stop offset="0" stop-color="#3d7bff" stop-opacity="0.75"/><stop offset="1" stop-color="#3d7bff" stop-opacity="0"/></radialGradient>'
			. '<pattern id="' . $id . 'k" width="60" height="40" patternUnits="userSpaceOnUse"><rect width="60" height="40" fill="#e3e8ef"/><rect width="30" height="20" fill="#1f4fa0"/><rect x="30" y="20" width="30" height="20" fill="#1f4fa0"/></pattern></defs>'
			. '<rect width="1600" height="900" fill="url(#' . $id . 'a)"/>';

		// City silhouette with a few lit windows.
		$x = -20;

		while ($x < 1620) {
			$width = 70 + $this->random() * 110;
			$height = 160 + $this->random() * 260;
			$top = 640 - $height;
			$svg .= '<rect x="' . self::n($x) . '" y="' . self::n($top) . '" width="' . self::n($width) . '" height="' . self::n($height + 10) . '" fill="#08132a"/>';

			for ($wy = $top + 22; $wy < 610; $wy += 34) {
				for ($wx = $x + 14; $wx < $x + $width - 20; $wx += 26) {
					if ($this->random() < 0.18) {
						$svg .= '<rect x="' . self::n($wx) . '" y="' . self::n($wy) . '" width="10" height="14" fill="#f2d48a" opacity="' . self::n(0.35 + $this->random() * 0.5) . '"/>';
					}
				}
			}

			$x += $width + 6;
		}

		// Street with perspective lines.
		$svg .= '<path d="M0 640H1600V900H0Z" fill="#0d1626"/>';

		foreach (array(-900, -300, 300, 900) as $dx) {
			$svg .= '<line x1="800" y1="640" x2="' . (800 + $dx * 2) . '" y2="900" stroke="#ffffff" stroke-opacity="0.08" stroke-width="3"/>';
		}

		// Blue light glow and light bar.
		$svg .= '<g class="backdrop__bluelight"><ellipse cx="560" cy="600" rx="380" ry="170" fill="url(#' . $id . 'b)"/><ellipse cx="1080" cy="610" rx="320" ry="150" fill="url(#' . $id . 'b)"/></g>'
			. '<rect x="680" y="606" width="240" height="18" rx="9" fill="#9fc0ff"/><rect x="690" y="610" width="100" height="10" rx="5" fill="#2f6bff"/><rect x="810" y="610" width="100" height="10" rx="5" fill="#2f6bff"/>';

		// Checker band.
		$svg .= '<rect y="760" width="1600" height="40" fill="url(#' . $id . 'k)" opacity="0.9"/>';

		return $svg;
	}

	/**
	 * Seeded random number between 0 and 1.
	 */
	private function random(): float {
		$this->state = ($this->state * 1103515245 + 12345) & 0x7fffffff;

		return $this->state / 0x7fffffff;
	}

	/**
	 * The stage set as SVG (1600 x 900, scaled to cover).
	 *
	 * @param string $scene
	 * @param string $id
	 * @return string
	 */
	private function stage(string $scene, string $id): string {
		$svg = $scene === 'police' ? $this->policeScene($id) : $this->assemblyScene($id, $scene === 'party');

		return '<svg class="backdrop__svg" viewBox="0 0 1600 900" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" aria-hidden="true">' . $svg . '</svg>';
	}
}
