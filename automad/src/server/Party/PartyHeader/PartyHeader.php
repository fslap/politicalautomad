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

namespace Automad\Party\PartyHeader;

use Automad\Blocks\AbstractDynamicTemplateBlock;
use Automad\Party\Traits\ComponentConfig;

defined('AUTOMAD') or die('Direct access not permitted!');

/**
 * Party component PartyHeader (politicalpartysite → Automad party block).
 *
 * @author Florian Leon Steenbuck
 * @copyright Copyright (c) 2026 by Florian Leon Steenbuck - https://kil.ls
 * @license See LICENSE_PARTY_PURPOSE.md for license information
 */
class PartyHeader extends AbstractDynamicTemplateBlock {
	use ComponentConfig;

	public function __construct() {
		parent::__construct(__DIR__, 'partyheader');
	}

	public function context(array $config): array {
		$parts = preg_split('/\s+/', trim((string) $config['party_name'])) ?: array();

		return array(
			'warning_text' => $config['warning_text'],
			'party_name_parts' => array_values(array_filter($parts, fn ($part) => $part !== '')),
			'main_link' => $config['main_link'],
			'main_link_text' => $config['main_link_text'],
			'intro_text' => $config['intro_text'],
			'classes' => $this->getClasses($config)
		);
	}

	protected function definition(): array {
		return array(
			'title' => 'Partei-Header',
			'icon' => 'flag',
			'description' => 'Kopfbereich mit Parteiname (zeilenweise), Hinweistext, Haupt-Link und Intro.',
			'fields' => array(
				array('name' => 'warning_text', 'type' => 'textarea', 'label' => 'Hinweistext'),
				array('name' => 'party_name', 'type' => 'text', 'label' => 'Parteiname', 'default' => 'Freie Soziale-Libertäre Alternative Partei', 'help' => 'Jedes Wort wird als eigene Zeile dargestellt.'),
				array('name' => 'main_link', 'type' => 'url', 'label' => 'Haupt-Link'),
				array('name' => 'main_link_text', 'type' => 'text', 'label' => 'Text des Haupt-Links'),
				array('name' => 'intro_text', 'type' => 'textarea', 'label' => 'Intro-Text'),
				self::classesField('bgorange')
			)
		);
	}
}
