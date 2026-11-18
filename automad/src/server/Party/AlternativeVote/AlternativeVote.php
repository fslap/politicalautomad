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

namespace Automad\Party\AlternativeVote;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component AlternativeVote: suggestions for an alternative vote as a wall of bold
 * color tiles with hard shadows next to a text column, or as a dark row of cards.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class AlternativeVote extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const COLORS = array('blue' => 'Blau', 'orange' => 'Terrakotta / Orange', 'gold' => 'Ocker', 'green' => 'Grün', 'purple' => 'Aubergine', 'red' => 'Rot', 'white' => 'Weiß');

	public function __construct() {
		parent::__construct(__DIR__, 'alternativevote');
	}

	public function context(array $config): array {
		$colors = array_keys(self::COLORS);
		$options = array();

		foreach (array_values(array_filter((array) $config['options'], fn ($o) => is_array($o) && trim((string) ($o['title'] ?? '')) !== '')) as $i => $o) {
			$options[] = array(
				'label' => trim((string) ($o['label'] ?? '')),
				'title' => trim((string) $o['title']),
				'text' => trim((string) ($o['text'] ?? '')),
				'hint' => trim((string) ($o['hint'] ?? '')),
				'link' => trim((string) ($o['link'] ?? '')),
				'link_label' => trim((string) ($o['link_label'] ?? '')) ?: 'Mehr erfahren',
				'picks' => self::picks($o, $config),
				'has_picks' => !empty(self::picks($o, $config)),
				'color' => in_array($o['color'] ?? '', $colors, true) ? $o['color'] : $colors[$i % 4]
			);
		}

		$text = trim((string) $config['text']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'text_html' => $text === '' ? '' : Str::markdown($text),
			'options' => $options,
			'layout' => $config['layout'] === 'row' ? 'row' : 'wall',
			'note' => trim((string) $config['note']),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Alternative Wahl (Vorschläge)',
			'icon' => 'check2-square',
			'description' => 'Vorschläge zur alternativen Wahl als farbige Kachelwand mit harten Schatten neben einer Textspalte oder als dunkle Kartenreihe.',
			'fields' => array(
				self::sectionIdField('alternative_wahl'),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => 'Wahl'),
				self::titleField('Alternativ wählen'),
				array('name' => 'text', 'type' => 'markdown', 'label' => 'Text (Markdown)', 'default' => ''),
				array(
					'name' => 'options',
					'type' => 'list',
					'label' => 'Vorschläge',
					'addLabel' => 'Vorschlag hinzufügen',
					'itemTitle' => 'title',
					'default' => array(),
					'fields' => array(
						array('name' => 'label', 'type' => 'text', 'label' => 'Kleine Zeile (z.B. Erststimme)'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Vorschlag'),
						array('name' => 'text', 'type' => 'textarea', 'label' => 'Text'),
						array('name' => 'hint', 'type' => 'text', 'label' => 'Hinweis (fett unten)'),
						array('name' => 'person', 'type' => 'text', 'label' => 'Empfehlung: Person (Name)'),
						array('name' => 'person_link', 'type' => 'url', 'label' => 'Link zur Person'),
						array('name' => 'party', 'type' => 'text', 'label' => 'Empfehlung: Partei (Name)'),
						array('name' => 'party_link', 'type' => 'url', 'label' => 'Link zur Partei'),
						array('name' => 'link', 'type' => 'url', 'label' => 'Link'),
						array('name' => 'link_label', 'type' => 'text', 'label' => 'Linktext'),
						array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch') + self::COLORS)
					)
				),
				array('name' => 'person_label', 'type' => 'text', 'label' => 'Bezeichnung Person', 'default' => 'Person'),
				array('name' => 'party_label', 'type' => 'text', 'label' => 'Bezeichnung Partei', 'default' => 'Partei'),
				array('name' => 'layout', 'type' => 'select', 'label' => 'Darstellung', 'default' => 'wall', 'options' => array('wall' => 'Kachelwand neben dem Text', 'row' => 'Dunkle Kartenreihe')),
				array('name' => 'note', 'type' => 'text', 'label' => 'Fußnote (optional)', 'default' => ''),
				self::classesField('')
			)
		);
	}

	/**
	 * The recommended person and party of an option, each with an optional link.
	 *
	 * @param array $option
	 * @param array $config
	 * @return array
	 */
	private static function picks(array $option, array $config): array {
		$picks = array();

		foreach (array('person' => 'Person', 'party' => 'Partei') as $key => $label) {
			$name = trim((string) ($option[$key] ?? ''));

			if ($name === '') {
				continue;
			}

			$picks[] = array(
				'type' => $key,
				'label' => trim((string) ($config[$key . '_label'] ?? '')) ?: $label,
				'name' => $name,
				'link' => trim((string) ($option[$key . '_link'] ?? ''))
			);
		}

		return $picks;
	}
}
