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

namespace Automad\Party\TextSplit;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\SideMenuText\SideMenuText;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Gleichmäßige Spalten: ein Element oben oder unten fixiert mit Überschrift und Fließtext, oder ein Seitenmenü mit Text (Skizze: Bilanz).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class TextSplit extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'textsplit');
	}

	public function context(array $config): array {
		$md = fn ($text) => trim((string) $text) === '' ? '' : \Automad\Core\Str::markdown((string) $text);
		$maxHeight = trim((string) preg_replace('/[^a-zA-Z0-9.%()+\-\s]/', '', (string) $config['max_height'])) ?: '30rem';
		$colors = array('orange', 'blue', 'green', 'purple', 'gold', 'red');
		$columns = array();

		foreach (is_array($config['columns']) ? $config['columns'] : array() as $index => $column) {
			if (!is_array($column)) {
				continue;
			}

			$mode = in_array($column['mode'] ?? '', array('top', 'bottom', 'menu'), true) ? $column['mode'] : 'top';
			$media = in_array($column['media'] ?? '', array('image', 'value', 'box', 'none'), true) ? $column['media'] : 'image';
			$color = (string) ($column['color'] ?? '') ?: $colors[count($columns) % count($colors)];

			if ($media === 'image' && empty($column['image'])) {
				$media = 'none';
			}

			$columns[] = array(
				'mode' => $mode,
				'is_menu' => $mode === 'menu',
				'is_bottom' => $mode === 'bottom',
				'title' => (string) ($column['title'] ?? ''),
				'text_html' => $md($column['text'] ?? ''),
				'has_media' => $media !== 'none',
				'is_image' => $media === 'image',
				'is_value' => $media === 'value',
				'is_box' => $media === 'box',
				'image' => (string) ($column['image'] ?? ''),
				'value' => (string) ($column['value'] ?? ''),
				'value_label' => (string) ($column['value_label'] ?? ''),
				'box_html' => $md($column['box'] ?? ''),
				'menu_html' => $mode === 'menu' ? (new SideMenuText())->renderInner(array(
					'title' => (string) ($column['title'] ?? ''),
					'entries' => is_array($column['entries'] ?? null) ? $column['entries'] : array(),
					'menu_side' => 'top',
					'sticky' => 'top',
					'max_height' => '100%',
					'color' => $color,
					'classes' => 'side-menu-text--in-split'
				)) : '',
				'classes' => 'text-split__column--' . $mode . SideMenuText::colorClass($color)
			);
		}

		return array(
			'title' => $config['title'],
			'columns' => $columns,
			'has_columns' => !empty($columns),
			'style' => '--split-columns: ' . max(1, min(4, count($columns))) . '; --split-max: ' . $maxHeight . ';',
			'classes' => trim('text-split ' . $this->getClasses($config))
		);
	}

	/**
	 * The fields of the element (also used by slides with a prefix).
	 *
	 * @return array
	 */
	public static function elementFields(): array {
		return array(
			array('name' => 'title', 'type' => 'text', 'label' => 'Titel über den Spalten (optional)', 'default' => ''),
			array(
				'name' => 'columns',
				'type' => 'list',
				'label' => 'Spalten',
				'addLabel' => 'Spalte hinzufügen',
				'itemTitle' => 'title',
				'default' => array(
					array('mode' => 'top', 'media' => 'value', 'value' => '12.400 EUR', 'value_label' => 'Einnahmen', 'title' => 'Einnahmen', 'text' => 'Fließtext zu den **Einnahmen**.', 'color' => 'orange'),
					array('mode' => 'bottom', 'media' => 'value', 'value' => '9.800 EUR', 'value_label' => 'Ausgaben', 'title' => 'Ausgaben', 'text' => 'Fließtext zu den **Ausgaben**.', 'color' => 'blue'),
					array('mode' => 'menu', 'title' => 'Bericht', 'entries' => array(
						array('title' => 'Mitglieder', 'text' => 'Text zu den **Mitgliedern**.'),
						array('title' => 'Aktionen', 'text' => 'Text zu den **Aktionen**.'),
						array('title' => 'Ausblick', 'text' => 'Text zum **Ausblick**.')
					), 'color' => 'green')
				),
				'fields' => array(
					array('name' => 'mode', 'type' => 'select', 'label' => 'Aufbau', 'default' => 'top', 'options' => array(
						'top' => 'Element oben fixiert, Überschrift und Text darunter',
						'bottom' => 'Element unten fixiert, Überschrift und Text darüber',
						'menu' => 'Seitenmenü (Überschriften) mit Text daneben'
					)),
					array('name' => 'media', 'type' => 'select', 'label' => 'Element', 'default' => 'image', 'options' => array('image' => 'Bild', 'value' => 'Kennzahl', 'box' => 'Box mit Text', 'none' => 'Kein Element')),
					array('name' => 'image', 'type' => 'image', 'label' => 'Bild'),
					array('name' => 'value', 'type' => 'text', 'label' => 'Kennzahl (z.B. 12.400 EUR)'),
					array('name' => 'value_label', 'type' => 'text', 'label' => 'Beschriftung der Kennzahl'),
					array('name' => 'box', 'type' => 'markdown', 'label' => 'Box (Markdown)'),
					array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift'),
					array('name' => 'text', 'type' => 'markdown', 'label' => 'Fließtext (Markdown)'),
					array(
						'name' => 'entries',
						'type' => 'list',
						'label' => 'Seitenmenü: Einträge',
						'addLabel' => 'Eintrag hinzufügen',
						'itemTitle' => 'title',
						'fields' => array(
							array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift (im Menü)'),
							array('name' => 'text', 'type' => 'markdown', 'label' => 'Fließtext (Markdown)')
						)
					),
					array('name' => 'color', 'type' => 'select', 'label' => 'Farbe', 'default' => '', 'options' => array('' => 'Automatisch', 'orange' => 'Terrakotta / Orange', 'blue' => 'Blau', 'green' => 'Grün', 'purple' => 'Aubergine', 'gold' => 'Ocker', 'red' => 'Rot'))
				)
			),
			array('name' => 'max_height', 'type' => 'text', 'label' => 'Höhe der Spalten (Text scrollt darin)', 'default' => '30rem'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => '3er-Split mit Text',
			'icon' => 'layout-three-columns',
			'description' => 'Gleichmäßige Spalten: ein Element oben oder unten fixiert mit Überschrift und Fließtext, oder ein Seitenmenü mit Text (Skizze: Bilanz).',
			'fields' => self::elementFields()
		);
	}
}
