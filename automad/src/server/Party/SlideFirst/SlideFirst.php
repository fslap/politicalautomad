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

namespace Automad\Party\SlideFirst;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\HeroTitle\HeroTitle;
use Automad\Party\HouseRow\HouseRow;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;
use Automad\Party\WaveLine\WaveLine;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Folie mit großem Titel, Mehr-Pfeil, Häusern, Wellen und Boot (Skizze: First Page).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideFirst extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slidefirst');
	}

	public function context(array $config): array {
		$hero = self::unprefix($config, 'hero_');
		$hero['classes'] = trim(($hero['classes'] ?? '') . ' hero-title--slide');

		return array_merge($this->slideContext($config, 'first'), array(
			'hero_html' => (new HeroTitle())->renderInner($hero),
			'scene_image' => $config['scene_image'],
			'houses_html' => $config['scene_image'] ? '' : (new HouseRow())->renderInner(self::unprefix($config, 'houses_')),
			'wave_html' => $config['scene_image'] ? '' : (new WaveLine())->renderInner(self::unprefix($config, 'wave_'))
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Start', '', 'top-right'),
			...self::prefixFields(HeroTitle::elementFields(), 'hero_', 'Titel'),
			...self::prefixFields(self::withDefaults(HouseRow::elementFields(), array('height' => '42%', 'ground' => false, 'hover_animation' => 'tilt')), 'houses_', 'Häuser'),
			...self::prefixFields(self::withDefaults(WaveLine::elementFields(), array('height' => '30%')), 'wave_', 'Wellen'),
			array('name' => 'scene_image', 'type' => 'image', 'label' => 'Szene: eigenes Bild statt Häusern und Wellen (optional)', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Startseite',
			'icon' => 'easel',
			'description' => 'Folie mit großem Titel, Mehr-Pfeil, Häusern, Wellen und Boot (Skizze: First Page).',
			'fields' => self::elementFields()
		);
	}
}
