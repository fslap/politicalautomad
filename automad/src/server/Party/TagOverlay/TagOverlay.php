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

namespace Automad\Party\TagOverlay;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Schräge, zusammengeschriebene Tags (ohne #), die über einer Section schweben – zum Taggen von Sections im Graffiti-, Sticker- oder Stempel-Stil.
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class TagOverlay extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'tagoverlay');
	}

	public function context(array $config): array {
		$case = in_array($config['case'], array('camel', 'keep', 'upper', 'lower'), true) ? $config['case'] : 'camel';
		$tilt = in_array($config['tilt'], array('mixed', 'up', 'down', 'none'), true) ? $config['tilt'] : 'mixed';
		$colors = array('orange', 'blue', 'green', 'purple', 'gold', 'red');
		$tags = array();

		foreach ((array) $config['tags'] as $index => $raw) {
			$text = self::tag((string) $raw, $case);

			if ($text === '') {
				continue;
			}

			$n = count($tags);
			// Stable "random" values per tag: rotation, position and delay.
			$hash = crc32($text . $n);
			$angle = match ($tilt) {
				'up' => -4 - $hash % 6,
				'down' => 4 + $hash % 6,
				'none' => 0,
				default => ($n % 2 ? 1 : -1) * (3 + $hash % 7)
			};
			$color = (string) $config['color'] ?: $colors[$n % count($colors)];
			$link = trim((string) $config['link_base']);

			$tags[] = array(
				'text' => $text,
				'link' => $link !== '' ? str_replace('{tag}', rawurlencode($text), $link) : '',
				'style' => sprintf('--tag-angle: %ddeg; --tag-x: %d%%; --tag-y: %dpx; --tag-delay: %dms;', $angle, 4 + ($hash % 86), ($hash >> 8) % 40 - 20, ($hash >> 4) % 1600),
				'classes' => 'tag-overlay__tag tag-overlay__tag--' . preg_replace('/[^a-z]/', '', $color)
			);
		}

		$style = in_array($config['style'], array('graffiti', 'sticker', 'stamp', 'tape'), true) ? $config['style'] : 'graffiti';
		$placement = in_array($config['placement'], array('boundary', 'next', 'prev', 'inline'), true) ? $config['placement'] : 'boundary';
		$align = in_array($config['align'], array('scatter', 'left', 'center', 'right'), true) ? $config['align'] : 'scatter';
		$size = in_array($config['size'], array('s', 'm', 'l', 'xl'), true) ? $config['size'] : 'm';

		return array(
			'tags' => $tags,
			'has_tags' => !empty($tags),
			'classes' => trim("tag-overlay tag-overlay--$style tag-overlay--$placement tag-overlay--align-$align tag-overlay--size-$size" . ((bool) $config['animate'] ? ' tag-overlay--animate' : '') . ' ' . $this->getClasses($config))
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'tags', 'type' => 'strings', 'label' => 'Tags (einer pro Zeile, Leerzeichen werden zusammengeschrieben)', 'default' => array('Freie Wahl', 'Kommunen stärken', 'Dezentral')),
			array('name' => 'case', 'type' => 'select', 'label' => 'Schreibweise', 'default' => 'camel', 'options' => array('camel' => 'ZusammenGeschrieben (Großbuchstaben am Wortanfang)', 'keep' => 'Wie eingegeben, ohne Leerzeichen', 'upper' => 'GROSSBUCHSTABEN', 'lower' => 'kleinbuchstaben')),
			array('name' => 'style', 'type' => 'select', 'label' => 'Stil', 'default' => 'graffiti', 'options' => array('graffiti' => 'Graffiti (gesprüht, Outline)', 'sticker' => 'Sticker', 'stamp' => 'Stempel', 'tape' => 'Klebeband')),
			array('name' => 'placement', 'type' => 'select', 'label' => 'Schweben über', 'default' => 'boundary', 'options' => array('boundary' => 'Der Grenze zwischen zwei Sections', 'next' => 'Dem oberen Rand der nächsten Section', 'prev' => 'Dem unteren Rand der vorherigen Section', 'inline' => 'Nicht schwebend (normaler Abstand)')),
			array('name' => 'align', 'type' => 'select', 'label' => 'Verteilung', 'default' => 'scatter', 'options' => array('scatter' => 'Verstreut', 'left' => 'Links', 'center' => 'Mitte', 'right' => 'Rechts')),
			array('name' => 'size', 'type' => 'select', 'label' => 'Größe', 'default' => 'm', 'options' => array('s' => 'Klein', 'm' => 'Mittel', 'l' => 'Groß', 'xl' => 'Riesig')),
			array('name' => 'tilt', 'type' => 'select', 'label' => 'Schräglage', 'default' => 'mixed', 'options' => array('mixed' => 'Gemischt', 'up' => 'Alle nach oben', 'down' => 'Alle nach unten', 'none' => 'Gerade (nur kursiv)')),
			array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Bunt (Palette)', 'orange' => 'Terrakotta / Orange', 'blue' => 'Blau', 'green' => 'Grün', 'purple' => 'Aubergine', 'gold' => 'Ocker', 'red' => 'Rot', 'ink' => 'Schwarz', 'white' => 'Weiß')),
			array('name' => 'animate', 'type' => 'toggle', 'label' => 'Leicht schweben (Animation)', 'default' => true),
			array('name' => 'link_base', 'type' => 'text', 'label' => 'Link je Tag (optional, z.B. #{tag} oder /suche?q={tag})', 'default' => ''),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	/**
	 * Write a tag together, without # and spaces.
	 *
	 * @param string $text
	 * @param string $case
	 * @return string
	 */
	public static function tag(string $text, string $case = 'camel'): string {
		$text = trim((string) preg_replace('/^#+/u', '', trim($text)));
		$words = preg_split('/[\\s_]+/u', $text) ?: array();
		$words = array_values(array_filter($words, fn ($word) => $word !== ''));

		$words = match ($case) {
			'upper' => array_map(fn ($word) => mb_strtoupper($word), $words),
			'lower' => array_map(fn ($word) => mb_strtolower($word), $words),
			'keep' => $words,
			default => array_map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)) . mb_substr($word, 1), $words)
		};

		return join('', $words);
	}

	protected function definition(): array {
		return array(
			'title' => 'Overlay-Tags',
			'icon' => 'tags',
			'description' => 'Schräge, zusammengeschriebene Tags (ohne #), die über einer Section schweben – zum Taggen von Sections im Graffiti-, Sticker- oder Stempel-Stil.',
			'fields' => self::elementFields()
		);
	}
}
