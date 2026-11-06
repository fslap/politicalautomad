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

namespace Automad\Party\RollTiles;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Kleine Kacheln, die beim Darüberfahren hereinrollen (Skizze: roll in hover).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class RollTiles extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'rolltiles');
	}

	public function context(array $config): array {
		$tiles = array_values(array_filter(
			is_array($config['tiles']) ? $config['tiles'] : array(),
			fn ($tile) => is_array($tile) && (!empty($tile['image']) || !empty($tile['label']))
		));

		foreach ($tiles as $index => &$tile) {
			$tile['style'] = '--i: ' . $index . ';';
		}

		unset($tile);

		$position = in_array($config['position'], array('bottom-right', 'bottom-left', 'inline'), true) ? $config['position'] : 'bottom-right';

		return array(
			'tiles' => $tiles,
			'has_tiles' => !empty($tiles),
			'position' => $position,
			'always' => (bool) $config['always'],
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
			array(
				'name' => 'tiles',
				'type' => 'list',
				'label' => 'Kacheln',
				'addLabel' => 'Kachel hinzufügen',
				'itemTitle' => 'label',
				'default' => array(
					array('image' => '', 'label' => '2024', 'link' => ''),
					array('image' => '', 'label' => '2025', 'link' => ''),
					array('image' => '', 'label' => 'Ausblick', 'link' => '')
				),
				'fields' => array(
					array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
					array('name' => 'label', 'type' => 'text', 'label' => 'Beschriftung'),
					array('name' => 'link', 'type' => 'url', 'label' => 'Link')
				)
			),
			array('name' => 'position', 'type' => 'select', 'label' => 'Position', 'default' => 'bottom-right', 'options' => array('bottom-right' => 'Unten rechts', 'bottom-left' => 'Unten links', 'inline' => 'Im Textfluss')),
			array('name' => 'always', 'type' => 'toggle', 'label' => 'Immer sichtbar (nicht erst beim Hover)', 'default' => false),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Rollkacheln',
			'icon' => 'grid-3x3-gap',
			'description' => 'Kleine Kacheln, die beim Darüberfahren hereinrollen (Skizze: roll in hover).',
			'fields' => self::elementFields()
		);
	}
}
