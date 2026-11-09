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

namespace Automad\Party\StainedGlass;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\YouTube;

defined('AUTOMAD') or die('Direct access not permitted!');

require_once __DIR__ . '/lib/StainedGlassGenerator.php';

/**
 * A section in the style of a church window (stained glass). The window is generated as SVG,
 * bullet points are shown on top and a YouTube player in the upper left corner starts playing
 * when the section is reached and stops when it is left.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class StainedGlass extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use YouTube;
	/**
	 * Symbols of the window (no religious symbols).
	 */
	const ARTIFACT_LABELS = array(
		'fist' => 'Faust (Solidarität)',
		'megaphone' => 'Megafon',
		'ballot' => 'Wahlurne',
		'handshake' => 'Handschlag',
		'scales' => 'Waage (Recht)',
		'key' => 'Schlüssel',
		'book' => 'Buch',
		'bird' => 'Schwalbe (Freiheit)',
		'feather' => 'Feder',
		'heart' => 'Herz',
		'eye' => 'Auge',
		'star' => 'Stern',
		'sun' => 'Sonne',
		'moon' => 'Mond',
		'flame' => 'Flamme',
		'lightning' => 'Blitz (Energie)',
		'leaf' => 'Blatt',
		'tree' => 'Baum',
		'sprout' => 'Keimling',
		'mountain' => 'Berge',
		'wave' => 'Welle',
		'globe' => 'Weltkugel',
		'house' => 'Haus (Kommune)',
		'gear' => 'Zahnrad (Arbeit)',
		'coins' => 'Münzen',
		'bike' => 'Fahrrad',
		'train' => 'Zug',
		'note' => 'Musiknote'
	);

	/**
	 * Shapes of the center window.
	 */
	const WINDOW_LABELS = array(
		'deco' => 'Art déco (Stufen mit Sonnenfächer)',
		'hex' => 'Wabe',
		'round' => 'Bullauge (rund)',
		'portal' => 'Rundbogen',
		'arch' => 'Spitzbogen'
	);

	public function __construct() {
		parent::__construct(__DIR__, 'stainedglass');
	}

	public function context(array $config): array {
		$seed = trim((string) $config['seed']);

		if ($seed === '' || $seed === 'auto') {
			$seed = (string) ($config['section_id'] ?: 'kirchenfenster');
		}

		$artifacts = array_values(array_intersect(array_keys(array_filter((array) $config['artifacts'])), StainedGlassGenerator::ARTIFACTS));
		$generator = (new StainedGlassGenerator($seed, (string) $config['palette'], $artifacts))->withWindow((string) $config['window']);
		$id = self::youtubeId((string) $config['video']);
		$position = in_array($config['text_position'], array('center', 'left', 'right', 'bottom'), true) ? $config['text_position'] : 'center';
		$size = in_array($config['player_size'], array('small', 'medium', 'large', 'audio'), true) ? $config['player_size'] : 'small';
		$height = preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['height']) ?: '100vh';
		$bullets = array();

		foreach (array_values(array_filter(array_map(fn ($text) => trim((string) $text), (array) $config['bullets']), fn ($text) => $text !== '')) as $text) {
			// "Forderung: Erklärung" or "Forderung – Erklärung" shows the demand in bold.
			$parts = preg_split('/\s*(?::|\s[–—-])\s+/u', $text, 2) ?: array($text);

			$bullets[] = count($parts) === 2 && mb_strlen($parts[0]) <= 80
				? array('lead' => $parts[0], 'text' => $parts[1])
				: array('lead' => '', 'text' => $text);
		}

		$style = in_array($config['bullet_style'], array('star', 'number', 'check', 'arrow', 'symbol'), true) ? $config['bullet_style'] : 'star';
		$columns = (string) $config['columns'];

		if (!in_array($columns, array('1', '2', '3'), true)) {
			$columns = count($bullets) > 14 ? '3' : (count($bullets) > 6 ? '2' : '1');
		}

		return array(
			'section_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $config['section_id']) ?: 'kirchenfenster',
			'title' => $config['title'],
			'bullets' => $bullets,
			'has_bullets' => !empty($bullets) || $config['title'] !== '',
			'intro' => trim((string) $config['intro']),
			'list_tag' => $style === 'number' ? 'ol' : 'ul',
			'list_classes' => "stained-glass__bullets stained-glass__bullets--$style stained-glass__bullets--columns-$columns",
			'svg' => $generator->svg(),
			'has_video' => $id !== '',
			'embed_url' => self::youtubeEmbedUrl((string) $config['video'], array('enablejsapi' => 1, 'controls' => 1, 'loop' => $config['loop'] ? 1 : 0, 'playlist' => $config['loop'] ? $id : null)),
			'player_size' => $size,
			'text_position' => $position,
			'style' => "--sg-height: $height;",
			'config_json' => $this->json(array(
				'autoplay' => (bool) $config['autoplay'],
				'restart' => (bool) $config['restart'],
				'volume' => max(0, min(100, (int) $config['volume']))
			)),
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		$artifacts = self::ARTIFACT_LABELS;

		return array(
			'title' => 'Glasfenster mit Musik',
			'icon' => 'brightness-high',
			'description' => 'Generiertes, buntes Bleiglasfenster (Art déco, Wabe, Bullauge, Bogen) mit politischen Symbolen als SVG-Hintergrund mit Forderungen. Ein YouTube-Song startet beim Erreichen der Section und stoppt beim Verlassen.',
			'fields' => array(
				self::sectionIdField('kirchenfenster'),
				array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift über den Stichpunkten (optional)'),
				array('name' => 'intro', 'type' => 'textarea', 'label' => 'Einleitung über den Forderungen (optional)'),
				array('name' => 'bullets', 'type' => 'strings', 'label' => 'Forderungen / Stichpunkte (eine pro Zeile)', 'default' => array('Freiheit: Selbstbestimmung in allen Lebensbereichen', 'Gerechtigkeit: faire Löhne und eine sichere Rente', 'Gemeinschaft: starke Kommunen statt zentraler Ämter'), 'help' => '„Forderung: Erklärung“ zeigt die Forderung fett und die Erklärung darunter.'),
				array('name' => 'columns', 'type' => 'select', 'label' => 'Spalten der Forderungen', 'default' => 'auto', 'options' => array('auto' => 'Automatisch (ab 7 Punkten 2, ab 15 Punkten 3)', '1' => '1 Spalte', '2' => '2 Spalten', '3' => '3 Spalten')),
				array('name' => 'bullet_style', 'type' => 'select', 'label' => 'Aufzählungszeichen', 'default' => 'star', 'options' => array('star' => 'Stern ✦', 'number' => 'Nummern 1. 2. 3.', 'check' => 'Haken ✓', 'arrow' => 'Pfeil →', 'symbol' => 'Faust ✊')),
				array('name' => 'text_position', 'type' => 'select', 'label' => 'Position der Stichpunkte', 'default' => 'center', 'options' => array('center' => 'Mitte (im Fenster)', 'left' => 'Links', 'right' => 'Rechts', 'bottom' => 'Unten')),
				array('name' => 'video', 'type' => 'url', 'label' => 'YouTube-Song', 'placeholder' => 'https://www.youtube.com/watch?v=…', 'help' => 'Ein Startpunkt im Link (t=…) wird übernommen.'),
				array('name' => 'player_size', 'type' => 'select', 'label' => 'Player oben links', 'default' => 'small', 'options' => array('small' => 'Klein', 'medium' => 'Mittel', 'large' => 'Groß', 'audio' => 'Nur Steuerung (Video ausgeblendet)')),
				array('name' => 'autoplay', 'type' => 'toggle', 'label' => 'Beim Erreichen der Section abspielen, beim Verlassen pausieren', 'default' => true),
				array('name' => 'restart', 'type' => 'toggle', 'label' => 'Bei jedem Erreichen von vorne beginnen'),
				array('name' => 'loop', 'type' => 'toggle', 'label' => 'Song wiederholen', 'default' => true),
				array('name' => 'volume', 'type' => 'number', 'label' => 'Lautstärke (0–100)', 'default' => 60),
				array('name' => 'window', 'type' => 'select', 'label' => 'Form des Mittelfensters', 'default' => 'deco', 'options' => self::WINDOW_LABELS),
				array('name' => 'palette', 'type' => 'select', 'label' => 'Farben', 'default' => 'classic', 'options' => array('classic' => 'Klassisch (Blau, Gold, Violett)', 'fire' => 'Feuer', 'ocean' => 'Ozean', 'forest' => 'Wald', 'party' => 'Partei-Farben', 'rainbow' => 'Regenbogen')),
				array('name' => 'seed', 'type' => 'text', 'label' => 'Muster-Seed', 'default' => 'auto', 'help' => 'Jeder Text erzeugt ein anderes Fenster, derselbe Text immer dasselbe. „auto“ nutzt die Section-ID.'),
				array('name' => 'artifacts', 'type' => 'json', 'label' => 'Symbole (an/aus)', 'default' => array_fill_keys(array_keys($artifacts), true), 'help' => 'Welche Symbole im Fenster vorkommen, z.B. {"fist": true, "coins": false}. Mögliche Symbole: ' . join(', ', array_keys(self::ARTIFACT_LABELS)) . '.'),
				array('name' => 'height', 'type' => 'text', 'label' => 'Höhe der Section', 'default' => '100vh'),
				self::classesField('')
			)
		);
	}
}
