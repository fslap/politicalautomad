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

namespace Automad\Party\WaveLine;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Wellenlinie als Trenner, optional mit Wasserfläche und Segelboot (Skizze: First Page, Bilanz).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class WaveLine extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'waveline');
	}

	public function context(array $config): array {
		$variant = in_array($config['variant'], array('line', 'double', 'water'), true) ? $config['variant'] : 'water';
		$height = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height'])) ?: '120px';
		$boatX = max(0, min(100, (int) $config['boat_position']));

		return array(
			'variant' => $variant,
			'boat' => (bool) $config['boat'] ? self::boatSvg() : '',
			'style' => "--wave-height: $height; --boat-x: $boatX%;",
			'classes' => trim('wave-line wave-line--' . $variant . ($config['color'] ? ' wave-line--' . preg_replace('/[^a-z]/', '', (string) $config['color']) : '') . ((bool) $config['animate'] ? ' wave-line--animate' : '') . ' ' . $this->getClasses($config)),
			'is_water' => $variant === 'water',
			'is_double' => $variant === 'double'
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'variant', 'type' => 'select', 'label' => 'Art', 'default' => 'water', 'options' => array('line' => 'Nur Linie', 'double' => 'Doppellinie', 'water' => 'Wasserfläche')),
			array('name' => 'boat', 'type' => 'toggle', 'label' => 'Segelboot', 'default' => true),
			array('name' => 'boat_position', 'type' => 'number', 'label' => 'Position des Boots (0 – 100 %)', 'default' => 50),
			array('name' => 'height', 'type' => 'text', 'label' => 'Höhe', 'default' => '120px'),
			array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => 'blue', 'options' => array('' => 'Textfarbe', 'blue' => 'Blau', 'green' => 'Grün', 'orange' => 'Terrakotta', 'purple' => 'Aubergine')),
			array('name' => 'animate', 'type' => 'toggle', 'label' => 'Wellen bewegen sich', 'default' => true),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Wellenlinie',
			'icon' => 'water',
			'description' => 'Wellenlinie als Trenner, optional mit Wasserfläche und Segelboot (Skizze: First Page, Bilanz).',
			'fields' => self::elementFields()
		);
	}
}
