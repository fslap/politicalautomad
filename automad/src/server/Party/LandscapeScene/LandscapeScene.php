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
		return array(
			'layers' => \Automad\Party\WaterAnimation\WaterAnimation::normalizeLayers($config['layers']),
			'height' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height']),
			'classes' => $this->getClasses($config)
		);
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
						array('name' => 'position', 'type' => 'select', 'label' => 'Ebenen-Position', 'default' => 'svgoffsetleft', 'options' => array('svgoffsetleft' => 'Links', 'svgoffsetmid' => 'Mitte', 'svgoffsetright' => 'Rechts', 'svgoffsettop' => 'Oben', 'svgoffsetbottom' => 'Unten')),
						array(
							'name' => 'svgs',
							'type' => 'list',
							'label' => 'Grafiken',
							'itemTitle' => 'file',
							'fields' => array(
								array('name' => 'file', 'type' => 'image', 'label' => 'SVG / Bild'),
								array('name' => 'position', 'type' => 'text', 'label' => 'Position (pos1 … pos16)', 'default' => 'pos1'),
								array('name' => 'animation', 'type' => 'text', 'label' => 'Beschreibung / Animation')
							)
						)
					)
				),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe', 'default' => '480px'),
				self::classesField('svgcontainer relative')
			)
		);
	}
}
