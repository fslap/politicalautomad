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

namespace Automad\Party\LandscapeScene;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\WaterAnimation\WaterAnimation;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component LandscapeScene (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class LandscapeScene extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'landscapescene');
	}

	public function context(array $config): array {
		return array_merge(WaterAnimation::sceneContext($config, 20), array(
			'hide_mobile' => false,
			'classes' => $this->getClasses($config)
		), self::hoverContext($config));
	}

	protected function definition(): array {
		return array(
			'title' => 'Landschafts-Szene',
			'icon' => 'tree',
			'description' => 'Aus SVG-Ebenen zusammengesetzte Landschaft.',
			'fields' => array(
				array(
					'name' => 'layers',
					'type' => 'list',
					'label' => 'Ebenen',
					'itemTitle' => 'position',
					'fields' => array(
						array('name' => 'position', 'type' => 'select', 'label' => 'Versatz der Ebene', 'default' => 'svgoffsetleft', 'options' => WaterAnimation::LAYER_OFFSETS),
						array(
							'name' => 'svgs',
							'type' => 'list',
							'label' => 'Grafiken',
							'itemTitle' => 'file',
							'fields' => array(
								array('name' => 'file', 'type' => 'image', 'label' => 'SVG / Bild'),
								array('name' => 'position', 'type' => 'text', 'label' => 'Stapelreihenfolge (pos1 … pos15, höher liegt oben)', 'default' => 'pos1'),
								array('name' => 'shift', 'type' => 'number', 'label' => 'Versatz nach unten (% der Zeichenfläche, negativ = nach oben)', 'default' => 0),
								array('name' => 'animation', 'type' => 'text', 'label' => 'Beschreibung oder Animation (water1 … water8)')
							)
						)
					)
				),
				...WaterAnimation::sceneFields(20),
				self::hoverField('tilt'),
				self::classesField('svgcontainer relative')
			)
		);
	}
}
