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

namespace Automad\Party\SlideViewer;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\HouseRow\HouseRow;
use Automad\Party\MarkdownViewer\MarkdownViewer;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Letzte Folie: Markdown-Viewer über einer Stadt-Skyline (Skizze: Last Page).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SlideViewer extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'slideviewer');
	}

	public function context(array $config): array {
		return array_merge($this->slideContext($config, 'viewer'), array(
			'viewer_html' => (new MarkdownViewer())->renderInner(self::unprefix($config, 'viewer_')),
			'scene_image' => $config['scene_image'],
			'city_html' => $config['scene_image'] ? '' : (new HouseRow())->renderInner(self::unprefix($config, 'city_'))
		));
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			...self::slideFields('Programm', '', ''),
			...self::prefixFields(MarkdownViewer::elementFields(), 'viewer_', 'Viewer'),
			...self::prefixFields(self::withDefaults(HouseRow::elementFields(), array('height' => '55%', 'align' => 'spread', 'style' => 'mixed', 'hover_animation' => 'tilt', 'houses' => array(
				array('type' => 'flat', 'width' => 5, 'floors' => 6, 'windows' => 3, 'door' => true, 'color' => '', 'details' => true),
				array('type' => 'gable', 'width' => 4, 'floors' => 3, 'windows' => 2, 'door' => true, 'color' => 'orange', 'details' => true),
				array('type' => 'church', 'width' => 6, 'floors' => 4, 'windows' => 2, 'door' => true, 'color' => '', 'details' => true),
				array('type' => 'dome', 'width' => 6, 'floors' => 4, 'windows' => 2, 'door' => true, 'color' => 'purple', 'details' => true),
				array('type' => 'tvtower', 'width' => 3, 'floors' => 14, 'windows' => 0, 'door' => false, 'color' => '', 'details' => true),
				array('type' => 'tower', 'width' => 3, 'floors' => 10, 'windows' => 2, 'door' => true, 'color' => 'blue', 'details' => true),
				array('type' => 'shop', 'width' => 4, 'floors' => 3, 'windows' => 2, 'door' => true, 'color' => 'green', 'details' => true),
				array('type' => 'factory', 'width' => 6, 'floors' => 2, 'windows' => 2, 'door' => true, 'color' => '', 'details' => true),
				array('type' => 'flat', 'width' => 5, 'floors' => 5, 'windows' => 3, 'door' => true, 'color' => 'gold', 'details' => true)
			))), 'city_', 'Stadt'),
			array('name' => 'scene_image', 'type' => 'image', 'label' => 'Szene: Hintergrundbild statt der Stadt (optional)', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Folie: Markdown-Viewer',
			'icon' => 'book-half',
			'description' => 'Letzte Folie: Markdown-Viewer über einer Stadt-Skyline (Skizze: Last Page).',
			'fields' => self::elementFields()
		);
	}
}
