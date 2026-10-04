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

namespace Automad\Party\RegionScroll;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component RegionScroll (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class RegionScroll extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'regionscroll');
	}

	public function context(array $config): array {
		$regions = array_map(function ($region) {
			if (!is_array($region)) {
				return array();
			}

			// Several anchors per region are supported, e.g. "schleswigholstein,sh".
			$ids = array_values(array_filter(array_map('trim', explode(',', (string) ($region['region_id'] ?? '')))));
			$region['anchors'] = array_map(fn ($id) => array('id' => $id), $ids);

			return $region;
		}, $config['regions']);

		return array(
			'section_id' => $config['section_id'],
			'navigation_target' => $config['navigation_target'],
			'navigation_text' => $config['navigation_text'],
			'scroll_width' => preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['scroll_width']),
			'regions' => array_values(array_filter($regions)),
			'classes' => $this->getClasses($config),
			'has_inner_container' => $this->hasInnerContainer($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Regionen-Scroller',
			'icon' => 'geo-alt',
			'description' => 'Horizontal scrollende Liste von Regionen mit Sprungankern und Navigations-Button.',
			'fields' => array(
				self::sectionIdField(),
				array('name' => 'navigation_target', 'type' => 'text', 'label' => 'Navigationsziel', 'placeholder' => '#lippe'),
				array('name' => 'navigation_text', 'type' => 'text', 'label' => 'Navigationstext', 'default' => '>>'),
				array('name' => 'scroll_width', 'type' => 'text', 'label' => 'Breite des Scrollbereichs', 'default' => '65%'),
				array(
					'name' => 'regions',
					'type' => 'list',
					'label' => 'Regionen',
					'itemTitle' => 'region_name',
					'fields' => array(
						array('name' => 'region_id', 'type' => 'text', 'label' => 'Anker-ID(s), mit Komma getrennt'),
						array('name' => 'region_name', 'type' => 'text', 'label' => 'Name'),
						array('name' => 'region_description', 'type' => 'textarea', 'label' => 'Beschreibung')
					)
				),
				self::classesField('scroll-slide')
			)
		);
	}
}
