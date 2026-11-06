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

namespace Automad\Party\InfoStack;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Überschrift, Text und hervorgehobene Box untereinander (Skizze: Folie 2 rechts).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class InfoStack extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'infostack');
	}

	public function context(array $config): array {
		return array(
			'title' => $config['title'],
			'text_html' => trim((string) $config['text']) === '' ? '' : \Automad\Core\Str::markdown((string) $config['text']),
			'box_html' => trim((string) $config['box']) === '' ? '' : \Automad\Core\Str::markdown((string) $config['box']),
			'box_image' => $config['box_image'],
			'has_box' => trim((string) $config['box']) !== '' || $config['box_image'] !== '',
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
			array('name' => 'title', 'type' => 'text', 'label' => 'Überschrift', 'default' => ''),
			array('name' => 'text', 'type' => 'markdown', 'label' => 'Text', 'default' => ''),
			array('name' => 'box', 'type' => 'markdown', 'label' => 'Hervorgehobene Box', 'default' => ''),
			array('name' => 'box_image', 'type' => 'image', 'label' => 'Bild in der Box (optional)', 'default' => ''),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Info-Stapel',
			'icon' => 'stack',
			'description' => 'Überschrift, Text und hervorgehobene Box untereinander (Skizze: Folie 2 rechts).',
			'fields' => self::elementFields()
		);
	}
}
