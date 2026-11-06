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

namespace Automad\Party\ListCard;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Karte mit Reiter-Titel und Aufzählung (Skizze: Satzung).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class ListCard extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'listcard');
	}

	public function context(array $config): array {
		$items = array_values(array_filter(array_map(
			fn ($item) => trim((string) $item),
			is_array($config['items']) ? $config['items'] : (preg_split('/\r?\n/', (string) $config['items']) ?: array())
		), fn ($item) => $item !== ''));

		return array(
			'title' => $config['title'],
			'items' => $items,
			'text_html' => trim((string) $config['text']) === '' ? '' : \Automad\Core\Str::markdown((string) $config['text']),
			'link' => $config['link'],
			'link_label' => $config['link_label'] ?: 'Alles lesen',
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
			array('name' => 'title', 'type' => 'text', 'label' => 'Reiter-Titel', 'default' => 'Satzung'),
			array('name' => 'items', 'type' => 'strings', 'label' => 'Punkte (einer pro Zeile)', 'default' => array()),
			array('name' => 'text', 'type' => 'markdown', 'label' => 'Text unter der Liste', 'default' => ''),
			array('name' => 'link', 'type' => 'file', 'label' => 'Link oder Dokument', 'default' => ''),
			array('name' => 'link_label', 'type' => 'text', 'label' => 'Linktext', 'default' => 'Alles lesen'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Listenkarte (Satzung)',
			'icon' => 'list-ul',
			'description' => 'Karte mit Reiter-Titel und Aufzählung (Skizze: Satzung).',
			'fields' => self::elementFields()
		);
	}
}
