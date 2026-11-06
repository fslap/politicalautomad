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

namespace Automad\Party\HoverCorner;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Schraffierte Ecke, die beim Darüberfahren Text oder Link zeigt.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class HoverCorner extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'hovercorner');
	}

	public function context(array $config): array {
		return array(
			'corner_html' => self::cornerHtml((string) $config['corner'], (string) $config['corner_label'], (string) $config['corner_link']),
			'style' => 'min-height: ' . (trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '160px') . ';',
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
			array('name' => 'corner', 'type' => 'select', 'label' => 'Ecke', 'default' => 'top-right', 'options' => array_slice(self::$corners, 1, null, true)),
			array('name' => 'corner_label', 'type' => 'text', 'label' => 'Text (erscheint beim Hover)', 'default' => ''),
			array('name' => 'corner_link', 'type' => 'url', 'label' => 'Link', 'default' => ''),
			array('name' => 'height', 'type' => 'text', 'label' => 'Höhe des Bereichs', 'default' => '160px'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Hover-Ecke',
			'icon' => 'triangle-half',
			'description' => 'Schraffierte Ecke, die beim Darüberfahren Text oder Link zeigt.',
			'fields' => self::elementFields()
		);
	}
}
