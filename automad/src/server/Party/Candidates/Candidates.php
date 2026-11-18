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

namespace Automad\Party\Candidates;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Core\Str;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component Candidates: extended candidate profiles. The first candidate can be shown
 * as a large profile (portrait, facts, quote, bio, topics, demands, contact), the others as
 * cards that open the full profile.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class Candidates extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	const COLORS = array('blue' => 'Blau', 'orange' => 'Terrakotta / Orange', 'green' => 'Grün', 'gold' => 'Ocker', 'purple' => 'Aubergine', 'red' => 'Rot');

	public function __construct() {
		parent::__construct(__DIR__, 'candidates');
	}

	public function context(array $config): array {
		$candidates = array();
		$colors = array_keys(self::COLORS);

		foreach (array_values(array_filter((array) $config['candidates'], fn ($c) => is_array($c) && trim((string) ($c['name'] ?? '')) !== '')) as $i => $c) {
			$name = trim((string) $c['name']);
			$facts = array();

			foreach (array('district' => 'Wahlkreis', 'list_position' => 'Listenplatz', 'age' => 'Alter', 'profession' => 'Beruf') as $key => $label) {
				$value = trim((string) ($c[$key] ?? ''));

				if ($value !== '') {
					$facts[] = array('label' => trim((string) ($config['label_' . $key] ?? '')) ?: $label, 'value' => $value);
				}
			}

			$links = array();

			foreach (array('email' => 'E-Mail', 'website' => 'Website', 'instagram' => 'Instagram') as $key => $label) {
				$value = trim((string) ($c[$key] ?? ''));

				if ($value === '') {
					continue;
				}

				$href = $key === 'email' && strpos($value, '@') !== false && stripos($value, 'mailto:') !== 0 ? 'mailto:' . $value : $value;
				$links[] = array('label' => $label, 'href' => $href);
			}

			$color = in_array($c['color'] ?? '', $colors, true) ? $c['color'] : $colors[$i % count($colors)];
			$bio = trim((string) ($c['bio'] ?? ''));
			$topics = array_values(array_filter(array_map('trim', (array) ($c['topics'] ?? array()))));
			$demands = array_values(array_filter(array_map('trim', (array) ($c['demands'] ?? array()))));

			$candidates[] = array(
				'name' => $name,
				'initials' => self::initials($name),
				'role' => trim((string) ($c['role'] ?? '')),
				'photo' => trim((string) ($c['photo'] ?? '')),
				'quote' => trim((string) ($c['quote'] ?? '')),
				'bio_html' => $bio === '' ? '' : Str::markdown($bio),
				'facts' => $facts,
				'has_facts' => !empty($facts),
				'topics' => $topics,
				'has_topics' => !empty($topics),
				'demands' => $demands,
				'has_demands' => !empty($demands),
				'links' => $links,
				'has_links' => !empty($links) && (bool) ($config['show_links'] ?? false),
				'color' => $color,
				'featured' => $i === 0 && (bool) $config['feature_first']
			);
		}

		$intro = trim((string) $config['intro']);

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']),
			'title' => $config['title'],
			'intro_html' => $intro === '' ? '' : Str::markdown($intro),
			'featured' => array_values(array_filter($candidates, fn ($c) => $c['featured'])),
			'others' => array_values(array_filter($candidates, fn ($c) => !$c['featured'])),
			'has_others' => count(array_filter($candidates, fn ($c) => !$c['featured'])) > 0,
			'details_label' => trim((string) $config['details_label']) ?: 'Profil ansehen',
			'topics_label' => trim((string) $config['topics_label']) ?: 'Schwerpunkte',
			'demands_label' => trim((string) $config['demands_label']) ?: 'Forderungen',
			'classes' => $this->getClasses($config)
		);
	}

	/**
	 * The initials of a name for the placeholder portrait.
	 *
	 * @param string $name
	 * @return string
	 */
	public static function initials(string $name): string {
		$words = array_values(array_filter(preg_split('/[^\p{L}]+/u', $name) ?: array()));

		if (empty($words)) {
			return '?';
		}

		$first = mb_substr($words[0], 0, 1);
		$last = count($words) > 1 ? mb_substr($words[count($words) - 1], 0, 1) : '';

		return mb_strtoupper($first . $last);
	}

	protected function definition(): array {
		return array(
			'title' => 'Kandidat:innen (erweitertes Profil)',
			'icon' => 'person-badge',
			'description' => 'Erweiterte Profile: großes Porträt mit Fakten, Zitat, Lebenslauf, Schwerpunkten, Forderungen und Kontakt; weitere Kandidat:innen als Karten zum Aufklappen.',
			'fields' => array(
				self::sectionIdField('kandidaten'),
				self::titleField('Unsere Kandidat:innen'),
				array('name' => 'intro', 'type' => 'markdown', 'label' => 'Einleitung', 'default' => ''),
				array(
					'name' => 'candidates',
					'type' => 'list',
					'label' => 'Kandidat:innen',
					'addLabel' => 'Kandidat:in hinzufügen',
					'itemTitle' => 'name',
					'default' => array(),
					'fields' => array(
						array('name' => 'photo', 'type' => 'image', 'label' => 'Foto (ohne Foto: Initialen)'),
						array('name' => 'name', 'type' => 'text', 'label' => 'Name'),
						array('name' => 'role', 'type' => 'text', 'label' => 'Rolle (z.B. Direktkandidat:in)'),
						array('name' => 'district', 'type' => 'text', 'label' => 'Wahlkreis'),
						array('name' => 'list_position', 'type' => 'text', 'label' => 'Listenplatz'),
						array('name' => 'age', 'type' => 'text', 'label' => 'Alter'),
						array('name' => 'profession', 'type' => 'text', 'label' => 'Beruf'),
						array('name' => 'quote', 'type' => 'textarea', 'label' => 'Zitat'),
						array('name' => 'bio', 'type' => 'markdown', 'label' => 'Lebenslauf / Über mich (Markdown)'),
						array('name' => 'topics', 'type' => 'strings', 'label' => 'Schwerpunkte (einer pro Zeile)'),
						array('name' => 'demands', 'type' => 'strings', 'label' => 'Forderungen (eine pro Zeile)'),
						array('name' => 'email', 'type' => 'text', 'label' => 'E-Mail'),
						array('name' => 'website', 'type' => 'url', 'label' => 'Website'),
						array('name' => 'instagram', 'type' => 'url', 'label' => 'Instagram'),
						array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch') + self::COLORS)
					)
				),
				array('name' => 'show_links', 'type' => 'toggle', 'label' => 'Kontakt-Links anzeigen (E-Mail, Website, Instagram)', 'default' => false),
				array('name' => 'feature_first', 'type' => 'toggle', 'label' => 'Erste:n Kandidat:in groß zeigen', 'default' => true),
				array('name' => 'details_label', 'type' => 'text', 'label' => 'Text des Aufklappers', 'default' => 'Profil ansehen'),
				array('name' => 'topics_label', 'type' => 'text', 'label' => 'Überschrift Schwerpunkte', 'default' => 'Schwerpunkte'),
				array('name' => 'demands_label', 'type' => 'text', 'label' => 'Überschrift Forderungen', 'default' => 'Forderungen'),
				array('name' => 'label_district', 'type' => 'text', 'label' => 'Bezeichnung Wahlkreis', 'default' => 'Wahlkreis'),
				array('name' => 'label_list_position', 'type' => 'text', 'label' => 'Bezeichnung Listenplatz', 'default' => 'Listenplatz'),
				array('name' => 'label_age', 'type' => 'text', 'label' => 'Bezeichnung Alter', 'default' => 'Alter'),
				array('name' => 'label_profession', 'type' => 'text', 'label' => 'Bezeichnung Beruf', 'default' => 'Beruf'),
				self::classesField('')
			)
		);
	}
}
