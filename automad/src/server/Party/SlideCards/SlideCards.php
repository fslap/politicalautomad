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

namespace Automad\Party\SlideCards;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\CardRow\CardRow;
use Automad\Party\HouseRow\HouseRow;
use Automad\Party\RollTiles\RollTiles;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;
use Automad\Party\WaveLine\WaveLine;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Folie mit Kartenreihe, hereinrollenden Häusern beim Hover und Wellenlinie (Skizze: Bilanz).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideCards extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slidecards');
	}

	public function context(array $config): array {
		return array_merge($this->slideContext($config, 'cards'), array(
			'cards_html' => (new CardRow())->renderInner(self::unprefix($config, 'cards_')),
			'houses_html' => (new HouseRow())->renderInner(self::unprefix($config, 'houses_')),
			'tiles_html' => (new RollTiles())->renderInner(self::unprefix($config, 'tiles_')),
			'wave_html' => (bool) $config['wave'] ? (new WaveLine())->renderInner(self::unprefix($config, 'wave_')) : ''
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Bilanz', '', 'top-right'),
			...self::prefixFields(CardRow::elementFields(), 'cards_', 'Karten'),
			...self::prefixFields(self::withDefaults(HouseRow::elementFields(), array('height' => '110px', 'align' => 'right', 'roll_in' => 'hover', 'style' => 'mixed', 'houses' => array(
				array('type' => 'gable', 'width' => 3, 'floors' => 2, 'windows' => 1, 'door' => true, 'color' => 'orange', 'details' => true),
				array('type' => 'flat', 'width' => 3, 'floors' => 4, 'windows' => 2, 'door' => true, 'color' => 'blue', 'details' => true),
				array('type' => 'shop', 'width' => 4, 'floors' => 2, 'windows' => 2, 'door' => true, 'color' => 'green', 'details' => true)
			))), 'houses_', 'Rollhäuser'),
			...self::prefixFields(self::withDefaults(RollTiles::elementFields(), array('tiles' => array())), 'tiles_', 'Rollkacheln'),
			...self::prefixFields(self::withDefaults(WaveLine::elementFields(), array('variant' => 'line', 'boat' => false, 'height' => '50px')), 'wave_', 'Wellenlinie'),
			array('name' => 'wave', 'type' => 'toggle', 'label' => 'Wellenlinie anzeigen', 'default' => true)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Bilanz',
			'icon' => 'layout-three-columns',
			'description' => 'Folie mit Kartenreihe, hereinrollenden Häusern beim Hover und Wellenlinie (Skizze: Bilanz).',
			'fields' => self::elementFields()
		);
	}
}
