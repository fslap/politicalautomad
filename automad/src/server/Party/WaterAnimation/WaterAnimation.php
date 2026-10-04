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

	public function __construct() {
		parent::__construct(__DIR__, 'wateranimation');
	}

	public function context(array $config): array {
		return array(
			'layers' => self::normalizeLayers($config['layers']),
			'height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height']),
			'hide_mobile' => (bool) $config['hide_mobile'],
			'classes' => $this->getClasses($config)
		);
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

			$svgs = array_values(array_filter(
				is_array($layer['svgs'] ?? null) ? $layer['svgs'] : array(),
				fn ($svg) => is_array($svg) && !empty($svg['file'])
			));

			$out[] = array(
				'position' => (string) ($layer['position'] ?? ''),
				'svgs' => $svgs
			);
		}

		return $out;
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
						array('name' => 'position', 'type' => 'select', 'label' => 'Ebenen-Position', 'default' => 'svgoffsetleft', 'options' => array('svgoffsetleft' => 'Links', 'svgoffsetmid' => 'Mitte', 'svgoffsetright' => 'Rechts', 'svgoffsettop' => 'Oben', 'svgoffsetbottom' => 'Unten')),
						array(
							'name' => 'svgs',
							'type' => 'list',
							'label' => 'Grafiken',
							'itemTitle' => 'file',
							'fields' => array(
								array('name' => 'file', 'type' => 'image', 'label' => 'SVG / Bild'),
								array('name' => 'position', 'type' => 'text', 'label' => 'Position (pos1 … pos16)', 'default' => 'pos1'),
								array('name' => 'animation', 'type' => 'text', 'label' => 'Animation (water1 … water8)')
							)
						)
					)
				),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe', 'default' => '320px'),
				array('name' => 'hide_mobile', 'type' => 'toggle', 'label' => 'Auf Mobilgeräten ausblenden', 'default' => true),
				self::classesField('svgcontainer svgwater relative')
			)
		);
	}
}
