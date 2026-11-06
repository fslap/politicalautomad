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

namespace Automad\Party\HeroTitle;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;
use Automad\Party\Traits\SlideKit;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Großer mehrzeiliger Titel mit einem Mehr-Pfeil zum nächsten Abschnitt (Skizze: First Page).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class HeroTitle extends AbstractDynamicTemplateBlock {
	use ComponentConfig;
	use SlideKit;

	public function __construct() {
		parent::__construct(__DIR__, 'herotitle');
	}

	public function context(array $config): array {
		$lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $config['title']) ?: array()), fn ($line) => $line !== ''));

		return array(
			'section_id' => $config['section_id'],
			'lines' => $lines,
			'more_label' => $config['more_label'],
			'more_target' => trim((string) $config['more_target']),
			'align' => $config['align'] === 'center' ? 'center' : 'left',
			'chevrons' => self::chevrons('down'),
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
			array('name' => 'section_id', 'type' => 'text', 'label' => 'Section-ID (Anker)', 'default' => ''),
			array('name' => 'title', 'type' => 'textarea', 'label' => 'Titel (eine Zeile pro Wort/Zeile)', 'default' => "Frei\nSozial\nLibertäre\nAlternative"),
			array('name' => 'more_label', 'type' => 'text', 'label' => 'Text am Pfeil', 'default' => 'Mehr'),
			array('name' => 'more_target', 'type' => 'text', 'label' => 'Ziel des Pfeils (#anker, leer = nächster Abschnitt)', 'default' => ''),
			array('name' => 'align', 'type' => 'select', 'label' => 'Ausrichtung', 'default' => 'left', 'options' => array('left' => 'Links', 'center' => 'Mitte')),
			array('name' => 'classes', 'type' => 'text', 'label' => 'CSS-Klassen', 'default' => '')
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Titel mit Mehr-Pfeil',
			'icon' => 'type-h1',
			'description' => 'Großer mehrzeiliger Titel mit einem Mehr-Pfeil zum nächsten Abschnitt (Skizze: First Page).',
			'fields' => self::elementFields()
		);
	}
}
