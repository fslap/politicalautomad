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

namespace Automad\Party\HoverDecor;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Kleines Bild in einer Ecke, das beim Darüberfahren kippt oder sich bewegt.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class HoverDecor extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'hoverdecor');
	}

	public function context(array $config): array {
		return array(
			'decor_html' => self::decorHtml((string) $config['decor_image'], (string) $config['decor_position'], (string) $config['decor_animation']),
			'style' => 'min-height: ' . (trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '140px') . ';',
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'decor_image', 'type' => 'image', 'label' => 'Bild (leer = Segelboot)', 'default' => ''),
			array('name' => 'decor_position', 'type' => 'select', 'label' => 'Position', 'default' => 'bottom-right', 'options' => array_slice(self::$corners, 1, null, true)),
			array('name' => 'decor_animation', 'type' => 'select', 'label' => 'Animation', 'default' => 'tilt', 'options' => array('tilt' => 'Kippen', 'move' => 'Bewegen', 'float' => 'Schweben', 'wobble' => 'Wackeln')),
			array('name' => 'height', 'type' => 'text', 'label' => 'Höhe des Bereichs', 'default' => '140px'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Hover-Deko',
			'icon' => 'balloon',
			'description' => 'Kleines Bild in einer Ecke, das beim Darüberfahren kippt oder sich bewegt.',
			'fields' => self::elementFields()
		);
	}
}
