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

namespace Automad\Party\SlideMap;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\CaseLeafletRegion\CaseLeafletRegion;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Folie mit Leaflet-Karte (Route, Polygone, Marker) und Liste, die bei vielen Einträgen ausklappt (Skizze: Folie 3).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideMap extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slidemap');
	}

	public function context(array $config): array {
		$map = self::unprefix($config, 'map_');
		$map['classes'] = 'case-leaflet-region--slide';

		return array_merge($this->slideContext($config, 'map'), array(
			'map_html' => (new CaseLeafletRegion())->renderInner($map),
			'list_expand' => max(0, (int) $config['list_expand'])
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Karte', '', 'top-right'),
			...self::prefixFields(self::blockFields(new CaseLeafletRegion(), array('classes')), 'map_', 'Karte'),
			array('name' => 'list_expand', 'type' => 'number', 'label' => 'Liste: ab so vielen Einträgen ausklappbar (volle Breite)', 'default' => 5)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Karte mit Liste',
			'icon' => 'map',
			'description' => 'Folie mit Leaflet-Karte (Route, Polygone, Marker) und Liste, die bei vielen Einträgen ausklappt (Skizze: Folie 3).',
			'fields' => self::elementFields()
		);
	}
}
