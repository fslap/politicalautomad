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

namespace Automad\Party\DocumentCard;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Karte mit Vorschau, Textauszug und Ansehen-Knopf (Skizze: Folie 2 links).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class DocumentCard extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'documentcard');
	}

	public function context(array $config): array {
		return array(
			'title' => $config['title'],
			'preview' => $config['preview'],
			'excerpt_html' => $this->markdown((string) $config['excerpt']),
			'file' => $config['file'],
			'button' => $config['button'] ?: 'ansehen',
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
			array('name' => 'title', 'type' => 'text', 'label' => 'Titel', 'default' => 'Satzung'),
			array('name' => 'preview', 'type' => 'image', 'label' => 'Vorschaubild (optional)', 'default' => ''),
			array('name' => 'excerpt', 'type' => 'markdown', 'label' => 'Textauszug', 'default' => ''),
			array('name' => 'file', 'type' => 'file', 'label' => 'Dokument (PDF) oder Link', 'default' => ''),
			array('name' => 'button', 'type' => 'text', 'label' => 'Knopf', 'default' => 'ansehen'),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Dokumentkarte',
			'icon' => 'file-earmark-text',
			'description' => 'Karte mit Vorschau, Textauszug und Ansehen-Knopf (Skizze: Folie 2 links).',
			'fields' => self::elementFields()
		);
	}

	/**
	 * Render markdown.
	 *
	 * @param string $text
	 * @return string
	 */
	private function markdown(string $text): string {
		return trim($text) === '' ? '' : \Automad\Core\Str::markdown($text);
	}
}
