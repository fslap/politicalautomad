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

namespace Automad\Party\CardRow;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Karten mit Bild, Titel und Text nebeneinander (Skizze: Bilanz).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class CardRow extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'cardrow');
	}

	public function context(array $config): array {
		$cards = array_values(array_filter(
			is_array($config['cards']) ? $config['cards'] : array(),
			fn ($card) => is_array($card) && (!empty($card['title']) || !empty($card['image']) || !empty($card['text']))
		));

		return array(
			'title' => $config['title'],
			'cards' => $cards,
			'style' => '--cards: ' . max(1, min(4, count($cards))) . ';',
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
			array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift', 'default' => ''),
			array(
				'name' => 'cards',
				'type' => 'list',
				'label' => 'Karten',
				'addLabel' => 'Karte hinzufügen',
				'itemTitle' => 'title',
				'default' => array(),
				'fields' => array(
					array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
					array('name' => 'title', 'type' => 'text', 'label' => 'Titel'),
					array('name' => 'value', 'type' => 'text', 'label' => 'Kennzahl (optional, z.B. 12.400 EUR)'),
					array('name' => 'text', 'type' => 'textarea', 'label' => 'Text'),
					array('name' => 'link', 'type' => 'url', 'label' => 'Link')
				)
			),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Kartenreihe (Bilanz)',
			'icon' => 'layout-three-columns',
			'description' => 'Karten mit Bild, Titel und Text nebeneinander (Skizze: Bilanz).',
			'fields' => self::elementFields()
		);
	}
}
