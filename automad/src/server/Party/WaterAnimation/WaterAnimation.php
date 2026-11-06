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

namespace Automad\Party\WaterAnimation;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component WaterAnimation (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class WaterAnimation extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	/**
	 * Horizontal offsets of layers (like the original stylesheet).
	 */
	const LAYER_OFFSETS = array(
		'svgoffsetl55' => 'Links -6 %',
		'svgoffsetleft' => 'Links -3 %',
		'svgoffsetl5' => 'Links -3 % (l5)',
		'svgoffsetmid' => 'Mitte',
		'svgoffsetright' => 'Rechts +5 %',
		'svgoffsetr55' => 'Rechts +10 %'
	);

	public function __construct() {
		parent::__construct(__DIR__, 'wateranimation');
	}

	public function context(array $config): array {
		return array_merge(self::sceneContext($config, 44, 112), array(
			'hide_mobile' => (bool) $config['hide_mobile'],
			'classes' => $this->getClasses($config)
		));
	}

	/**
	 * Normalize SVG layers.
	 *
	 * @param array $layers
	 * @return array
	 */
	public static function normalizeLayers(array $layers): array {
		$out = array();

		foreach ($layers as $layer) {
			if (!is_array($layer)) {
				continue;
			}

			$svgs = array();

			foreach (is_array($layer['svgs'] ?? null) ? $layer['svgs'] : array() as $svg) {
				if (!is_array($svg) || empty($svg['file'])) {
					continue;
				}

				$animation = trim((string) ($svg['animation'] ?? ''));
				$isAnimation = (bool) preg_match('/^water\d+$/', $animation);

				$shift = (float) str_replace(',', '.', (string) ($svg['shift'] ?? '0'));

				$svgs[] = array(
					'file' => (string) $svg['file'],
					'z' => (int) preg_replace('/\D/', '', (string) ($svg['position'] ?? '')) ?: 1,
					'shift' => $shift ? 'top: ' . round(max(-100, min(100, $shift)), 2) . '%;' : '',
					'animation_class' => $isAnimation ? $animation : '',
					'title' => $isAnimation ? '' : $animation
				);
			}

			$out[] = array(
				'position' => preg_replace('/[^a-z0-9]/', '', (string) ($layer['position'] ?? '')),
				'svgs' => $svgs
			);
		}

		return $out;
	}

	/**
	 * The context of a SVG scene. All graphics share one drawing area (like the original
	 * artboards of 1584 × 1152), they are stacked by their pos class (z-index) and the
	 * layers are shifted slightly. The scene shows the bottom part of the drawing area,
	 * the transparent top part is cut off.
	 *
	 * @param array $config
	 * @param int $defaultCrop
	 * @param int $defaultZoom
	 * @return array
	 */
	public static function sceneContext(array $config, int $defaultCrop, int $defaultZoom = 100): array {
		$ratio = array(1584.0, 1152.0);

		if (preg_match('#^\s*(\d+(?:\.\d+)?)\s*[/x:]\s*(\d+(?:\.\d+)?)\s*$#', (string) ($config['ratio'] ?? ''), $match) && (float) $match[1] > 0 && (float) $match[2] > 0) {
			$ratio = array((float) $match[1], (float) $match[2]);
		}

		$crop = ($config['crop'] ?? '') === '' ? $defaultCrop : (int) $config['crop'];
		$crop = max(0, min(90, $crop));
		$height = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) ($config['height'] ?? '')));
		$zoom = ($config['zoom'] ?? '') === '' ? $defaultZoom : (int) $config['zoom'];
		$zoom = max(50, min(300, $zoom));
		$visible = $ratio[1] * $zoom / 100 * (1 - $crop / 100);
		$left = round((100 - $zoom) / 2, 2);

		return array(
			'layers' => self::normalizeLayers((array) ($config['layers'] ?? array())),
			'scene_style' => $height !== '' ? "height: $height;" : 'aspect-ratio: ' . round($ratio[0], 2) . ' / ' . round($visible, 2) . ';',
			'canvas_style' => 'aspect-ratio: ' . round($ratio[0], 2) . ' / ' . round($ratio[1], 2) . "; width: $zoom%; left: $left%;"
		);
	}

	/**
	 * Fields shared by all SVG scenes.
	 *
	 * @param int $crop
	 * @param int $zoom
	 * @return array
	 */
	public static function sceneFields(int $crop, int $zoom = 100): array {
		$crops = array();

		foreach (range(0, 90, 2) as $value) {
			$crops[(string) $value] = $value === 0 ? 'Nichts' : "$value %";
		}

		$zooms = array();

		foreach (range(100, 200, 2) as $value) {
			$zooms[(string) $value] = "$value %";
		}

		return array(
			array('name' => 'zoom', 'type' => 'select', 'label' => 'Breite der Zeichenfläche (größer = Grafiken reichen bis zum Rand)', 'default' => (string) $zoom, 'options' => $zooms),
			array('name' => 'crop', 'type' => 'select', 'label' => 'Oben abschneiden (leerer Bereich der Zeichenfläche)', 'default' => (string) $crop, 'options' => $crops),
			array('name' => 'ratio', 'type' => 'text', 'label' => 'Seitenverhältnis der Zeichenfläche', 'default' => '1584 / 1152', 'help' => 'Format der SVG-Dateien (viewBox), alle Grafiken einer Szene haben dieselbe Zeichenfläche.'),
			array('name' => 'height', 'type' => 'text', 'label' => 'Feste Höhe (optional)', 'default' => '', 'help' => 'Leer lassen, dann skaliert die Szene mit der Breite.')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Wasser-Animation',
			'icon' => 'water',
			'description' => 'Animierte SVG-Ebenen (Wellen, Boote …).',
			'fields' => array(
				array(
					'name' => 'layers',
					'type' => 'list',
					'label' => 'Ebenen',
					'itemTitle' => 'position',
					'fields' => array(
						array('name' => 'position', 'type' => 'select', 'label' => 'Versatz der Ebene', 'default' => 'svgoffsetleft', 'options' => self::LAYER_OFFSETS),
						array(
							'name' => 'svgs',
							'type' => 'list',
							'label' => 'Grafiken',
							'itemTitle' => 'file',
							'fields' => array(
								array('name' => 'file', 'type' => 'image', 'label' => 'SVG / Bild'),
								array('name' => 'position', 'type' => 'text', 'label' => 'Stapelreihenfolge (pos1 … pos15, höher liegt oben)', 'default' => 'pos1'),
								array('name' => 'shift', 'type' => 'number', 'label' => 'Versatz nach unten (% der Zeichenfläche, negativ = nach oben)', 'default' => 0),
								array('name' => 'animation', 'type' => 'text', 'label' => 'Animation (water1 … water8)')
							)
						)
					)
				),
				...self::sceneFields(44, 112),
				array('name' => 'hide_mobile', 'type' => 'toggle', 'label' => 'Auf Mobilgeräten ausblenden', 'default' => true),
				self::classesField('svgcontainer svgwater relative')
			)
		);
	}
}
