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

namespace Automad\Party\SideMenuText;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Überschriften als fixiertes Menü, daneben der Markdown-Text der gewählten Überschrift (zum Durchlesen).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class SideMenuText extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'sidemenutext');
	}

	/**
	 * A color class of the palette.
	 *
	 * @param string $color
	 * @return string
	 */
	public static function colorClass(string $color): string {
		return in_array($color, array('orange', 'blue', 'green', 'purple', 'gold', 'red'), true) ? " party-color--$color" : '';
	}

	public function context(array $config): array {
		$entries = array();

		foreach (is_array($config['entries']) ? $config['entries'] : array() as $entry) {
			if (!is_array($entry) || (trim((string) ($entry['title'] ?? '')) === '' && trim((string) ($entry['text'] ?? '')) === '')) {
				continue;
			}

			$entries[] = array(
				'index' => count($entries),
				'title' => (string) ($entry['title'] ?? ''),
				'html' => \Automad\Core\Str::markdown((string) ($entry['text'] ?? '')),
				'first' => empty($entries)
			);
		}

		$side = in_array($config['menu_side'], array('left', 'right', 'top', 'bottom'), true) ? $config['menu_side'] : 'left';
		$sticky = in_array($config['sticky'], array('top', 'bottom'), true) ? $config['sticky'] : '';

		return array(
			'title' => $config['title'],
			'entries' => $entries,
			'has_entries' => !empty($entries),
			'style' => '--smt-max: ' . (trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['max_height'])) ?: '28rem') . ';',
			'classes' => trim("side-menu-text side-menu-text--$side" . ($sticky !== '' ? " side-menu-text--sticky-$sticky" : '') . self::colorClass((string) $config['color']) . ' ' . $this->getClasses($config))
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'title', 'type' => 'text', 'label' => 'Titel (optional)', 'default' => ''),
			array(
				'name' => 'entries',
				'type' => 'list',
				'label' => 'Einträge',
				'addLabel' => 'Eintrag hinzufügen',
				'itemTitle' => 'title',
				'default' => array(
					array('title' => 'Einnahmen', 'text' => 'Text zu den **Einnahmen**.'),
					array('title' => 'Ausgaben', 'text' => 'Text zu den **Ausgaben**.'),
					array('title' => 'Ausblick', 'text' => 'Text zum **Ausblick**.')
				),
				'fields' => array(
					array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift (im Menü)'),
					array('name' => 'text', 'type' => 'markdown', 'label' => 'Fließtext (Markdown)')
				)
			),
			array('name' => 'menu_side', 'type' => 'select', 'label' => 'Menü', 'default' => 'left', 'options' => array('left' => 'Links neben dem Text', 'right' => 'Rechts neben dem Text', 'top' => 'Über dem Text', 'bottom' => 'Unter dem Text')),
			array('name' => 'sticky', 'type' => 'select', 'label' => 'Menü fixiert', 'default' => 'top', 'options' => array('top' => 'Oben', 'bottom' => 'Unten', '' => 'Nicht fixiert')),
			array('name' => 'max_height', 'type' => 'text', 'label' => 'Maximale Höhe (Text scrollt darin)', 'default' => '28rem'),
			array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch', 'orange' => 'Terrakotta / Orange', 'blue' => 'Blau', 'green' => 'Grün', 'purple' => 'Aubergine', 'gold' => 'Ocker', 'red' => 'Rot')),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Seitenmenü mit Text',
			'icon' => 'layout-sidebar',
			'description' => 'Überschriften als fixiertes Menü, daneben der Markdown-Text der gewählten Überschrift (zum Durchlesen).',
			'fields' => self::elementFields()
		);
	}
}
