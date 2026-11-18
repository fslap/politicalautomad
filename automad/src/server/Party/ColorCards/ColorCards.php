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

namespace Automad\Party\ColorCards;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component ColorCards: a colorful wall of tilted cards, each with a pattern, a large
 * symbol and a caption sticker. On hover a card straightens and comes to the front.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ColorCards extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const COLORS = array('pink' => 'Pink', 'yellow' => 'Gelb', 'blue' => 'Blau', 'green' => 'Grün', 'orange' => 'Orange', 'purple' => 'Lila');

	public function __construct() {
		parent::__construct(__DIR__, 'colorcards');
	}

	public function context(array $config): array {
		$colors = array_keys(self::COLORS);
		$cards = array();
		$angles = array(-4, 3, -2, 4, -3, 2, -5, 3);

		foreach (array_values(array_filter((array) $config['cards'], fn ($s) => is_array($s) && trim((string) ($s['caption'] ?? '')) !== '')) as $i => $s) {
			$cards[] = array(
				'symbol' => trim((string) ($s['symbol'] ?? '')),
				'label' => trim((string) ($s['label'] ?? '')),
				'caption' => trim((string) ($s['caption'] ?? '')),
				'link' => trim((string) ($s['link'] ?? '')),
				'color' => in_array($s['color'] ?? '', $colors, true) ? $s['color'] : $colors[$i % count($colors)],
				'style' => '--sw-angle: ' . $angles[$i % count($angles)] . 'deg;'
			);
		}

		$text = trim((string) $config['text']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'cards' => $cards,
			'has_cards' => !empty($cards),
			'theme' => $config['theme'] === 'light' ? 'light' : 'dark',
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Farbkarten (bunte Wand)',
			'icon' => 'grid-3x2-gap',
			'description' => 'Bunte Wand aus schräg gestellten Karten mit Muster, großem Symbol und Beschriftung. Beim Darüberfahren richtet sich die Karte auf.',
			'fields' => array(
				self::sectionIdField('farbkarten'),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => ''),
				self::titleField(''),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => ''),
				array(
					'name' => 'cards',
					'type' => 'list',
					'label' => 'Karten',
					'addLabel' => 'Karte hinzufügen',
					'itemTitle' => 'caption',
					'default' => array(),
					'fields' => array(
						array('name' => 'symbol', 'type' => 'text', 'label' => 'Symbol (Emoji)'),
						array('name' => 'label', 'type' => 'text', 'label' => 'Kleine Zeile oben'),
						array('name' => 'caption', 'type' => 'text', 'label' => 'Beschriftung (Sticker)'),
						array('name' => 'link', 'type' => 'url', 'label' => 'Link (optional)'),
						array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch') + self::COLORS)
					)
				),
				array('name' => 'theme', 'type' => 'select', 'label' => 'Hintergrund', 'default' => 'dark', 'options' => array('dark' => 'Dunkel', 'light' => 'Hell')),
				self::classesField('')
			)
		);
	}
}
