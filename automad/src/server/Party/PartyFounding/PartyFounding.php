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

namespace Automad\Party\PartyFounding;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component PartyFounding: the steps to found a party as a representative charter.
 * Every step is a card that turns over on hover (or tap) and shows its back side, e.g. an
 * allusion or the details behind the step.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class PartyFounding extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const COLORS = array('blue' => 'Blau', 'orange' => 'Terrakotta / Orange', 'gold' => 'Ocker', 'green' => 'Grün', 'purple' => 'Aubergine', 'red' => 'Rot');

	public function __construct() {
		parent::__construct(__DIR__, 'partyfounding');
	}

	public function context(array $config): array {
		$colors = array_keys(self::COLORS);
		$steps = array();

		foreach (array_values(array_filter((array) $config['steps'], fn ($s) => is_array($s) && trim((string) ($s['title'] ?? '')) !== '')) as $i => $s) {
			$back = trim((string) ($s['back'] ?? ''));
			$steps[] = array(
				'number' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
				'symbol' => trim((string) ($s['symbol'] ?? '')),
				'title' => trim((string) $s['title']),
				'text' => trim((string) ($s['text'] ?? '')),
				'back_html' => $back === '' ? '' : Str::markdown($back),
				'has_back' => $back !== '',
				'link' => trim((string) ($s['link'] ?? '')),
				'link_label' => trim((string) ($s['link_label'] ?? '')) ?: 'Mehr',
				'color' => in_array($s['color'] ?? '', $colors, true) ? $s['color'] : $colors[$i % count($colors)]
			);
		}

		$intro = trim((string) $config['intro']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'intro_html' => $intro === '' ? '' : Str::markdown($intro),
			'steps' => $steps,
			'has_steps' => !empty($steps),
			'hint' => trim((string) $config['hint']),
			'seal' => trim((string) $config['seal']),
			'layout' => $config['layout'] === 'grid' ? 'grid' : 'path',
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Parteigründung (Schritte mit Hover)',
			'icon' => 'flag',
			'description' => 'Die Schritte zur Gründung einer Partei als repräsentative Urkunde: Karten, die sich beim Darüberfahren oder Antippen umdrehen und ihre Rückseite (Anspielung, Details) zeigen.',
			'fields' => array(
				self::sectionIdField('parteigruendung'),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => 'Gründung'),
				self::titleField('Eine Partei gründen'),
				array('name' => 'intro', 'type' => 'markdown', 'label' => 'Einleitung (Markdown)', 'default' => ''),
				array(
					'name' => 'steps',
					'type' => 'list',
					'label' => 'Schritte',
					'addLabel' => 'Schritt hinzufügen',
					'itemTitle' => 'title',
					'default' => array(),
					'fields' => array(
						array('name' => 'symbol', 'type' => 'text', 'label' => 'Symbol (Emoji, optional)'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Schritt'),
						array('name' => 'text', 'type' => 'textarea', 'label' => 'Vorderseite: kurzer Text'),
						array('name' => 'back', 'type' => 'markdown', 'label' => 'Rückseite beim Hover (Anspielung, Details)'),
						array('name' => 'link', 'type' => 'url', 'label' => 'Link (optional)'),
						array('name' => 'link_label', 'type' => 'text', 'label' => 'Linktext'),
						array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch') + self::COLORS)
					)
				),
				array('name' => 'layout', 'type' => 'select', 'label' => 'Anordnung', 'default' => 'path', 'options' => array('path' => 'Weg (versetzt, mit Linie)', 'grid' => 'Raster')),
				array('name' => 'hint', 'type' => 'text', 'label' => 'Hinweis unter den Karten', 'default' => 'Darüberfahren oder antippen, um die Karten umzudrehen.'),
				array('name' => 'seal', 'type' => 'text', 'label' => 'Text im Stempel (optional)', 'default' => ''),
				self::classesField('')
			)
		);
	}
}
