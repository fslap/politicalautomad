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

namespace Automad\Party\Curriculum;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Curriculum: a study program as a colorful bento grid of modules (number,
 * symbol, title, text, topics) next to a column with the demands.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Curriculum extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const COLORS = array('pink' => 'Pink', 'yellow' => 'Gelb', 'blue' => 'Blau', 'green' => 'Grün', 'orange' => 'Orange', 'purple' => 'Lila');

	public function __construct() {
		parent::__construct(__DIR__, 'curriculum');
	}

	public function context(array $config): array {
		$colors = array_keys(self::COLORS);
		$modules = array();

		foreach (array_values(array_filter((array) $config['modules'], fn ($m) => is_array($m) && trim((string) ($m['title'] ?? '')) !== '')) as $i => $m) {
			$topics = array_values(array_filter(array_map('trim', (array) ($m['topics'] ?? array()))));
			$modules[] = array(
				'number' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
				'symbol' => trim((string) ($m['symbol'] ?? '')),
				'title' => trim((string) $m['title']),
				'text' => trim((string) ($m['text'] ?? '')),
				'topics' => $topics,
				'has_topics' => !empty($topics),
				'wide' => (bool) ($m['wide'] ?? false),
				'color' => in_array($m['color'] ?? '', $colors, true) ? $m['color'] : $colors[$i % count($colors)]
			);
		}

		$demands = array();

		foreach (array_values(array_filter(array_map(fn ($t) => trim((string) $t), (array) $config['demands']))) as $text) {
			$parts = preg_split('/\s*:\s+/u', $text, 2) ?: array($text);
			$demands[] = count($parts) === 2 ? array('lead' => $parts[0], 'text' => $parts[1]) : array('lead' => '', 'text' => $text);
		}

		$intro = trim((string) $config['intro']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'kicker' => trim((string) $config['kicker']),
			'title' => $config['title'],
			'intro_html' => $intro === '' ? '' : Str::markdown($intro),
			'modules' => $modules,
			'has_modules' => !empty($modules),
			'demands_title' => trim((string) $config['demands_title']),
			'demands' => $demands,
			'has_demands' => !empty($demands),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Studienplan (bunte Module)',
			'icon' => 'mortarboard',
			'description' => 'Ein Studiengang als bunte Kachelfläche aus Modulen (Nummer, Symbol, Titel, Text, Themen) neben einer Spalte mit den Forderungen.',
			'fields' => array(
				self::sectionIdField('studium'),
				array('name' => 'kicker', 'type' => 'text', 'label' => 'Kleine Zeile über dem Titel', 'default' => 'Studium'),
				self::titleField('Studienplan'),
				array('name' => 'intro', 'type' => 'markdown', 'label' => 'Einleitung (Markdown)', 'default' => ''),
				array(
					'name' => 'modules',
					'type' => 'list',
					'label' => 'Module',
					'addLabel' => 'Modul hinzufügen',
					'itemTitle' => 'title',
					'default' => array(),
					'fields' => array(
						array('name' => 'symbol', 'type' => 'text', 'label' => 'Symbol (Emoji)'),
						array('name' => 'title', 'type' => 'text', 'label' => 'Modul'),
						array('name' => 'text', 'type' => 'textarea', 'label' => 'Text'),
						array('name' => 'topics', 'type' => 'strings', 'label' => 'Themen (eines pro Zeile)'),
						array('name' => 'wide', 'type' => 'toggle', 'label' => 'Breite Kachel', 'default' => false),
						array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch') + self::COLORS)
					)
				),
				array('name' => 'demands_title', 'type' => 'text', 'label' => 'Überschrift der Forderungen', 'default' => 'Wir fordern'),
				array('name' => 'demands', 'type' => 'strings', 'label' => 'Forderungen (eine pro Zeile, „Forderung: Erklärung“ setzt den Anfang fett)', 'default' => array()),
				self::classesField('')
			)
		);
	}
}
